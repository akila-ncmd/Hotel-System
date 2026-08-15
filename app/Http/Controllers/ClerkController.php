<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use App\Models\Room;
use App\Models\Billing;
use App\Models\TravelAgency;
use App\Models\TravelAgencyBooking;
use App\Models\RoomType;
use App\Models\Branch;
use App\Mail\PaymentConfirmation;
use App\Mail\ReservationConfirmation;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class ClerkController extends Controller
{
    public function dashboard(Request $request)
    {
        $branchId = $this->getBranchId($request);
        $branch = Branch::findOrFail($branchId);

        // Fetch individual reservations (not linked to travel agency bookings)
        $reservations = Reservation::where('branch_id', $branchId)
            ->whereNotIn('id', TravelAgencyBooking::pluck('reservation_id'))
            ->where(function ($query) {
                $query->whereIn('status', ['pending', 'confirmed', 'checked_in'])
                    ->orWhere(function ($subQuery) {
                        $subQuery->where('status', 'checked_out')
                            ->where('updated_at', '>=', Carbon::now()->subHours(24));
                    });
            })
            ->with(['user', 'roomType', 'branch'])
            ->get();

        // Fetch travel agency bookings with their reservations
        $travelAgencyBookings = TravelAgencyBooking::whereHas('reservation', function ($query) use ($branchId) {
                $query->where('branch_id', $branchId)
                    ->whereIn('status', ['pending', 'confirmed', 'checked_in'])
                    ->orWhere(function ($subQuery) {
                        $subQuery->where('status', 'checked_out')
                            ->where('updated_at', '>=', Carbon::now()->subHours(24));
                    });
            })
            ->with(['travelAgency', 'reservation' => function ($query) {
                $query->with(['user', 'roomType', 'branch']);
            }])
            ->get()
            ->groupBy('travel_agency_id');

        Log::info('Clerk Dashboard Data', [
            'branch_id' => $branchId,
            'individual_reservations_count' => $reservations->count(),
            'travel_agency_bookings_count' => $travelAgencyBookings->count(),
            'statuses' => $reservations->pluck('status')->unique()->toArray(),
            'checked_out' => $reservations->where('status', 'checked_out')->map(function ($res) {
                return [
                    'id' => $res->id,
                    'updated_at' => $res->updated_at->toDateTimeString(),
                    'within_24h' => $res->updated_at >= Carbon::now()->subHours(24)
                ];
            })->toArray()
        ]);

        return view('clerk.dashboard', compact('reservations', 'travelAgencyBookings', 'branch'));
    }

    public function showRoomReservationForm(Request $request)
    {
        $branchId = $this->getBranchId($request);
        $branch = Branch::findOrFail($branchId);
        $roomTypes = RoomType::where('is_suite', false)->get();
        $users = \App\Models\User::where('role', 'customer')->get();
        return view('clerk.reservation-room', compact('branch', 'roomTypes', 'users', 'branchId'));
    }

    public function showSuiteReservationForm(Request $request)
    {
        $branchId = $this->getBranchId($request);
        $branch = Branch::findOrFail($branchId);
        $roomTypes = RoomType::where('is_suite', true)->get();
        $users = \App\Models\User::where('role', 'customer')->get();
        return view('clerk.reservation-suite', compact('branch', 'roomTypes', 'users', 'branchId'));
    }

    public function storeReservation(Request $request)
    {
        try {
            $branchId = $this->getBranchId($request);
            $isSuite = $request->has('is_suite') && $request->input('is_suite');

            $rules = [
                'user_id' => 'required|exists:users,id',
                'room_type_id' => 'required|exists:room_types,id',
                'number_of_occupants' => 'required|integer|min:1',
                'credit_card_details' => 'nullable|string',
            ];

            if ($isSuite) {
                $rules = array_merge($rules, [
                    'check_in_date' => 'required|date|after:today',
                    'duration_type' => 'required|in:weeks,months',
                    'duration_value' => 'required|integer|min:1',
                ]);
            } else {
                $rules = array_merge($rules, [
                    'check_in_date' => 'required|date|after:today',
                    'check_out_date' => 'required|date|after:check_in_date',
                ]);
            }

            $data = $request->validate($rules);

            // Set branch_id
            $data['branch_id'] = $branchId;
            $roomType = RoomType::find($data['room_type_id']);
            if ($data['number_of_occupants'] > $roomType->max_occupants) {
                Log::warning('Reservation Failed: Occupants Exceed Limit', [
                    'room_type_id' => $data['room_type_id'],
                    'occupants' => $data['number_of_occupants'],
                    'max_occupants' => $roomType->max_occupants
                ]);
                return back()->withErrors(['number_of_occupants' => 'Exceeds maximum occupants'])->withInput();
            }

            $data['status'] = 'pending';

            if ($isSuite) {
                $checkInDate = Carbon::parse($data['check_in_date']);
                if ($data['duration_type'] === 'weeks') {
                    if ($data['duration_value'] == 4) {
                        $data['duration_type'] = 'months';
                        $data['duration_value'] = 1;
                        $data['check_out_date'] = $checkInDate->copy()->addMonth()->format('Y-m-d');
                    } else {
                        $data['check_out_date'] = $checkInDate->copy()->addWeeks($data['duration_value'])->format('Y-m-d');
                    }
                } else {
                    $data['check_out_date'] = $checkInDate->copy()->addMonths($data['duration_value'])->format('Y-m-d');
                }
            }

            $reservation = Reservation::create($data);

            Log::info('Reservation Created', [
                'reservation_id' => $reservation->id,
                'user_id' => $data['user_id'],
                'branch_id' => $data['branch_id'],
                'room_type_id' => $data['room_type_id'],
                'check_in_date' => $data['check_in_date'],
                'check_out_date' => $data['check_out_date'] ?? null,
                'status' => $data['status']
            ]);

            Mail::to($reservation->user->email)->send(new ReservationConfirmation($reservation));

            return redirect()->route('clerk.dashboard', ['branch_id' => $branchId])->with('success', 'Reservation created successfully');
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::warning('Reservation Validation Failed', [
                'errors' => $e->errors(),
                'request_data' => $request->all()
            ]);
            return back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            Log::error('Reservation Creation Failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return back()->withErrors(['error' => 'Failed to create reservation: ' . $e->getMessage()])->withInput();
        }
    }

    public function editReservation(Request $request, $id)
    {
        $branchId = $this->getBranchId($request);
        $reservation = Reservation::where('branch_id', $branchId)->findOrFail($id);
        if (!in_array($reservation->status, ['pending', 'confirmed'])) {
            return back()->withErrors(['status' => 'Cannot edit this reservation']);
        }
        $branch = Branch::findOrFail($branchId);
        $roomTypes = RoomType::where('is_suite', $reservation->roomType->is_suite)->get();
        $users = \App\Models\User::where('role', 'customer')->get();
        return view('clerk.reservation-edit', compact('reservation', 'branch', 'roomTypes', 'users', 'branchId'));
    }

    public function updateReservation(Request $request, $id)
    {
        try {
            $branchId = $this->getBranchId($request);
            $reservation = Reservation::where('branch_id', $branchId)->findOrFail($id);
            if (!in_array($reservation->status, ['pending', 'confirmed'])) {
                return back()->withErrors(['status' => 'Cannot edit this reservation']);
            }
            $isSuite = $reservation->roomType->is_suite;

            $rules = [
                'user_id' => 'required|exists:users,id',
                'room_type_id' => 'required|exists:room_types,id',
                'number_of_occupants' => 'required|integer|min:1',
                'credit_card_details' => 'nullable|string',
            ];

            if ($isSuite) {
                $rules = array_merge($rules, [
                    'check_in_date' => 'required|date|after:today',
                    'duration_type' => 'required|in:weeks,months',
                    'duration_value' => 'required|integer|min:1',
                ]);
            } else {
                $rules = array_merge($rules, [
                    'check_in_date' => 'required|date|after:today',
                    'check_out_date' => 'required|date|after:check_in_date',
                ]);
            }

            $data = $request->validate($rules);

            $data['branch_id'] = $branchId;
            $roomType = RoomType::find($data['room_type_id']);
            if ($data['number_of_occupants'] > $roomType->max_occupants) {
                Log::warning('Reservation Update Failed: Occupants Exceed Limit', [
                    'room_type_id' => $data['room_type_id'],
                    'occupants' => $data['number_of_occupants'],
                    'max_occupants' => $roomType->max_occupants
                ]);
                return back()->withErrors(['number_of_occupants' => 'Exceeds maximum occupants'])->withInput();
            }

            if ($isSuite) {
                $checkInDate = Carbon::parse($data['check_in_date']);
                if ($data['duration_type'] === 'weeks') {
                    if ($data['duration_value'] == 4) {
                        $data['duration_type'] = 'months';
                        $data['duration_value'] = 1;
                        $data['check_out_date'] = $checkInDate->copy()->addMonth()->format('Y-m-d');
                    } else {
                        $data['check_out_date'] = $checkInDate->copy()->addWeeks($data['duration_value'])->format('Y-m-d');
                    }
                } else {
                    $data['check_out_date'] = $checkInDate->copy()->addMonths($data['duration_value'])->format('Y-m-d');
                }
            }

            $reservation->update($data);

            Log::info('Reservation Updated', [
                'reservation_id' => $reservation->id,
                'user_id' => $data['user_id'],
                'branch_id' => $data['branch_id'],
                'room_type_id' => $data['room_type_id'],
                'check_in_date' => $data['check_in_date'],
                'check_out_date' => $data['check_out_date'] ?? null
            ]);

            return redirect()->route('clerk.dashboard', ['branch_id' => $branchId])->with('success', 'Reservation updated successfully');
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::warning('Reservation Update Validation Failed', [
                'errors' => $e->errors(),
                'request_data' => $request->all()
            ]);
            return back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            Log::error('Reservation Update Failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return back()->withErrors(['error' => 'Failed to update reservation: ' . $e->getMessage()])->withInput();
        }
    }

    public function cancelReservation(Request $request, $id)
    {
        try {
            $branchId = $this->getBranchId($request);
            $reservation = Reservation::where('branch_id', $branchId)->findOrFail($id);
            if (!in_array($reservation->status, ['pending', 'confirmed'])) {
                Log::warning('Reservation Cancellation Failed: Invalid Status', [
                    'reservation_id' => $id,
                    'status' => $reservation->status
                ]);
                return back()->withErrors(['status' => 'Cannot cancel this reservation']);
            }

            $reservation->update(['status' => 'cancelled']);

            Log::info('Reservation Cancelled', [
                'reservation_id' => $reservation->id,
                'branch_id' => $branchId
            ]);

            return redirect()->route('clerk.dashboard', ['branch_id' => $branchId])->with('success', 'Reservation cancelled successfully');
        } catch (\Exception $e) {
            Log::error('Reservation Cancellation Failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return back()->withErrors(['error' => 'Failed to cancel reservation: ' . $e->getMessage()]);
        }
    }

    public function checkIn(Request $request, $id)
    {
        $branchId = $this->getBranchId($request);
        $reservation = Reservation::where('branch_id', $branchId)->findOrFail($id);
        $availableRooms = Room::where('branch_id', $branchId)
                             ->where('room_type_id', $reservation->room_type_id)
                             ->where('status', 'available')
                             ->get();
        $branch = Branch::findOrFail($branchId);
        return view('clerk.check-in', compact('reservation', 'availableRooms', 'branch', 'branchId'));
    }

    public function storeCheckIn(Request $request, $id)
    {
        $branchId = $this->getBranchId($request);
        $reservation = Reservation::where('branch_id', $branchId)->findOrFail($id);
        $today = Carbon::today()->format('Y-m-d');
        $request->validate([
            'room_id' => 'required|exists:rooms,id',
            'credit_card_details' => 'required_if:check_in_date,'.$today.'|string|nullable'
        ]);

        $reservation->update([
            'room_id' => $request->room_id,
            'status' => 'checked_in',
            'credit_card_details' => $request->credit_card_details ?? $reservation->credit_card_details
        ]);

        Room::find($request->room_id)->update(['status' => 'occupied']);

        Billing::create([
            'reservation_id' => $reservation->id,
            'user_id' => $reservation->user_id,
            'branch_id' => $branchId,
            'total_amount' => $this->calculateTotal($reservation),
            'payment_status' => 'pending'
        ]);

        return redirect()->route('clerk.dashboard', ['branch_id' => $branchId])->with('success', 'Check-in completed successfully');
    }

    public function checkOut(Request $request, $id)
    {
        $branchId = $this->getBranchId($request);
        $reservation = Reservation::where('branch_id', $branchId)->findOrFail($id);
        $billing = Billing::where('reservation_id', $id)->firstOrFail();
        $baseCost = $this->calculateTotal($reservation);
        $branch = Branch::findOrFail($branchId);
        return view('clerk.check-out', compact('reservation', 'billing', 'baseCost', 'branch', 'branchId'));
    }

    public function storeCheckOut(Request $request, $id)
    {
        try {
            $branchId = $this->getBranchId($request);
            $reservation = Reservation::where('branch_id', $branchId)->findOrFail($id);
            $billing = Billing::where('reservation_id', $id)->firstOrFail();

            $request->validate([
                'payment_method' => 'required|in:cash,credit_card',
                'restaurant_charges' => 'nullable|numeric|min:0|max:10000|regex:/^\d+(\.\d{0,2})?$/',
                'room_service_charges' => 'nullable|numeric|min:0|max:10000|regex:/^\d+(\.\d{0,2})?$/',
                'laundry_charges' => 'nullable|numeric|min:0|max:10000|regex:/^\d+(\.\d{0,2})?$/',
                'telephone_charges' => 'nullable|numeric|min:0|max:10000|regex:/^\d+(\.\d{0,2})?$/',
                'club_facility_charges' => 'nullable|numeric|min:0|max:10000|regex:/^\d+(\.\d{0,2})?$/'
            ]);

            $billing->update([
                'payment_method' => $request->payment_method,
                'payment_status' => 'paid',
                'restaurant_charges' => $request->restaurant_charges ?? 0,
                'room_service_charges' => $request->room_service_charges ?? 0,
                'laundry_charges' => $request->laundry_charges ?? 0,
                'telephone_charges' => $request->telephone_charges ?? 0,
                'club_facility_charges' => $request->club_facility_charges ?? 0,
                'total_amount' => $this->calculateTotal($reservation, $request->all())
            ]);

            $reservation->update(['status' => 'checked_out']);
            Room::find($reservation->room_id)->update(['status' => 'available']);

            Mail::to($reservation->user->email)->send(new PaymentConfirmation($billing));

            Log::info('Check-out Completed', [
                'reservation_id' => $reservation->id,
                'billing_id' => $billing->id,
                'total_amount' => $billing->total_amount
            ]);

            return redirect()->route('clerk.dashboard', ['branch_id' => $branchId])->with('success', 'Check-out completed successfully');
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::warning('Check-out Validation Failed', [
                'errors' => $e->errors(),
                'request_data' => $request->all()
            ]);
            return back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            Log::error('Check-out Failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return back()->withErrors(['error' => 'Failed to complete check-out: ' . $e->getMessage()])->withInput();
        }
    }

    public function editCheckOut(Request $request, $id)
    {
        $branchId = $this->getBranchId($request);
        $reservation = Reservation::where('branch_id', $branchId)
                                  ->where('status', 'checked_out')
                                  ->where('updated_at', '>=', Carbon::now()->subHours(24))
                                  ->findOrFail($id);
        $billing = Billing::where('reservation_id', $id)->firstOrFail();
        $baseCost = $this->calculateTotal($reservation);
        $branch = Branch::findOrFail($branchId);
        return view('clerk.edit-check-out', compact('reservation', 'billing', 'baseCost', 'branch', 'branchId'));
    }

    public function updateCheckOut(Request $request, $id)
    {
        try {
            $branchId = $this->getBranchId($request);
            $reservation = Reservation::where('branch_id', $branchId)
                                      ->where('status', 'checked_out')
                                      ->where('updated_at', '>=', Carbon::now()->subHours(24))
                                      ->findOrFail($id);
            $billing = Billing::where('reservation_id', $id)->firstOrFail();

            $request->validate([
                'payment_method' => 'required|in:cash,credit_card',
                'payment_status' => 'required|in:pending,paid',
                'restaurant_charges' => 'nullable|numeric|min:0|max:10000|regex:/^\d+(\.\d{0,2})?$/',
                'room_service_charges' => 'nullable|numeric|min:0|max:10000|regex:/^\d+(\.\d{0,2})?$/',
                'laundry_charges' => 'nullable|numeric|min:0|max:10000|regex:/^\d+(\.\d{0,2})?$/',
                'telephone_charges' => 'nullable|numeric|min:0|max:10000|regex:/^\d+(\.\d{0,2})?$/',
                'club_facility_charges' => 'nullable|numeric|min:0|max:10000|regex:/^\d+(\.\d{0,2})?$/'
            ]);

            $billing->update([
                'payment_method' => $request->payment_method,
                'payment_status' => $request->payment_status,
                'restaurant_charges' => $request->restaurant_charges ?? 0,
                'room_service_charges' => $request->room_service_charges ?? 0,
                'laundry_charges' => $request->laundry_charges ?? 0,
                'telephone_charges' => $request->telephone_charges ?? 0,
                'club_facility_charges' => $request->club_facility_charges ?? 0,
                'total_amount' => $this->calculateTotal($reservation, $request->all())
            ]);

            if ($billing->payment_status === 'paid') {
                Mail::to($reservation->user->email)->send(new PaymentConfirmation($billing));
            }

            Log::info('Check-out Updated', [
                'reservation_id' => $reservation->id,
                'billing_id' => $billing->id,
                'total_amount' => $billing->total_amount
            ]);

            return redirect()->route('clerk.dashboard', ['branch_id' => $branchId])->with('success', 'Checkout updated successfully');
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::warning('Check-out Update Validation Failed', [
                'errors' => $e->errors(),
                'request_data' => $request->all()
            ]);
            return back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            Log::error('Check-out Update Failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return back()->withErrors(['error' => 'Failed to update check-out: ' . $e->getMessage()])->withInput();
        }
    }

    public function roomAvailability(Request $request)
    {
        $branchId = $this->getBranchId($request);
        $branch = Branch::findOrFail($branchId);

        $rooms = Room::where('branch_id', $branchId)
                     ->with('roomType')
                     ->get()
                     ->filter(function ($room) {
                         if (!$room->roomType) {
                             Log::warning('Room with missing roomType found', [
                                 'room_id' => $room->id,
                                 'room_type_id' => $room->room_type_id,
                                 'branch_id' => $room->branch_id
                             ]);
                             return false;
                         }
                         return true;
                     });

        $reservations = Reservation::where('branch_id', $branchId)
                                   ->whereIn('status', ['confirmed', 'checked_in'])
                                   ->with(['user', 'room', 'roomType'])
                                   ->get();

        $checkedInRooms = [];
        $availableRooms = [];

        foreach ($rooms as $room) {
            $reservation = $reservations->firstWhere('room_id', $room->id);
            if ($reservation && $reservation->status === 'checked_in') {
                $checkedInRooms[] = [
                    'room' => $room,
                    'reservation' => $reservation,
                    'customer' => $reservation->user->name,
                    'check_in_date' => $reservation->check_in_date,
                    'check_out_date' => $reservation->check_out_date
                ];
            } elseif (!$reservation && $room->status === 'available') {
                $availableRooms[] = [
                    'room' => $room,
                    'room_type' => $room->roomType->name
                ];
            }
        }

        return view('clerk.room-availability', compact('checkedInRooms', 'availableRooms', 'branch', 'branchId'));
    }

    public function travelAgencyBooking(Request $request)
    {
        $branchId = $this->getBranchId($request);
        $branch = Branch::findOrFail($branchId);
        $agencies = TravelAgency::where('is_verified', true)->get();
        $roomTypes = RoomType::all();
        $branches = [$branch];

        $roomGroups = [
            'single' => $roomTypes->where('max_occupants', 1)->where('is_suite', 0),
            'double' => $roomTypes->where('max_occupants', 2)->where('is_suite', 0),
            'suite' => $roomTypes->where('is_suite', 1)
        ];

        return view('clerk.travel-agency', compact('agencies', 'roomGroups', 'branches', 'branchId'));
    }

    public function storeTravelAgencyBooking(Request $request)
    {
        try {
            $branchId = $this->getBranchId($request);
            // Log incoming request data
            Log::info('Travel Agency Booking Request', $request->all());

            // Base validation rules
            $baseRules = [
                'travel_agency_id' => 'required|exists:travel_agencies,id',
                'branch_id' => 'required|exists:branches,id',
                'room_types' => 'required|array|min:1',
                'room_types.*.selected' => 'nullable|boolean',
                'room_types.*.quantity' => 'required_if:room_types.*.selected,1|integer|min:1',
                'room_types.*.occupants' => 'required_if:room_types.*.selected,1|integer|min:1',
                'room_types.*.check_in_date' => 'required_if:room_types.*.selected,1|date|after_or_equal:today',
                'discount_percentage' => 'required|numeric|min:0|max:100',
                'quotation_amount' => 'required|numeric|min:0'
            ];

            // Validate branch_id matches selected branch
            if ($request->branch_id != $branchId) {
                Log::warning('Travel Agency Booking Failed: Invalid Branch', [
                    'requested_branch_id' => $request->branch_id,
                    'expected_branch_id' => $branchId
                ]);
                return back()->withErrors(['branch_id' => 'Invalid branch selected.'])->withInput();
            }

            // Dynamic validation rules based on room type
            $roomTypes = RoomType::all()->keyBy('id');
            $dynamicRules = [];

            foreach ($request->input('room_types', []) as $roomTypeId => $roomData) {
                if (isset($roomData['selected']) && $roomData['selected']) {
                    $isSuite = isset($roomTypes[$roomTypeId]) && $roomTypes[$roomTypeId]->is_suite;
                    if ($isSuite) {
                        $dynamicRules["room_types.{$roomTypeId}.duration_value"] = 'required|integer|min:1';
                        $dynamicRules["room_types.{$roomTypeId}.duration_type"] = 'required|in:weeks,months';
                        $dynamicRules["room_types.{$roomTypeId}.check_out_date"] = 'nullable';
                    } else {
                        $dynamicRules["room_types.{$roomTypeId}.check_out_date"] = 'required|date|after:room_types.*.check_in_date';
                        $dynamicRules["room_types.{$roomTypeId}.duration_value"] = 'nullable';
                        $dynamicRules["room_types.{$roomTypeId}.duration_type"] = 'nullable';
                    }
                } else {
                    $dynamicRules["room_types.{$roomTypeId}.check_out_date"] = 'nullable';
                    $dynamicRules["room_types.{$roomTypeId}.duration_value"] = 'nullable';
                    $dynamicRules["room_types.{$roomTypeId}.duration_type"] = 'nullable';
                }
            }

            // Merge base and dynamic rules
            $rules = array_merge($baseRules, $dynamicRules);

            // Validate request data
            $data = $request->validate($rules);

            Log::info('Travel Agency Booking Validation Passed', [
                'travel_agency_id' => $data['travel_agency_id'],
                'branch_id' => $data['branch_id'],
                'room_types' => $data['room_types'],
                'discount_percentage' => $data['discount_percentage'],
                'quotation_amount' => $data['quotation_amount']
            ]);

            // Calculate total rooms
            $totalRooms = 0;
            $selectedRoomTypes = [];
            foreach ($data['room_types'] as $roomTypeId => $roomData) {
                if (isset($roomData['selected']) && $roomData['selected'] && !empty($roomData['quantity'])) {
                    $totalRooms += (int)$roomData['quantity'];
                    $selectedRoomTypes[$roomTypeId] = $roomData;
                }
            }

            if ($totalRooms < 3) {
                Log::warning('Travel Agency Booking Failed: Insufficient Rooms', ['total_rooms' => $totalRooms]);
                return back()->withErrors(['room_types' => 'A minimum of 3 rooms total is required.'])->withInput();
            }

            // Validate room types and availability
            foreach ($selectedRoomTypes as $roomTypeId => $roomData) {
                $roomType = RoomType::find($roomTypeId);
                if (!$roomType) {
                    Log::warning('Travel Agency Booking Failed: Invalid Room Type', ['room_type_id' => $roomTypeId]);
                    return back()->withErrors(["room_types.{$roomTypeId}" => 'Invalid room type selected.'])->withInput();
                }

                if ($roomData['occupants'] > $roomType->max_occupants) {
                    Log::warning('Travel Agency Booking Failed: Occupants Exceed Limit', [
                        'room_type_id' => $roomTypeId,
                        'occupants' => $roomData['occupants'],
                        'max_occupants' => $roomType->max_occupants
                    ]);
                    return back()->withErrors(["room_types.{$roomTypeId}.occupants" => "Number of occupants exceeds maximum for {$roomType->name}."])->withInput();
                }

                if ($roomType->is_suite && $roomData['quantity'] > 3) {
                    Log::warning('Travel Agency Booking Failed: Suite Quantity Exceeds Limit', [
                        'room_type_id' => $roomTypeId,
                        'quantity' => $roomData['quantity']
                    ]);
                    return back()->withErrors(["room_types.{$roomTypeId}.quantity" => "Maximum 3 rooms allowed per booking for {$roomType->name}."])->withInput();
                }

                $availableRooms = Room::where('branch_id', $data['branch_id'])
                                     ->where('room_type_id', $roomTypeId)
                                     ->where('status', 'available')
                                     ->count();
                if ($availableRooms < $roomData['quantity']) {
                    Log::warning('Travel Agency Booking Failed: Insufficient Available Rooms', [
                        'room_type_id' => $roomTypeId,
                        'available' => $availableRooms,
                        'requested' => $roomData['quantity']
                    ]);
                    return back()->withErrors(["room_types.{$roomTypeId}.quantity" => "Not enough available rooms for {$roomType->name}."])->withInput();
                }
            }

            // Start transaction
            DB::beginTransaction();

            try {
                $reservations = [];
                $totalQuotationPerRoom = $data['quotation_amount'] / $totalRooms;

                foreach ($selectedRoomTypes as $roomTypeId => $roomData) {
                    $roomType = RoomType::findOrFail($roomTypeId);
                    $checkOutDate = $roomData['check_out_date'] ?? null;

                    if ($roomType->is_suite) {
                        $checkInDate = Carbon::parse($roomData['check_in_date']);
                        if ($roomData['duration_type'] === 'weeks') {
                            if ($roomData['duration_value'] == 4) {
                                $roomData['duration_type'] = 'months';
                                $roomData['duration_value'] = 1;
                                $checkOutDate = $checkInDate->copy()->addMonth()->format('Y-m-d');
                            } else {
                                $checkOutDate = $checkInDate->copy()->addWeeks($roomData['duration_value'])->format('Y-m-d');
                            }
                        } else {
                            $checkOutDate = $checkInDate->copy()->addMonths($roomData['duration_value'])->format('Y-m-d');
                        }
                    }

                    for ($i = 0; $i < $roomData['quantity']; $i++) {
                        // Create reservation
                        $reservation = Reservation::create([
                            'user_id' => Auth::id(),
                            'branch_id' => $branchId,
                            'room_type_id' => $roomTypeId,
                            'check_in_date' => $roomData['check_in_date'],
                            'check_out_date' => $checkOutDate,
                            'number_of_occupants' => $roomData['occupants'],
                            'status' => 'pending',
                            'duration_type' => $roomType->is_suite ? $roomData['duration_type'] : null,
                            'duration_value' => $roomType->is_suite ? $roomData['duration_value'] : null,
                        ]);

                        Log::info('Reservation Created', [
                            'reservation_id' => $reservation->id,
                            'room_type_id' => $roomTypeId,
                            'quantity' => $roomData['quantity'],
                            'check_out_date' => $checkOutDate
                        ]);

                        // Create travel agency booking
                        $travelAgencyBooking = TravelAgencyBooking::create([
                            'travel_agency_id' => $data['travel_agency_id'],
                            'reservation_id' => $reservation->id,
                            'discount_percentage' => $data['discount_percentage'],
                            'quotation_amount' => $totalQuotationPerRoom
                        ]);

                        Log::info('Travel Agency Booking Created', [
                            'travel_agency_booking_id' => $travelAgencyBooking->id,
                            'reservation_id' => $reservation->id,
                            'travel_agency_id' => $data['travel_agency_id'],
                            'quotation_amount' => $totalQuotationPerRoom
                        ]);

                        // Create billing record
                        Billing::create([
                            'reservation_id' => $reservation->id,
                            'user_id' => $reservation->user_id,
                            'branch_id' => $branchId,
                            'total_amount' => $totalQuotationPerRoom,
                            'payment_method' => 'travel_agency',
                            'payment_status' => 'pending'
                        ]);

                        $reservations[] = $reservation;
                    }
                }

                DB::commit();

                Log::info('Travel Agency Booking Completed', [
                    'total_rooms' => $totalRooms,
                    'reservations_count' => count($reservations)
                ]);

                return redirect()->route('clerk.dashboard', ['branch_id' => $branchId])->with('success', 'Travel agency booking for ' . $totalRooms . ' rooms created successfully');
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('Travel Agency Booking Failed', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                return back()->withErrors(['error' => 'Failed to create booking: ' . $e->getMessage()])->withInput();
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::warning('Travel Agency Booking Validation Failed', [
                'errors' => $e->errors(),
                'request_data' => $request->all()
            ]);
            throw $e;
        } catch (\Exception $e) {
            Log::error('Unexpected Error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return back()->withErrors(['error' => 'An unexpected error occurred: ' . $e->getMessage()])->withInput();
        }
    }

    private function calculateTotal($reservation, $additionalCharges = [])
    {
        $roomType = $reservation->roomType;
        $total = 0;

        if ($roomType->is_suite) {
            if ($reservation->duration_type === 'weeks') {
                $total = $roomType->weekly_rate * $reservation->duration_value;
            } elseif ($reservation->duration_type === 'months') {
                $total = $roomType->monthly_rate * $reservation->duration_value;
            }
        } else {
            $days = Carbon::parse($reservation->check_out_date)->diffInDays(Carbon::parse($reservation->check_in_date));
            $total = $roomType->price_per_night * $days;
        }

        foreach (['restaurant_charges', 'room_service_charges', 'laundry_charges', 'telephone_charges', 'club_facility_charges'] as $charge) {
            $total += $additionalCharges[$charge] ?? 0;
        }

        return $total;
    }

    private function calculateSuiteDuration($checkInDate, $checkOutDate)
    {
        $checkIn = Carbon::parse($checkInDate);
        $checkOut = Carbon::parse($checkOutDate);
        $days = $checkOut->diffInDays($checkIn);

        $weeks = floor($days / 7);
        if ($weeks >= 4) {
            $months = floor($weeks / 4);
            return $months;
        }

        return $weeks;
    }

    protected function getBranchId(Request $request)
    {
        if (Auth::user()->role === 'admin') {
            $branchId = session('admin_selected_branch', $request->query('branch_id'));
            if (!$branchId) {
                abort(400, 'No branch selected.');
            }
            return $branchId;
        }
        return Auth::user()->branch_id;
    }
}
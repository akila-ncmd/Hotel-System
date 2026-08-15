<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use App\Models\RoomType;
use App\Models\Branch;
use App\Mail\ReservationConfirmation;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ReservationController extends Controller
{
    public function showRoomForm(Request $request)
    {
        $branch_id = $this->getBranchId($request);
        $branches = Branch::all();
        $roomTypes = RoomType::where('is_suite', false)->get();
        return view('customer.reservation-room', compact('branches', 'roomTypes', 'branch_id'));
    }

    public function showSuiteForm(Request $request)
    {
        $branch_id = $this->getBranchId($request);
        $branches = Branch::all();
        $roomTypes = RoomType::where('is_suite', true)->get();
        return view('customer.reservation-suite', compact('branches', 'roomTypes', 'branch_id'));
    }

    public function store(Request $request)
    {
        $isSuite = $request->has('is_suite') && $request->input('is_suite');

        $rules = [
            'branch_id' => 'required|exists:branches,id',
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

        // Validate branch_id for Admins
        if (Auth::user()->role === 'admin') {
            $adminBranchId = session('admin_selected_branch');
            if ($adminBranchId && $data['branch_id'] != $adminBranchId) {
                return back()->withErrors(['branch_id' => 'Selected branch does not match the admin’s chosen branch.']);
            }
        }

        $roomType = RoomType::find($data['room_type_id']);
        if ($data['number_of_occupants'] > $roomType->max_occupants) {
            return back()->withErrors(['number_of_occupants' => 'Exceeds maximum occupants']);
        }

        $data['user_id'] = Auth::id();
        $data['status'] = 'pending';

        if ($isSuite) {
            $checkInDate = Carbon::parse($data['check_in_date']);
            if ($data['duration_type'] === 'weeks') {
                if ($data['duration_value'] == 4) {
                    // Treat 4 weeks as 1 month
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
        Mail::to(Auth::user()->email)->send(new ReservationConfirmation($reservation));

        return redirect()->route('customer.reservations', ['branch_id' => $data['branch_id']])->with('success', 'Reservation created');
    }

    public function index(Request $request)
    {
        $branch_id = $this->getBranchId($request);
        $query = Reservation::where('user_id', Auth::id())->with(['branch', 'roomType']);
        if ($branch_id && Auth::user()->role === 'admin') {
            $query->where('branch_id', $branch_id);
        }
        $reservations = $query->get();
        return view('customer.manage-reservations', compact('reservations', 'branch_id'));
    }

    public function edit($id, Request $request)
    {
        $reservation = Reservation::where('user_id', Auth::id())->findOrFail($id);
        $branch_id = $this->getBranchId($request);
        if (Auth::user()->role === 'admin' && $branch_id && $reservation->branch_id != $branch_id) {
            abort(403, 'Unauthorized: Reservation does not belong to the selected branch.');
        }
        $branches = Branch::all();
        $roomTypes = RoomType::where('is_suite', $reservation->roomType->is_suite)->get();
        return view('customer.reservation-edit', compact('reservation', 'branches', 'roomTypes', 'branch_id'));
    }

    public function update(Request $request, $id)
    {
        $reservation = Reservation::where('user_id', Auth::id())->findOrFail($id);
        $isSuite = $reservation->roomType->is_suite;

        $rules = [
            'branch_id' => 'required|exists:branches,id',
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

        // Validate branch_id for Admins
        if (Auth::user()->role === 'admin') {
            $adminBranchId = session('admin_selected_branch');
            if ($adminBranchId && $data['branch_id'] != $adminBranchId) {
                return back()->withErrors(['branch_id' => 'Selected branch does not match the admin’s chosen branch.']);
            }
        }

        $roomType = RoomType::find($data['room_type_id']);
        if ($data['number_of_occupants'] > $roomType->max_occupants) {
            return back()->withErrors(['number_of_occupants' => 'Exceeds maximum occupants']);
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
        return redirect()->route('customer.reservations', ['branch_id' => $data['branch_id']])->with('success', 'Reservation updated');
    }

    public function cancel($id, Request $request)
    {
        $reservation = Reservation::where('user_id', Auth::id())->findOrFail($id);
        $branch_id = $this->getBranchId($request);
        if (Auth::user()->role === 'admin' && $branch_id && $reservation->branch_id != $branch_id) {
            abort(403, 'Unauthorized: Reservation does not belong to the selected branch.');
        }
        if ($reservation->status === 'pending' || $reservation->status === 'confirmed') {
            $reservation->update(['status' => 'cancelled']);
            return redirect()->route('customer.reservations', ['branch_id' => $branch_id])->with('success', 'Reservation cancelled');
        }
        return back()->withErrors(['status' => 'Cannot cancel this reservation']);
    }

    protected function getBranchId(Request $request)
    {
        if (Auth::user()->role === 'admin') {
            return session('admin_selected_branch', $request->query('branch_id'));
        }
        return $request->input('branch_id', $request->query('branch_id'));
    }
}
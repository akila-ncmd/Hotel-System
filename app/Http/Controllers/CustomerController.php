<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class CustomerController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:customer']);
    }

    public function editProfile()
    {
        $user = Auth::user();
        $countries = $this->getCountries();
        return view('customer.edit-profile', compact('user', 'countries'));
    }

    public function updateProfile(Request $request)
    {
        try {
            $user = Auth::user();

            Log::info('Customer Profile Update Request', [
                'user_id' => $user->id,
                'name' => $request->name,
                'email' => $request->email,
                'nationality' => $request->nationality,
                'contact_number' => $request->contact_number,
            ]);

            $rules = [
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
                'nationality' => 'required|string|max:255',
                'contact_number' => 'required|string|max:20',
                'password' => 'nullable|string|min:8|confirmed',
            ];

            $data = $request->validate($rules);

            $user->name = $data['name'];
            $user->email = $data['email'];
            $user->nationality = $data['nationality'];
            $user->contact_number = $data['contact_number'];

            if ($request->filled('password')) {
                $user->password = Hash::make($data['password']);
            }

            $user->save();

            Log::info('Customer Profile Updated', [
                'user_id' => $user->id,
                'updated_fields' => array_keys($request->except(['password', 'password_confirmation', '_token', '_method'])),
            ]);

            return redirect()->route('customer.edit-profile')->with('success', 'Profile updated successfully');

        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::warning('Customer Profile Update Validation Failed', [
                'user_id' => Auth::id(),
                'errors' => $e->errors(),
            ]);
            throw $e;
        } catch (\Exception $e) {
            Log::error('Customer Profile Update Failed', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return back()->withErrors(['error' => 'Failed to update profile: ' . $e->getMessage()])->withInput();
        }
    }

    protected function getCountries()
    {
        return json_decode(file_get_contents(resource_path('data/countries.json')), true);
    }
}
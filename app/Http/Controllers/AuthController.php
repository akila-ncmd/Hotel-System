<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Branch;
use App\Models\Reservation;
use App\Mail\RegistrationConfirmation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function __construct()
    {
        $this->middleware('guest')->except([
            'logout',
            'selectBranch',
            'viewBranchData',
            'showStaffRegisterForm',
            'registerStaff'
        ]);
        $this->middleware('auth')->only([
            'selectBranch', 
            'viewBranchData', 
            'logout', 
            'showStaffRegisterForm',
            'registerStaff'
        ]);
        $this->middleware('role:admin')->only([
            'selectBranch', 
            'viewBranchData', 
            'showStaffRegisterForm', 
            'registerStaff'
        ]);
    }

    public function showLoginForm()
    {
        $branches = Branch::all();
        return view('auth.login', compact('branches'));
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
            'role' => 'required|in:customer,clerk,manager,admin',
            'branch_id' => 'required_if:role,clerk,manager|nullable|exists:branches,id',
        ]);

        $key = 'login:' . $request->email . '|' . $request->ip();
        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);
            return back()->withErrors(['email' => "Too many login attempts. Please try again in {$seconds} seconds."]);
        }

        if (Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
            $user = Auth::user();

            if ($user->role !== $request->role) {
                Auth::logout();
                RateLimiter::hit($key, 60);
                Log::warning('Login attempt with mismatched role', [
                    'email' => $request->email,
                    'selected_role' => $request->role,
                    'user_role' => $user->role,
                ]);
                return back()->withErrors(['role' => 'Selected role does not match your account.']);
            }

            if (in_array($user->role, ['clerk', 'manager'])) {
                if (!$request->branch_id || $user->branch_id != $request->branch_id) {
                    Auth::logout();
                    RateLimiter::hit($key, 60);
                    Log::warning('Login attempt with invalid branch', [
                        'email' => $request->email,
                        'user_branch_id' => $user->branch_id,
                        'selected_branch_id' => $request->branch_id,
                    ]);
                    return back()->withErrors(['branch_id' => 'Invalid branch selection for your account.']);
                }
            }

            if ($user->role === 'admin') {
                $request->session()->forget('admin_selected_branch');
            }

            RateLimiter::clear($key);

            Log::info('User logged in successfully', [
                'user_id' => $user->id,
                'email' => $user->email,
                'role' => $user->role,
                'branch_id' => $user->branch_id ?? null,
            ]);

            return match ($user->role) {
                'admin' => redirect()->route('admin.dashboard'),
                'customer' => redirect()->route('customer.reservations'),
                'clerk' => redirect()->route('clerk.dashboard', ['branch_id' => $user->branch_id]),
                'manager' => redirect()->route('manager.dashboard', ['branch_id' => $user->branch_id]),
                default => redirect()->route('dashboard'),
            };
        }

        RateLimiter::hit($key, 60);
        Log::warning('Failed login attempt', [
            'email' => $request->email,
            'ip' => $request->ip(),
        ]);

        return back()->withErrors(['email' => 'Invalid email or password.']);
    }

    public function showRegisterForm()
    {
        $countries = $this->getCountries();
        return view('auth.register', compact('countries'));
    }

    protected function validator(array $data)
    {
        $nationality = $data['nationality'] ?? '';
        $contactNumberRules = $nationality === 'Sri Lanka' 
            ? ['required', 'string', 'max:20', 'regex:/^\+\d{1,4}\d{10}$/']
            : ['required', 'string', 'max:20', 'regex:/^\+\d{1,4}\d{7,15}$/'];

        return Validator::make($data, [
            'name' => [
                'required', 
                'string', 
                'min:5', 
                'max:100', 
                'regex:/^[A-Za-z\s]+$/', 
                function ($attribute, $value, $fail) {
                    $parts = array_filter(explode(' ', trim($value)));
                    if (count($parts) < 2) {
                        $fail('The name must contain at least two parts (e.g., First Last).');
                    }
                }
            ],
            'email' => ['required', 'string', 'email', 'min:3', 'max:50', 'unique:users', 'regex:/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/'],
            'password' => ['required', 'string', 'min:8', 'max:20', 'confirmed'],
            'nationality' => ['required', 'string', 'max:255'],
            'contact_number' => $contactNumberRules,
        ], [
            'name.regex' => 'Name can only contain letters and spaces.',
            'name.min' => 'Name must be at least 5 characters.',
            'email.regex' => 'Please enter a valid email address.',
            'email.max' => 'Email cannot exceed 50 characters.',
            'email.min' => 'Email must be at least 3 characters.',
            'password.min' => 'Password must be at least 8 characters.',
            'password.max' => 'Password cannot exceed 20 characters.',
            'contact_number.regex' => $nationality === 'Sri Lanka' 
                ? 'Contact number must be exactly 10 digits for Sri Lanka.'
                : 'Contact number must be 7-15 digits.'
        ]);
    }

    public function register(Request $request)
    {
        try {
            $this->validator($request->all())->validate();

            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'nationality' => $request->nationality,
                'contact_number' => $request->contact_number,
                'role' => 'customer',
            ]);

            Mail::to($user->email)->send(new RegistrationConfirmation($user));

            Log::info('Customer Registered and Email Sent', [
                'user_id' => $user->id,
                'email' => $user->email,
            ]);

            Auth::login($user);

            return redirect()->route('customer.reservations')->with('success', 'Registration successful! A confirmation email has been sent.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::warning('Customer Registration Validation Failed', [
                'errors' => $e->errors(),
                'request_data' => $request->except(['password', 'password_confirmation']),
            ]);
            throw $e;
        } catch (\Exception $e) {
            Log::error('Customer Registration Failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return back()->withErrors(['error' => 'Failed to register: ' . $e->getMessage()])->withInput();
        }
    }

    public function checkEmail(Request $request)
    {
        $email = $request->input('email');
        $exists = User::where('email', $email)->exists();
        return response()->json(['isUnique' => !$exists]);
    }

    public function showStaffRegisterForm()
    {
        $branches = Branch::all();
        return view('auth.staff-register', compact('branches'));
    }

    public function registerStaff(Request $request)
    {
        try {
            $data = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:users,email',
                'password' => 'required|confirmed|min:8',
                'role' => 'required|in:clerk,manager',
                'branch_id' => 'required|exists:branches,id',
            ]);

            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'role' => $data['role'],
                'branch_id' => $data['branch_id'],
            ]);

            Mail::to($user->email)->send(new RegistrationConfirmation($user));

            Log::info('Staff Registered and Email Sent', [
                'user_id' => $user->id,
                'email' => $user->email,
                'role' => $user->role,
                'branch_id' => $user->branch_id,
            ]);

            return redirect()->route('admin.dashboard')->with('success', 'Staff member registered successfully!');
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::warning('Staff Registration Validation Failed', [
                'errors' => $e->errors(),
                'request_data' => $request->except(['password', 'password_confirmation']),
            ]);
            throw $e;
        } catch (\Exception $e) {
            Log::error('Staff Registration Failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return back()->withErrors(['error' => 'Failed to register: ' . $e->getMessage()])->withInput();
        }
    }

    public function selectBranch(Request $request)
    {
        $data = $request->validate([
            'branch_id' => 'required|exists:branches,id',
        ]);

        $request->session()->put('admin_selected_branch', $data['branch_id']);

        Log::info('Admin selected branch', [
            'user_id' => Auth::id(),
            'branch_id' => $data['branch_id'],
        ]);

        return redirect()->route('admin.branch.data', ['branch_id' => $data['branch_id']]);
    }

    public function viewBranchData(Request $request, $branch_id)
    {
        $branch = Branch::findOrFail($branch_id);
        $reservations = Reservation::where('branch_id', $branch_id)->with(['user', 'branch'])->get();

        return view('admin.branch-data', compact('branch', 'reservations'));
    }

    public function logout(Request $request)
    {
        $userId = Auth::id();
        $role = Auth::user()->role ?? 'unknown';

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        Log::info('User logged out', [
            'user_id' => $userId,
            'role' => $role,
        ]);

        return redirect()->route('login');
    }

    protected function getCountries()
    {
        return json_decode(file_get_contents(resource_path('data/countries.json')), true);
    }
}
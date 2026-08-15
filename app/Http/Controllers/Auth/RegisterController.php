<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;

class RegisterController extends Controller
{
    use RegistersUsers;

    protected $redirectTo = '/dashboard';

    public function __construct()
    {
        $this->middleware('guest');
    }

    public function showRegistrationForm()
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

    protected function create(array $data)
    {
        return User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'nationality' => $data['nationality'],
            'contact_number' => $data['contact_number'],
            'role' => 'customer',
        ]);
    }

    protected function getCountries()
    {
        return json_decode(file_get_contents(resource_path('data/countries.json')), true);
    }

    public function checkEmail(Request $request)
    {
        $email = $request->input('email');
        $exists = User::where('email', $email)->exists();
        return response()->json(['isUnique' => !$exists]);
    }
}
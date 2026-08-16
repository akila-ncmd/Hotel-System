@extends('layouts.app')

@section('content')
<div class="ds-page-head ds-reveal">
    <span class="ds-eyebrow">Your account</span>
    <h1>Profile</h1>
    <p class="ds-lead mt-3 mb-0">
        Keep your contact details current &mdash; we use them to confirm every booking.
    </p>
</div>

<div class="ds-edit-wrap">
    @if (session('success'))
        <div class="alert alert-success ds-reveal">
            {{ session('success') }}
        </div>
    @endif
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    <div class="ds-panel ds-reveal">
    <form method="POST" action="{{ route('customer.update-profile') }}" id="editProfileForm">
        @csrf
        @method('PUT')
        <div class="mb-3">
            <label for="name" class="form-label">Name</label>
            <input type="text" name="name" id="name" class="form-control" value="{{ old('name', auth()->user()->name) }}" required>
            <div id="name-error" class="text-danger d-none"></div>
            @error('name') <span class="text-danger">{{ $message }}</span> @enderror
        </div>
        <div class="mb-3">
            <label for="email" class="form-label">Email</label>
            <input type="email" name="email" id="email" class="form-control" value="{{ old('email', auth()->user()->email) }}" required>
            <div id="email-error" class="text-danger d-none"></div>
            @error('email') <span class="text-danger">{{ $message }}</span> @enderror
        </div>
        <div class="mb-3">
            <label for="nationality" class="form-label">Nationality</label>
            <select name="nationality" id="nationality" class="form-control" required>
                <option value="">Select a nationality</option>
                @foreach ($countries as $country)
                    <option value="{{ $country['name'] }}" data-phone-code="{{ $country['phone_code'] }}"
                        {{ old('nationality', auth()->user()->nationality) == $country['name'] ? 'selected' : '' }}>
                        {{ $country['name'] }}
                    </option>
                @endforeach
            </select>
            <div id="nationality-error" class="text-danger d-none"></div>
            @error('nationality') <span class="text-danger">{{ $message }}</span> @enderror
        </div>
        <div class="mb-3">
            <label for="contact_number" class="form-label">Contact Number</label>
            <div class="input-group">
                <span class="input-group-text" id="country-code"></span>
                <input type="text" name="contact_number" id="contact_number" class="form-control" value="{{ old('contact_number', auth()->user()->contact_number) }}" required>
            </div>
            <div id="contact-number-error" class="text-danger d-none"></div>
            @error('contact_number') <span class="text-danger">{{ $message }}</span> @enderror
        </div>
        <div class="mb-3">
            <label for="password" class="form-label">New Password (Optional)</label>
            <input type="password" name="password" id="password" class="form-control">
            <div id="password-error" class="text-danger d-none"></div>
            @error('password') <span class="text-danger">{{ $message }}</span> @enderror
        </div>
        <div class="mb-3">
            <label for="password_confirmation" class="form-label">Confirm New Password</label>
            <input type="password" name="password_confirmation" id="password_confirmation" class="form-control">
            <div id="password-confirmation-error" class="text-danger d-none"></div>
        </div>
        <div class="d-flex flex-wrap gap-2 mt-5 pt-4" style="border-top: 1px solid var(--ds-shell);">
            <button type="submit" class="btn btn-primary">Save profile</button>
            <a href="{{ route('dashboard') }}" class="btn btn-link">Cancel</a>
        </div>
    </form>
    </div>
</div>

<style>
    .ds-edit-wrap { max-width: 44rem; }
</style>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const nameInput = document.getElementById('name');
    const emailInput = document.getElementById('email');
    const nationalitySelect = document.getElementById('nationality');
    const contactNumberInput = document.getElementById('contact_number');
    const passwordInput = document.getElementById('password');
    const passwordConfirmationInput = document.getElementById('password_confirmation');
    const countryCodeSpan = document.getElementById('country-code');
    const form = document.getElementById('editProfileForm');

    const errorElements = {
        name: document.getElementById('name-error'),
        email: document.getElementById('email-error'),
        nationality: document.getElementById('nationality-error'),
        contact_number: document.getElementById('contact-number-error'),
        password: document.getElementById('password-error'),
        password_confirmation: document.getElementById('password-confirmation-error')
    };

    // Track interaction state
    const touched = {
        name: false,
        email: false,
        nationality: false,
        contact_number: false,
        password: false,
        password_confirmation: false
    };

    // Validation regex patterns
    const nameRegex = /^[A-Za-z\s]+$/;
    const emailRegex = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;

    // Country-specific phone number length rules
    const phoneNumberRules = {
        'Sri Lanka': { length: 10, error: 'Contact number must be exactly 10 digits for Sri Lanka.' },
        'default': { min: 7, max: 15, error: 'Contact number must be 7-15 digits.' }
    };

    // Show error message
    function showError(field, message) {
        errorElements[field].textContent = message;
        errorElements[field].classList.remove('d-none');
    }

    // Clear error message
    function clearError(field) {
        errorElements[field].classList.add('d-none');
        errorElements[field].textContent = '';
    }

    // Validate Name
    function validateName() {
        if (!touched.name) return true;
        const value = nameInput.value.trim();
        if (value.length < 5) {
            showError('name', 'Name must be at least 5 characters.');
            return false;
        }
        if (value.length > 100) {
            showError('name', 'Name cannot exceed 100 characters.');
            nameInput.value = value.slice(0, 100);
            return false;
        }
        if (value && !nameRegex.test(value)) {
            showError('name', 'Name can only contain letters and spaces.');
            return false;
        }
        const parts = value.split(/\s+/).filter(part => part.length > 0);
        if (parts.length < 2) {
            showError('name', 'Name must contain at least two parts (e.g., First Last).');
            return false;
        }
        clearError('name');
        return true;
    }

    // Validate Email with AJAX uniqueness check
    function validateEmail() {
        if (!touched.email) return true;
        const value = emailInput.value.trim();
        if (value.length > 50) {
            showError('email', 'Email cannot exceed 50 characters.');
            emailInput.value = value.slice(0, 50);
            return false;
        }
        if (value && !emailRegex.test(value)) {
            showError('email', 'Please enter a valid email address.');
            return false;
        }
        if (value && value !== "{{ auth()->user()->email }}") {
            fetch('/check-email', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({ email: value })
            })
            .then(response => response.json())
            .then(data => {
                if (!data.isUnique && touched.email) {
                    showError('email', 'This email is already registered.');
                } else {
                    clearError('email');
                }
            })
            .catch(() => showError('email', 'Error checking email.'));
        } else {
            clearError('email');
        }
        return value ? emailRegex.test(value) : true;
    }

    // Validate Nationality
    function validateNationality() {
        if (!touched.nationality) return true;
        const value = nationalitySelect.value;
        if (!value) {
            showError('nationality', 'Please select a nationality.');
            contactNumberInput.disabled = true;
            countryCodeSpan.textContent = '';
            return false;
        }
        clearError('nationality');
        const phoneCode = nationalitySelect.selectedOptions[0].dataset.phoneCode || '';
        countryCodeSpan.textContent = phoneCode;
        contactNumberInput.disabled = false;
        if (!contactNumberInput.value || contactNumberInput.value === countryCodeSpan.dataset.currentCode) {
            contactNumberInput.value = phoneCode;
        }
        countryCodeSpan.dataset.currentCode = phoneCode;
        return true;
    }

    // Validate Contact Number
    function validateContactNumber() {
        if (!touched.contact_number) return true;
        const value = contactNumberInput.value.trim();
        const phoneCode = countryCodeSpan.textContent;
        const nationality = nationalitySelect.value;

        if (!nationality) {
            showError('contact_number', 'Please select a nationality first.');
            contactNumberInput.value = '';
            return false;
        }

        const numberPart = value.replace(phoneCode, '').trim();
        if (numberPart.length > 15) {
            showError('contact_number', 'Contact number cannot exceed 15 digits.');
            contactNumberInput.value = phoneCode + numberPart.slice(0, 15);
            return false;
        }

        const rule = phoneNumberRules[nationality] || phoneNumberRules['default'];
        if (nationality === 'Sri Lanka') {
            if (!/^\d{10}$/.test(numberPart)) {
                showError('contact_number', rule.error);
                return false;
            }
        } else {
            if (!/^\d{7,15}$/.test(numberPart)) {
                showError('contact_number', rule.error);
                return false;
            }
        }
        clearError('contact_number');
        return true;
    }

    // Validate Password
    function validatePassword() {
        if (!touched.password) return true;
        const value = passwordInput.value;
        if (value && value.length < 8) {
            showError('password', 'Password must be at least 8 characters.');
            return false;
        }
        if (value.length > 20) {
            showError('password', 'Password cannot exceed 20 characters.');
            passwordInput.value = value.slice(0, 20);
            return false;
        }
        clearError('password');
        return true;
    }

    // Validate Password Confirmation
    function validatePasswordConfirmation() {
        if (!touched.password_confirmation) return true;
        const password = passwordInput.value;
        const confirmPassword = passwordConfirmationInput.value;
        if (password && confirmPassword !== password) {
            showError('password_confirmation', 'Passwords did not match.');
            return false;
        }
        if (confirmPassword && confirmPassword.length < 8) {
            showError('password_confirmation', 'Password must be at least 8 characters.');
            return false;
        }
        if (confirmPassword.length > 20) {
            showError('password_confirmation', 'Password cannot exceed 20 characters.');
            passwordConfirmationInput.value = confirmPassword.slice(0, 20);
            return false;
        }
        clearError('password_confirmation');
        return true;
    }

    // Prevent invalid characters in Name
    nameInput.addEventListener('keypress', (e) => {
        if (!/[A-Za-z\s]/.test(e.key)) {
            e.preventDefault();
            touched.name = true;
            validateName();
        }
    });

    // Mark fields as touched and validate on input
    nameInput.addEventListener('input', () => {
        touched.name = true;
        validateName();
    });
    emailInput.addEventListener('input', () => {
        touched.email = true;
        validateEmail();
    });
    nationalitySelect.addEventListener('change', () => {
        touched.nationality = true;
        validateNationality();
        touched.contact_number = true;
        validateContactNumber();
    });
    contactNumberInput.addEventListener('input', () => {
        touched.contact_number = true;
        validateContactNumber();
    });
    passwordInput.addEventListener('input', () => {
        touched.password = true;
        validatePassword();
        if (touched.password_confirmation) validatePasswordConfirmation();
    });
    passwordConfirmationInput.addEventListener('input', () => {
        touched.password_confirmation = true;
        validatePasswordConfirmation();
    });

    // Prevent non-digits in contact number
    contactNumberInput.addEventListener('keypress', (e) => {
        if (!/[0-9]/.test(e.key) && e.key !== 'Backspace' && e.key !== 'Delete') {
            e.preventDefault();
        }
    });

    // Validate on blur
    nameInput.addEventListener('blur', () => {
        touched.name = true;
        validateName();
    });
    emailInput.addEventListener('blur', () => {
        touched.email = true;
        validateEmail();
    });
    nationalitySelect.addEventListener('blur', () => {
        touched.nationality = true;
        validateNationality();
    });
    contactNumberInput.addEventListener('blur', () => {
        touched.contact_number = true;
        validateContactNumber();
    });
    passwordInput.addEventListener('blur', () => {
        touched.password = true;
        validatePassword();
    });
    passwordConfirmationInput.addEventListener('blur', () => {
        touched.password_confirmation = true;
        validatePasswordConfirmation();
    });

    // Form submission validation
    form.addEventListener('submit', (e) => {
        touched.name = true;
        touched.email = true;
        touched.nationality = true;
        touched.contact_number = true;
        if (passwordInput.value) touched.password = true;
        if (passwordConfirmationInput.value) touched.password_confirmation = true;

        const isNameValid = validateName();
        const isEmailValid = validateEmail();
        const isNationalityValid = validateNationality();
        const isContactNumberValid = validateContactNumber();
        const isPasswordValid = !passwordInput.value || validatePassword();
        const isPasswordConfirmationValid = !passwordConfirmationInput.value || validatePasswordConfirmation();

        if (!isNameValid || !isEmailValid || !isNationalityValid || !isContactNumberValid || !isPasswordValid || !isPasswordConfirmationValid) {
            e.preventDefault();
        }
    });

    // Initialize country code
    if (nationalitySelect.value) {
        const phoneCode = nationalitySelect.selectedOptions[0].dataset.phoneCode || '';
        countryCodeSpan.textContent = phoneCode;
        countryCodeSpan.dataset.currentCode = phoneCode;
        contactNumberInput.disabled = false;
    }
});
</script>
@endsection
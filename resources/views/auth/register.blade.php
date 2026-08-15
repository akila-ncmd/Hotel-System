@extends('layouts.app')

@section('content')
<div class="container d-flex justify-content-center align-items-center min-vh-100">
    <div class="card shadow-lg p-4 w-100" style="max-width: 500px; border-radius: 12px;">
        <div class="text-center mb-4">
            <h2 class="fw-bold">Register</h2>
            <p class="text-muted">Create your account</p>
        </div>

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('register') }}" id="registerForm">
            @csrf
            <!-- Name -->
            <div class="mb-3">
                <label for="name" class="form-label fw-semibold">Name</label>
                <input type="text" name="name" id="name" class="form-control form-control-lg @error('name') is-invalid @enderror" value="{{ old('name') }}" required placeholder="First Last">
                @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <!-- Email -->
            <div class="mb-3">
                <label for="email" class="form-label fw-semibold">Email</label>
                <input type="email" name="email" id="email" class="form-control form-control-lg @error('email') is-invalid @enderror" value="{{ old('email') }}" required placeholder="example@email.com">
                @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <!-- Password -->
            <div class="mb-3">
                <label for="password" class="form-label fw-semibold">Password</label>
                <input type="password" name="password" id="password" class="form-control form-control-lg @error('password') is-invalid @enderror" required placeholder="Enter password">
                @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <!-- Confirm Password -->
            <div class="mb-3">
                <label for="password_confirmation" class="form-label fw-semibold">Confirm Password</label>
                <input type="password" name="password_confirmation" id="password_confirmation" class="form-control form-control-lg @error('password_confirmation') is-invalid @enderror" required placeholder="Re-enter password">
                @error('password_confirmation') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <!-- Nationality -->
            <div class="mb-3">
                <label for="nationality" class="form-label fw-semibold">Nationality</label>
                <select name="nationality" id="nationality" class="form-select form-select-lg @error('nationality') is-invalid @enderror" required>
                    <option value="">Select a nationality</option>
                    @foreach ($countries as $country)
                        <option value="{{ $country['name'] }}" data-phone-code="{{ $country['phone_code'] }}"
                            {{ old('nationality') == $country['name'] ? 'selected' : '' }}>
                            {{ $country['name'] }}
                        </option>
                    @endforeach
                </select>
                @error('nationality') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <!-- Contact Number -->
            <div class="mb-4">
                <label for="contact_number" class="form-label fw-semibold">Contact Number</label>
                <div class="input-group input-group-lg">
                    <span class="input-group-text" id="country-code"></span>
                    <input type="text" name="contact_number" id="contact_number" class="form-control @error('contact_number') is-invalid @enderror" value="{{ old('contact_number') }}" required disabled placeholder="Enter number">
                </div>
                @error('contact_number') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <!-- Submit -->
            <div class="d-grid mb-3">
                <button type="submit" class="btn btn-primary btn-lg fw-semibold shadow-sm">Register</button>
            </div>

            <!-- Login link -->
            <div class="text-center">
                <span class="text-muted">Already have an account?</span>
                <a href="{{ route('login') }}" class="fw-semibold text-decoration-none">Login here</a>
            </div>
        </form>
    </div>
</div>

<script>
// JS validation and phone code logic (same as before)
document.addEventListener('DOMContentLoaded', () => {
    const nationalitySelect = document.getElementById('nationality');
    const contactNumberInput = document.getElementById('contact_number');
    const countryCodeSpan = document.getElementById('country-code');

    function updateContactInput() {
        const selectedOption = nationalitySelect.selectedOptions[0];
        const code = selectedOption ? selectedOption.dataset.phoneCode : '';
        countryCodeSpan.textContent = code;
        contactNumberInput.disabled = !code;
        if (!contactNumberInput.value || contactNumberInput.value === countryCodeSpan.dataset.currentCode) {
            contactNumberInput.value = code;
        }
        countryCodeSpan.dataset.currentCode = code;
    }

    nationalitySelect.addEventListener('change', updateContactInput);
    updateContactInput(); // initialize on page load
});
</script>
@endsection

@extends('layouts.app')

@section('content')
<div class="container d-flex justify-content-center align-items-center min-vh-100">
    <div class="card shadow-lg p-4 w-100" style="max-width: 450px; border-radius: 12px;">
        <div class="text-center mb-4">
            <h2 class="fw-bold">Login</h2>
            <p class="text-muted">Access your account</p>
        </div>

        <form method="POST" action="{{ route('login') }}">
            @csrf
            <!-- Email -->
            <div class="mb-3">
                <label class="form-label fw-semibold">Email</label>
                <input type="email" name="email" class="form-control form-control-lg @error('email') is-invalid @enderror" value="{{ old('email') }}" placeholder="Enter your email" required>
                @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <!-- Password -->
            <div class="mb-3">
                <label class="form-label fw-semibold">Password</label>
                <input type="password" name="password" class="form-control form-control-lg @error('password') is-invalid @enderror" placeholder="Enter your password" required>
                @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <!-- Role -->
            <div class="mb-3">
                <label class="form-label fw-semibold">Role</label>
                <select name="role" class="form-select form-select-lg @error('role') is-invalid @enderror" id="role-select" required>
                    <option value="">Select Role</option>
                    <option value="customer" {{ old('role') == 'customer' ? 'selected' : '' }}>Customer</option>
                    <option value="clerk" {{ old('role') == 'clerk' ? 'selected' : '' }}>Clerk</option>
                    <option value="manager" {{ old('role') == 'manager' ? 'selected' : '' }}>Manager</option>
                    <option value="admin" {{ old('role') == 'admin' ? 'selected' : '' }}>Admin</option>
                </select>
                @error('role') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <!-- Branch (conditional) -->
            <div class="mb-4" id="branch-selection" style="display:none;">
                <label class="form-label fw-semibold">Branch</label>
                <select name="branch_id" id="branch_id" class="form-select form-select-lg @error('branch_id') is-invalid @enderror">
                    <option value="">Select Branch</option>
                    @foreach($branches as $branch)
                        <option value="{{ $branch->id }}" {{ old('branch_id') == $branch->id ? 'selected' : '' }}>{{ $branch->name }}</option>
                    @endforeach
                </select>
                @error('branch_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <!-- Submit -->
            <div class="d-grid mb-3">
                <button type="submit" class="btn btn-primary btn-lg fw-semibold shadow-sm">Login</button>
            </div>

            <!-- Register link -->
            <div class="text-center">
                <span class="text-muted">Don't have an account?</span>
                <a href="{{ route('register') }}" class="fw-semibold text-decoration-none">Register as Customer</a>
            </div>
        </form>
    </div>
</div>

<script>
    const roleSelect = document.getElementById('role-select');
    const branchSelection = document.getElementById('branch-selection');
    const branchId = document.getElementById('branch_id');

    function toggleBranch() {
        if (roleSelect.value === 'clerk' || roleSelect.value === 'manager') {
            branchSelection.style.display = 'block';
            branchId.required = true;
        } else {
            branchSelection.style.display = 'none';
            branchId.required = false;
            branchId.value = '';
        }
    }

    roleSelect.addEventListener('change', toggleBranch);
    toggleBranch(); // initialize on page load
</script>
@endsection

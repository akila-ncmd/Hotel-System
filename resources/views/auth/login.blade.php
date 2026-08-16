@extends('layouts.app')

@section('fullwidth')
<div class="ds-auth">
    {{-- Left: the photograph. Hidden on small screens, where it would push the
         form below the fold for no benefit. --}}
    <aside class="ds-auth__aside d-none d-lg-block ds-reveal-img">
        <div class="ds-img" style="background-image: url('{{ asset('images/hotelTwo.jpg') }}');"></div>
        <div class="ds-auth__aside-veil"></div>
        <div class="ds-auth__aside-copy">
            <span class="ds-eyebrow" style="color: rgba(250,247,242,.75);">Welcome back</span>
            <h2 class="ds-auth__quote">
                The quiet luxury<br>of arriving<br>somewhere familiar.
            </h2>
            <p class="ds-auth__places">Colombo &middot; Kandy &middot; Galle</p>
        </div>
    </aside>

    {{-- Right: the form. --}}
    <div class="ds-auth__panel">
        <div class="ds-auth__form ds-reveal">
            <span class="ds-eyebrow">{{ config('app.name') }}</span>
            <h1 class="mb-2">Sign in</h1>
            <p class="ds-lead mb-4" style="font-size: 1rem;">
                Access your reservations and manage your stay.
            </p>

            @if ($errors->any() && !$errors->has('email') && !$errors->has('role') && !$errors->has('branch_id'))
                <div class="alert alert-danger">{{ $errors->first() }}</div>
            @endif

            <form method="POST" action="{{ route('login') }}" novalidate>
                @csrf

                <div class="mb-4">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" name="email" id="email"
                           class="form-control form-control-lg @error('email') is-invalid @enderror"
                           value="{{ old('email') }}" placeholder="you@example.com" required autofocus>
                    @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="mb-4">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" name="password" id="password"
                           class="form-control form-control-lg @error('password') is-invalid @enderror"
                           placeholder="••••••••" required>
                    @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="mb-4">
                    <label for="role-select" class="form-label">Signing in as</label>
                    <select name="role" id="role-select"
                            class="form-select form-select-lg @error('role') is-invalid @enderror" required>
                        <option value="">Select role</option>
                        <option value="customer" @selected(old('role') == 'customer')>Guest</option>
                        <option value="clerk"    @selected(old('role') == 'clerk')>Front desk</option>
                        <option value="manager"  @selected(old('role') == 'manager')>Manager</option>
                        <option value="admin"    @selected(old('role') == 'admin')>Administrator</option>
                    </select>
                    @error('role') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                {{-- Staff are pinned to a branch and must select their own. --}}
                <div class="mb-4" id="branch-selection" hidden>
                    <label for="branch_id" class="form-label">Your branch</label>
                    <select name="branch_id" id="branch_id"
                            class="form-select form-select-lg @error('branch_id') is-invalid @enderror">
                        <option value="">Select branch</option>
                        @foreach($branches as $branch)
                            <option value="{{ $branch->id }}" @selected(old('branch_id') == $branch->id)>
                                {{ $branch->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('branch_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="d-grid mt-5">
                    <button type="submit" class="btn btn-primary btn-lg">Sign in</button>
                </div>
            </form>

            <hr class="ds-rule my-4">

            <p class="mb-0 ds-muted" style="font-size: .92rem;">
                No account yet?
                <a href="{{ route('register') }}" class="ds-link ms-2">Create one</a>
            </p>
        </div>
    </div>
</div>

<style>
    .ds-auth {
        display: grid;
        grid-template-columns: 1fr;
        min-height: calc(100vh - 76px);
    }
    @media (min-width: 992px) {
        .ds-auth { grid-template-columns: 1.05fr .95fr; }
    }

    .ds-auth__aside { position: relative; overflow: hidden; }
    .ds-auth__aside .ds-img {
        position: absolute; inset: 0;
        background-size: cover;
        background-position: center;
    }
    .ds-auth__aside-veil {
        position: absolute; inset: 0;
        background: linear-gradient(200deg,
                    rgba(26,23,21,.30) 0%,
                    rgba(26,23,21,.62) 55%,
                    rgba(107,47,62,.55) 100%);
    }
    .ds-auth__aside-copy {
        position: absolute;
        left: clamp(2rem, 5vw, 4.5rem);
        right: clamp(2rem, 5vw, 4.5rem);
        bottom: clamp(2.5rem, 6vw, 5rem);
        color: var(--ds-ivory);
    }
    .ds-auth__quote {
        font-family: var(--ds-serif);
        font-weight: 400;   /* display face is Regular-only */
        font-size: clamp(2rem, 3.2vw, 3.1rem);
        line-height: 1.15;
        letter-spacing: -0.015em;
        color: var(--ds-ivory);
        margin: 0 0 1.5rem;
    }
    .ds-auth__places {
        font-size: .7rem;
        font-weight: 400;
        letter-spacing: .24em;
        text-transform: uppercase;
        color: rgba(250, 247, 242, .65);
        margin: 0;
    }

    .ds-auth__panel {
        display: flex;
        align-items: center;
        justify-content: center;
        padding: clamp(2.5rem, 6vw, 5rem) var(--ds-gutter);
        background: var(--ds-ivory);
    }
    .ds-auth__form { width: 100%; max-width: 27rem; }
</style>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    var roleSelect = document.getElementById('role-select');
    var branchSelection = document.getElementById('branch-selection');
    var branchId = document.getElementById('branch_id');

    // Only clerks and managers belong to a branch; asking a guest for one
    // would be meaningless, and login rejects a mismatch anyway.
    function toggleBranch() {
        var needsBranch = roleSelect.value === 'clerk' || roleSelect.value === 'manager';
        branchSelection.hidden = !needsBranch;
        branchId.required = needsBranch;
        if (!needsBranch) branchId.value = '';
    }

    roleSelect.addEventListener('change', toggleBranch);
    toggleBranch();
});
</script>
@endsection

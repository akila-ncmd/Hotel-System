@extends('layouts.app')

@section('fullwidth')
<div class="ds-auth ds-auth--reverse">
    <div class="ds-auth__panel">
        <div class="ds-auth__form ds-auth__form--wide ds-reveal">
            <span class="ds-eyebrow">{{ config('app.name') }}</span>
            <h1 class="mb-2">Create your account</h1>
            <p class="ds-lead mb-4" style="font-size: 1rem;">
                One account for every house &mdash; Colombo, Kandy and Galle.
            </p>

            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0 ps-3">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('register') }}" id="registerForm" novalidate>
                @csrf

                <div class="mb-4">
                    <label for="name" class="form-label">Full name</label>
                    <input type="text" name="name" id="name"
                           class="form-control form-control-lg @error('name') is-invalid @enderror"
                           value="{{ old('name') }}" required placeholder="First Last" autofocus>
                    @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="mb-4">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" name="email" id="email"
                           class="form-control form-control-lg @error('email') is-invalid @enderror"
                           value="{{ old('email') }}" required placeholder="you@example.com">
                    @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="row g-4">
                    <div class="col-sm-6 mb-1">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" name="password" id="password"
                               class="form-control form-control-lg @error('password') is-invalid @enderror"
                               required placeholder="At least 8 characters">
                        @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-sm-6 mb-1">
                        <label for="password_confirmation" class="form-label">Confirm password</label>
                        <input type="password" name="password_confirmation" id="password_confirmation"
                               class="form-control form-control-lg @error('password_confirmation') is-invalid @enderror"
                               required placeholder="Repeat it">
                        @error('password_confirmation') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div class="mt-4 mb-4">
                    <label for="nationality" class="form-label">Nationality</label>
                    <select name="nationality" id="nationality"
                            class="form-select form-select-lg @error('nationality') is-invalid @enderror" required>
                        <option value="">Select a nationality</option>
                        @foreach ($countries as $country)
                            <option value="{{ $country['name'] }}" data-phone-code="{{ $country['phone_code'] }}"
                                @selected(old('nationality') == $country['name'])>
                                {{ $country['name'] }}
                            </option>
                        @endforeach
                    </select>
                    @error('nationality') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="mb-4">
                    <label for="contact_number" class="form-label">Contact number</label>
                    <div class="ds-phone">
                        <span class="ds-phone__code" id="country-code">&mdash;</span>
                        <input type="text" name="contact_number" id="contact_number"
                               class="form-control form-control-lg @error('contact_number') is-invalid @enderror"
                               value="{{ old('contact_number') }}" required disabled
                               placeholder="Select a nationality first">
                    </div>
                    <div class="form-text">Include your country code, e.g. +94771234567.</div>
                    @error('contact_number') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                </div>

                <div class="d-grid mt-5">
                    <button type="submit" class="btn btn-primary btn-lg">Create account</button>
                </div>
            </form>

            <hr class="ds-rule my-4">

            <p class="mb-0 ds-muted" style="font-size: .92rem;">
                Already registered?
                <a href="{{ route('login') }}" class="ds-link ms-2">Sign in</a>
            </p>
        </div>
    </div>

    <aside class="ds-auth__aside d-none d-lg-block ds-reveal-img">
        <div class="ds-img" style="background-image: url('{{ asset('images/hotelThree.jpg') }}');"></div>
        <div class="ds-auth__aside-veil"></div>
        <div class="ds-auth__aside-copy">
            <span class="ds-eyebrow" style="color: rgba(250,247,242,.75);">Become a guest</span>
            <h2 class="ds-auth__quote">
                Rooms by the night.<br>Suites by the month.<br>Both by the sea.
            </h2>
            <p class="ds-auth__places">Reception open 24 hours</p>
        </div>
    </aside>
</div>

<style>
    .ds-auth {
        display: grid;
        grid-template-columns: 1fr;
        min-height: calc(100vh - 76px);
    }
    @media (min-width: 992px) {
        .ds-auth { grid-template-columns: 1.05fr .95fr; }
        /* Register mirrors login, so the two pages don't feel identical. */
        .ds-auth--reverse { grid-template-columns: .95fr 1.05fr; }
    }

    .ds-auth__aside { position: relative; overflow: hidden; }
    .ds-auth__aside .ds-img {
        position: absolute; inset: 0;
        background-size: cover;
        background-position: center;
    }
    .ds-auth__aside-veil {
        position: absolute; inset: 0;
        background: linear-gradient(160deg,
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
    .ds-auth__form--wide { max-width: 32rem; }

    /* Dial code sits inline with the underline input rather than in a
       Bootstrap input-group, which would reintroduce boxes and borders. */
    .ds-phone { display: flex; align-items: baseline; gap: .75rem; }
    .ds-phone__code {
        font-family: var(--ds-serif);
        font-size: 1.15rem;
        color: var(--ds-muted);
        border-bottom: 1px solid var(--ds-sand);
        padding: .8rem 0;
        min-width: 3.5rem;
    }
    .ds-phone .form-control { flex: 1; }
</style>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    var nationality = document.getElementById('nationality');
    var contact = document.getElementById('contact_number');
    var codeEl = document.getElementById('country-code');

    // The contact number is validated against an international pattern, so the
    // field stays locked until a nationality supplies a dial code.
    function updateContactInput() {
        var option = nationality.selectedOptions[0];
        var code = option ? (option.dataset.phoneCode || '') : '';

        codeEl.textContent = code || '—';
        contact.disabled = !code;
        contact.placeholder = code ? 'Your number' : 'Select a nationality first';

        if (!contact.value || contact.value === codeEl.dataset.currentCode) {
            contact.value = code;
        }
        codeEl.dataset.currentCode = code;
    }

    nationality.addEventListener('change', updateContactInput);
    updateContactInput();
});
</script>
@endsection

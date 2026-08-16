{{--
    Shared shell for the static guest-information pages (terms, privacy,
    cancellation, accessibility, FAQ).

    These pages are plain Blade with no controller: they have no data and no
    state, so routes/web.php wires them with Route::view().

    NOTE: the copy is a sensible starting draft, not reviewed legal advice.
    Anything published commercially should be passed to a lawyer first.
--}}
@extends('layouts.app')

@section('content')
    <article class="ds-legal">
        <header class="ds-page-head">
            <span class="ds-eyebrow">@yield('eyebrow', 'Guest information')</span>
            <h1>@yield('heading')</h1>
            @hasSection('updated')
                <p class="ds-legal__meta">Last updated @yield('updated')</p>
            @endif
        </header>

        @yield('body')

        <footer class="ds-legal__foot">
            <p class="mb-0">
                Questions about this page? Call
                <a href="tel:{{ config('hotel.hotline_tel') }}">{{ config('hotel.hotline') }}</a>
                or write to
                <a href="mailto:{{ config('hotel.email_legal') }}">{{ config('hotel.email_legal') }}</a>.
            </p>
        </footer>
    </article>
@endsection

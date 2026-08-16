@extends('legal.layout')

@section('eyebrow', 'Guest information')
@section('heading', 'Accessibility')
@section('updated', date('F Y'))

@section('body')
    <p class="ds-lead">
        We want this site and our houses to work for every guest. This page says
        what we have done, what we know is not yet good enough, and how to tell us
        when we have got it wrong.
    </p>

    <h2>At our properties</h2>
    <ul class="ds-legal__list">
        <li>Step-free entrances and lift access to guest floors at all three houses.</li>
        <li>Accessible rooms with wider doorways, grab rails and a roll-in shower &mdash; ask reception when booking, as these rooms are assigned by hand.</li>
        <li>Assistance animals are welcome throughout, at no charge.</li>
        <li>Reception is staffed 24 hours, so there is always someone to help with luggage, wayfinding or an early arrival.</li>
    </ul>
    <p>
        Because rooms are assigned at check-in rather than at the time of booking,
        the surest way to secure a specific accessible room is to call
        <a href="tel:{{ config('hotel.hotline_tel') }}">{{ config('hotel.hotline') }}</a>
        after booking and have it noted against your reservation.
    </p>

    <h2>On this site</h2>
    <p>
        We aim to meet WCAG 2.1 level AA. In practice that means:
    </p>
    <ul class="ds-legal__list">
        <li>Every page works from the keyboard alone, with a visible focus outline.</li>
        <li>Text sits at or above the required contrast ratio against its background.</li>
        <li>Forms use real labels, and errors are described in words rather than by colour alone.</li>
        <li>Animation, including the moving hero and the scroll reveals, is switched off when your device asks for reduced motion. Content is never hidden behind an animation that has not run.</li>
        <li>Images that carry meaning have alternative text; decorative ones are hidden from screen readers.</li>
    </ul>

    <h2>Where we fall short</h2>
    <p>
        We would rather list these than pretend they are not there:
    </p>
    <ul class="ds-legal__list">
        <li>The reporting screens used by our own staff have had less accessibility work than the guest-facing pages.</li>
        <li>Some date-picking controls come from a third-party library and are harder to operate with a screen reader than the rest of the site.</li>
        <li>PDF reports are generated for internal use and are not tagged for assistive technology.</li>
    </ul>

    <h2>Tell us</h2>
    <p>
        If something on this site or at one of our houses stopped you doing what
        you came to do, please tell us at
        <a href="mailto:{{ config('hotel.email') }}">{{ config('hotel.email') }}</a>
        or on <a href="tel:{{ config('hotel.hotline_tel') }}">{{ config('hotel.hotline') }}</a>.
        Describe what you were trying to do and we will reply within five working
        days, whether or not we have fixed it by then.
    </p>
@endsection

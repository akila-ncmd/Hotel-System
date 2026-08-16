@extends('legal.layout')

@section('eyebrow', 'Legal')
@section('heading', 'Privacy Policy')
@section('updated', date('F Y'))

@section('body')
    <p class="ds-lead">
        This policy explains what {{ config('hotel.legal_entity') }} collects when you
        use this site or stay with us, why we hold it, and what you can ask us to
        do with it.
    </p>

    <h2>What we collect</h2>
    <ul class="ds-legal__list">
        <li><strong>Account details</strong> &mdash; your name, email address, phone number and, where you provide it, your address. Passwords are stored only as a one-way hash and are never readable by us.</li>
        <li><strong>Reservation details</strong> &mdash; the branch, room type, dates, duration, number of occupants and any notes you add to a booking.</li>
        <li><strong>Card guarantee</strong> &mdash; the last four digits and expiry date of a card used to guarantee a booking. We do not retain the full card number.</li>
        <li><strong>Billing records</strong> &mdash; the itemised folio for your stay.</li>
        <li><strong>Technical records</strong> &mdash; server logs of requests made to the site, used to keep it running and secure.</li>
    </ul>

    <h2>Why we hold it</h2>
    <ul class="ds-legal__list">
        <li>To take, hold and fulfil your reservation, and to bill for your stay &mdash; this is performance of our contract with you.</li>
        <li>To contact you about a booking, including confirmations and notice that an unguaranteed booking has been released.</li>
        <li>To meet legal and tax obligations, and to keep the guest records a hotel is required to keep.</li>
        <li>To protect the site against abuse. Failed sign-in attempts are rate-limited and logged.</li>
    </ul>
    <p>
        We do not sell your data, and we do not use it for advertising or
        profiling.
    </p>

    <h2>Who sees it</h2>
    <p>
        Access is limited to what a role needs. Reception staff and managers see
        the reservations of their own branch only. Card guarantees are visible as
        masked digits only. Beyond our own staff we share data with our email
        delivery provider (to send you booking correspondence), and with public
        authorities where the law requires it.
    </p>

    <h2>How long we keep it</h2>
    <p>
        Reservation and billing records are kept for as long as is needed for
        accounting and tax purposes, and then deleted. An account that you ask us
        to close is removed, except for records we are required to retain.
    </p>

    <h2>Cookies</h2>
    <p>
        This site sets only the cookies it needs to work: a session cookie that
        keeps you signed in, and a CSRF token that protects forms against
        cross-site submission. There are no analytics, advertising or third-party
        tracking cookies. Blocking the essential cookies will stop you being able
        to sign in.
    </p>

    <h2>Your rights</h2>
    <p>
        You can ask us for a copy of the data we hold about you, ask us to correct
        it, or ask us to delete it where we are not required to keep it. You can
        edit most of your own details directly from your profile page. To make any
        other request, write to
        <a href="mailto:{{ config('hotel.email_legal') }}">{{ config('hotel.email_legal') }}</a>
        and we will respond within 30 days.
    </p>

    <h2>Security</h2>
    <p>
        Passwords are hashed, card numbers are discarded after validation, and
        access to guest records is scoped by role and by branch. No system is
        perfectly secure; if a breach affects your data we will tell you and the
        relevant authority without undue delay.
    </p>

    <h2>Children</h2>
    <p>
        This site is not intended for children, and accounts may only be created
        by people aged 18 or over. Children are of course welcome as guests when
        booked by an adult.
    </p>
@endsection

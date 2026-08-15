@extends('layouts.app')

@section('content')
<div class="container mt-5">
    <h2>Welcome, {{ Auth::user()->name }}</h2>
    <p>Email: {{ Auth::user()->email }}</p>
    <p>Nationality: {{ Auth::user()->nationality }}</p>
    <p>Contact Number: {{ Auth::user()->contact_number }}</p>
    <div class="mb-3">
        <a href="{{ route('profile.edit') }}" class="btn btn-primary">Edit Profile</a>
    </div>
</div>
@endsection
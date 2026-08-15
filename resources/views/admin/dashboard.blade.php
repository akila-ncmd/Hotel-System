@extends('layouts.app')

@section('content')
<div class="container py-5">
    <h1 class="display-5 fw-bold mb-4">Admin Dashboard</h1>
    
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Branch Selection -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <h3 class="h4 mb-3">Select Branch</h3>
            <form action="{{ route('admin.select-branch') }}" method="POST">
                @csrf
                <div class="row g-3 align-items-end">
                    <div class="col-md-6">
                        <label for="branch_id" class="form-label">Branch</label>
                        <select name="branch_id" id="branch_id" class="form-select" required>
                            <option value="">Select Branch</option>
                            @foreach ($branches as $branch)
                                <option value="{{ $branch->id }}" {{ session('admin_selected_branch') == $branch->id ? 'selected' : '' }}>{{ $branch->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <button type="submit" class="btn btn-primary">View Branch Data</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="row g-4">
        <!-- Staff Management -->
        <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <h3 class="h4 mb-3">Staff Management</h3>
                    <p class="text-muted">Register new clerks and managers to the system.</p>
                    <form action="{{ route('admin.staff.register') }}" method="GET">
                        @csrf
                        <button type="submit" class="btn btn-primary">Register Staff</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
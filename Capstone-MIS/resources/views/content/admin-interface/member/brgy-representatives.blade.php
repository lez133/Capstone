@extends('layouts.adminlayout')

@section('title', 'Barangay Representatives')

@section('content')
<div class="container mt-4">
    <div class="card shadow-sm">
        <div class="card-header bg-success text-white">
            <h5 class="mb-0">Barangay Representatives</h5>
        </div>
        <div class="card-body">
            @if($brgyReps->isNotEmpty())
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Contact</th>
                                <th>Profile Picture</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($brgyReps as $rep)
                            <tr>
                                <td>{{ $rep->id }}</td>
                                <td>{{ $rep->full_name }}</td>
                                <td>{{ $rep->email }}</td>
                                <td>{{ $rep->contact }}</td>
                                <td>
                                    @if($rep->profile_picture)
                                        <img src="{{ asset('storage/' . $rep->profile_picture) }}" alt="Profile Picture" class="img-thumbnail" style="width: 50px; height: 50px;">
                                    @else
                                        <span class="text-muted">No Picture</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <a href="{{ route('members.show', Crypt::encrypt($rep->id)) }}" class="btn btn-sm btn-outline-primary">View</a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="text-center text-muted">No Barangay Representatives found.</p>
            @endif
        </div>
    </div>
</div>
@endsection

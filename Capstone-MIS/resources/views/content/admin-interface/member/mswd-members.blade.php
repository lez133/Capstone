@extends('layouts.adminlayout')

@section('title', 'MSWD Members')

@section('content')
<div class="container mt-4">
    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">MSWD Members</h5>
        </div>
        <div class="card-body">
            @if($mswdMembers->isNotEmpty())
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
                            @foreach($mswdMembers as $member)
                            <tr>
                                <td>{{ $member->id }}</td>
                                <td>{{ $member->full_name }}</td>
                                <td>{{ $member->email }}</td>
                                <td>{{ $member->contact }}</td>
                                <td>
                                    @if($member->profile_picture)
                                        <img src="{{ asset('storage/' . $member->profile_picture) }}" alt="Profile Picture" class="img-thumbnail" style="width: 50px; height: 50px;">
                                    @else
                                        <span class="text-muted">No Picture</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <a href="{{ route('members.show', Crypt::encrypt($member->id)) }}" class="btn btn-sm btn-outline-primary">View</a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="text-center text-muted">No MSWD Members found.</p>
            @endif
        </div>
    </div>
</div>
@endsection

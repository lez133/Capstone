@extends('layouts.adminlayout')

@section('title', 'Program Types')

@section('content')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
<style>
    .card-action { transition: box-shadow 0.3s, transform 0.3s; }
    .card-action:hover { box-shadow: 0 8px 24px rgba(0,0,0,0.15); transform: scale(1.03); }
    .fade-in { animation: fadeInUp 0.6s; }
    @keyframes fadeInUp {
        from { opacity: 0; transform: translateY(30px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .table-responsive { overflow-x: auto; }
</style>
<div class="container py-4">
    <!-- Back Button -->
    <div class="mb-3">
        <a href="{{ route('programs.index') }}" class="btn btn-secondary">
            <i class="fa fa-arrow-left"></i> Back to Programs
        </a>
    </div>

    <div class="row g-3 mb-4">
        <!-- Add Program Type Card -->
        <div class="col-md-6 col-12">
            <div class="card card-action fade-in">
                <div class="card-body">
                    <h5><i class="fa-solid fa-plus text-primary"></i> Add Program Type</h5>
                    <form method="POST" action="{{ route('program-types.store') }}">
                        @csrf
                        <div class="input-group mb-2">
                            <input type="text" name="program_type_name" class="form-control" placeholder="Program Type Name" required>
                        </div>
                        <button type="submit" class="btn btn-primary w-100 mt-2"><i class="fa fa-save"></i> Save</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Program Type List -->
    <div class="card fade-in">
        <div class="card-header bg-primary text-white">
            <i class="fa fa-list"></i> Program Type List
        </div>
        <div class="card-body p-0 table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>#</th>
                        <th><i class="fa fa-tag"></i> Program Type</th>
                        <th><i class="fa fa-calendar"></i> Added</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($programTypes as $i => $programType)
                        <tr>
                            <td>{{ $i+1 }}</td>
                            <td>{{ $programType->program_type_name }}</td>
                            <td>{{ $programType->created_at->format('Y-m-d') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="text-center text-muted">No program types found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

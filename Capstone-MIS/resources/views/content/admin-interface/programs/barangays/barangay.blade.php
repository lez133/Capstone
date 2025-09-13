@extends('layouts.adminlayout')

@section('title', 'Barangays')

@section('content')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
<style>
    .card-action { transition: box-shadow 0.3s, transform 0.3s; }
    .card-action:hover { box-shadow: 0 8px 24px rgba(0,0,0,0.15); transform: scale(1.03);}
    .fade-in { animation: fadeInUp 0.6s; }
    @keyframes fadeInUp {
        from { opacity: 0; transform: translateY(30px);}
        to { opacity: 1; transform: translateY(0);}
    }
    .table-responsive { overflow-x: auto; }
</style>
<div class="container py-4">
    <div class="mb-3">
        <a href="{{ route('programs.index') }}" class="btn btn-secondary">
            <i class="fa fa-arrow-left"></i> Back to Programs
        </a>
    </div>

    <div class="row g-3 mb-4">
        <!-- Add Barangay Card -->
        <div class="col-md-4 col-12">
            <div class="card card-action fade-in">
                <div class="card-body">
                    <h5><i class="fa-solid fa-plus text-primary"></i> Add Barangay</h5>
                    <form method="POST" action="{{ route('barangays.store') }}">
                        @csrf
                        <div id="barangay-fields">
                            <div class="input-group mb-2">
                                <input type="text" name="names[]" class="form-control" placeholder="Barangay Name" required>
                                <button type="button" class="btn btn-outline-secondary add-field" title="Add more"><i class="fa fa-plus"></i></button>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary w-100 mt-2"><i class="fa fa-save"></i> Save</button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Import Barangays Card -->
        <div class="col-md-4 col-12">
            <div class="card card-action fade-in">
                <div class="card-body">
                    <h5><i class="fa-solid fa-file-import text-success"></i> Import Barangays</h5>
                    <form method="POST" action="{{ route('barangays.import') }}" enctype="multipart/form-data">
                        @csrf
                        <input type="file" name="file" class="form-control mb-2" accept=".csv" required>
                        <button type="submit" class="btn btn-success w-100"><i class="fa fa-upload"></i> Import CSV</button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Export Barangays Card -->
        <div class="col-md-4 col-12">
            <div class="card card-action fade-in">
                <div class="card-body">
                    <h5><i class="fa-solid fa-file-export text-warning"></i> Export Barangays</h5>
                    <a href="{{ route('barangays.export') }}" class="btn btn-warning w-100"><i class="fa fa-download"></i> Export CSV</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Barangay List -->
    <div class="card fade-in">
        <div class="card-header bg-primary text-white">
            <i class="fa fa-list"></i> Barangay List
        </div>
        <div class="card-body p-0 table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>#</th>
                        <th><i class="fa fa-map-marker-alt"></i> Name</th>
                        <th><i class="fa fa-calendar"></i> Added</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($barangays as $i => $barangay)
                        <tr>
                            <td>{{ $i+1 }}</td>
                            <td>{{ $barangay->barangay_name }}</td> <!-- Updated column name -->
                            <td>{{ $barangay->created_at->format('Y-m-d') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="text-center text-muted">No barangays found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.add-field').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const group = document.createElement('div');
            group.className = 'input-group mb-2';
            group.innerHTML = `
                <input type="text" name="names[]" class="form-control" placeholder="Barangay Name" required>
                <button type="button" class="btn btn-outline-danger remove-field" title="Remove"><i class="fa fa-minus"></i></button>
            `;
            document.getElementById('barangay-fields').appendChild(group);
        });
    });
    document.getElementById('barangay-fields').addEventListener('click', function(e) {
        if (e.target.closest('.remove-field')) {
            e.target.closest('.input-group').remove();
        }
    });
});
</script>
@endsection

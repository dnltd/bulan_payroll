@extends('layouts.app')

@section('content')
<div class="container py-4">

    {{-- Header --}}
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4">
        <div>
            <h2 class="fw-bold mb-1">Holiday List</h2>
            <p class="text-muted small mb-0">View and manage all company holidays</p>
        </div>

        <div class="mt-3 mt-md-0">
            <button class="btn btn-custom-blue px-4 py-2" data-bs-toggle="modal" data-bs-target="#createHolidayModal">
                <i class="bi bi-plus-circle me-1"></i> Add Holiday
            </button>
        </div>
    </div>

    {{-- Holiday Table --}}
    <div class="card shadow-sm">
        <div class="card-body table-responsive p-3">
            <table class="table table-hover bg-white holiday-table">
                <thead>
                    <tr>
                        <th>No.</th>
                        <th>Name</th>
                        <th>Date</th>
                        <th class="actions-col">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($holidays as $index => $holiday)
                        <tr id="holiday-row-{{ $holiday->id }}">
                            <td>{{ ($holidays->currentPage() - 1) * $holidays->perPage() + $index + 1 }}</td>
                            <td>{{ $holiday->name }}</td>
                            <td>{{ \Carbon\Carbon::parse($holiday->date)->format('F d, Y') }}</td>
                            <td class="actions-col">
    <button class="btn btn-outline-primary btn-sm action-btn"  data-bs-toggle="modal" data-bs-target="#editHolidayModal{{ $holiday->id }}">
        <i class="bi bi-pencil-square"></i> <span class="action-text">Edit</span>
    </button>

    <button class="btn btn-outline-danger btn-sm action-btn" onclick="confirmDeleteHoliday({{ $holiday->id }})">
        <i class="bi bi-trash"></i> <span class="action-text">Delete</span>
    </button>
</td>

                        </tr>

                        <!-- Create Holiday Modal -->
<div class="modal fade" id="createHolidayModal" tabindex="-1" aria-labelledby="createHolidayLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 700px;">
        <div class="modal-content shadow-lg rounded-4 border-0">
            <form action="{{ route('admin.holidays.store') }}" method="POST">
                @csrf
                <div class="modal-header" style="background: linear-gradient(90deg, #17007C, #3422b5); color: #fff;">
                    <h5 class="modal-title fw-bold"><i class="bi bi-plus-circle me-1"></i> Add Holiday</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Name</label>
                        <input type="text" name="name" class="form-control shadow-sm border-0" value="{{ old('name') }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Date</label>
                        <input type="date" name="date" class="form-control shadow-sm border-0" value="{{ old('date') }}" required min="{{ date('Y-m-d', strtotime('+1 day')) }}">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary" style="background: linear-gradient(90deg, #17007C, #3422b5); border: none;">Save</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </form>
        </div>
    </div>
</div>

                        {{-- Edit Modal --}}
                        <div class="modal fade" id="editHolidayModal{{ $holiday->id }}" tabindex="-1">
                            <div class="modal-dialog modal-dialog-centered" style="max-width:700px;">
                                <div class="modal-content shadow-lg rounded-4 border-0">
                                    <form action="{{ route('admin.holidays.update', $holiday->id) }}" method="POST">
                                        @csrf
                                        @method('PUT')
                                        <div class="modal-header bg-primary text-white rounded-top-4">
                                            <h5 class="modal-title fw-bold"><i class="bi bi-pencil-square me-1"></i>Edit Holiday</h5>
                                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">Name</label>
                                                <input type="text" name="name" class="form-control shadow-sm border-0" value="{{ $holiday->name }}" required>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">Date</label>
                                                <input type="date" name="date" class="form-control shadow-sm border-0" value="{{ $holiday->date }}" required>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="submit" class="btn btn-primary">Update</button>
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                    @empty
                        <tr><td colspan="4" class="text-center text-muted">No holidays found.</td></tr>
                    @endforelse
                </tbody>
            </table>

            {{-- Pagination --}}
            @if ($holidays instanceof \Illuminate\Pagination\LengthAwarePaginator)
                <div class="d-flex justify-content-between align-items-center p-3 flex-wrap">
                    <div class="small text-muted mb-2">
                        Showing {{ $holidays->firstItem() }} to {{ $holidays->lastItem() }} of {{ $holidays->total() }} results
                    </div>
                    <nav>
                        <ul class="pagination mb-0 pagination-lg gap-2">
                            <li class="page-item {{ $holidays->onFirstPage() ? 'disabled' : '' }}">
                                <a class="page-link rounded-pill shadow-sm px-4" href="{{ $holidays->previousPageUrl() ?? '#' }}">Previous</a>
                            </li>
                            <li class="page-item {{ !$holidays->hasMorePages() ? 'disabled' : '' }}">
                                <a class="page-link rounded-pill shadow-sm px-4" href="{{ $holidays->nextPageUrl() ?? '#' }}">Next</a>
                            </li>
                        </ul>
                    </nav>
                </div>
            @endif
        </div>
    </div>
</div>

<style>
/* === Holiday Table Styling (Match Employee Table) === */
.holiday-table {
    width: 100%;
    font-size: 0.85rem;
    border-collapse: collapse;
}

/* Table Header */
.holiday-table thead th {
    background-color: #17007C !important;
    color: #fff !important;
    font-weight: 600;
    text-align: left;
    padding: 10px;
    border: 1px solid #17007C !important;
    white-space: nowrap;
}

/* Table Body Cells */
.holiday-table tbody td {
    text-align: left;
    vertical-align: middle;
    padding: 8px 10px;
    border: 1px solid #dee2e6;
}

/* Alternate Row Striping */
.holiday-table tbody tr:nth-child(even) {
    background-color: #f9f9ff;
}

/* Hover Effect */
.holiday-table tbody tr:hover {
    background-color: #eef2ff;
}

/* === Actions Column === */
.actions-col { 
    width: 180px; 
    text-align: center; 
}

.actions-col .action-btn { 
    margin: 2px 0; 
    white-space: nowrap; 
    transition: all 0.2s ease-in-out;
}

.actions-col .action-btn .action-text { 
    display: none; 
    margin-left: 4px; 
}

.actions-col .action-btn:hover .action-text { 
    display: inline; 
}

/* Add Holiday Button Styled Like Employee Button */
.btn-custom-blue {
    background-color: #17007C;
    color: #fff;
    border: none;
    border-radius: 0.5rem;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    transition: background-color 0.3s, transform 0.2s;
}
.btn-custom-blue:hover,
.btn-custom-blue:focus,
.btn-custom-blue:active {
    background-color: #17007C;
    color: #fff;
    box-shadow: 0 2px 4px rgba(0,0,0,0.15);
    transform: translateY(-1px);
}

/* Pagination */
.page-link {
    border-radius: 20px;
    padding: 0.25rem 0.75rem;
    font-size: 0.875rem;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    transition: all 0.2s ease-in-out;
}
.page-link:hover {
    background-color: #17007C;
    color: white;
}
</style>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
function confirmDeleteHoliday(id) {
    Swal.fire({
        title: 'Are you sure?',
        text: "This holiday will be permanently deleted!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#17007C',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, delete it!',
        cancelButtonText: 'Cancel',
        reverseButtons: true
    }).then((result) => {
        if(result.isConfirmed){
            const token = "{{ csrf_token() }}";

            fetch(`/admin/holidays/${id}`, {
                method: 'DELETE',
                headers: { 
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': token
                }
            })
            .then(response => {
                if(response.ok){
                    // Remove row from table
                    document.getElementById(`holiday-row-${id}`)?.remove();

                    Swal.fire({
                        icon: 'success',
                        title: 'Deleted!',
                        text: 'Holiday has been deleted.',
                        timer: 1500,
                        showConfirmButton: false
                    });
                } else {
                    Swal.fire('Error', 'Something went wrong!', 'error');
                }
            })
            .catch(() => Swal.fire('Error', 'Something went wrong!', 'error'));
        }
    });
}
</script>
@endpush


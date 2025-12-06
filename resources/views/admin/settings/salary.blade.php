{{-- Salary Rate Management --}}
<div class="mb-3">
    <h5 class="fw-bold">Salary Rate Management</h5>
    <p class="text-muted small mb-0">Manage salary rates for all positions. Update or delete existing rates below.</p>
</div>

{{-- Add Salary Rate Form --}}
<form method="POST" action="{{ route('admin.settings.salary.store') }}">
    @csrf
    <div class="row g-2 mb-3">
        <div class="col-md-4">
            <select name="position" class="form-select shadow-sm" required>
                <option value="">-- Select Position --</option>
                <option value="General Manager">General Manager</option>
                <option value="Secretary">Secretary</option>
                <option value="Treasurer">Treasurer</option>
                <option value="Dispatcher">Dispatcher</option>
                <option value="Inspector">Inspector</option>
                <option value="Driver">Driver</option>
                <option value="Conductor">Conductor</option>
            </select>
        </div>
        <div class="col-md-3">
            <input name="daily_rate" type="number" step="0.01" class="form-control shadow-sm" placeholder="Daily Rate" required>
        </div>
        <div class="col-md-3">
            <input name="overtime" type="number" step="0.01" class="form-control shadow-sm" placeholder="Overtime Rate" required>
        </div>
        <div class="col-md-2">
    <button class="btn btn-theme w-100 shadow-sm">Add</button>
</div>

    </div>
</form>

{{-- Salary Rates Table --}}
<div class="table-responsive shadow-sm rounded">
    <table class="table table-bordered table-hover mb-0 align-middle salary-table">
        <thead>
            <tr>
                <th>Position</th>
                <th>Daily Rate</th>
                <th>Overtime</th>
                <th class="text-center">Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($salaryRates as $rate)
            <tr>
                <td>{{ $rate->position }}</td>
                <td>₱{{ number_format($rate->daily_rate, 2) }}</td>
                <td>₱{{ number_format($rate->overtime, 2) }}</td>
                <td class="text-center actions-col">
                    {{-- Delete --}}
                    <button class="btn btn-outline-danger btn-sm action-btn" onclick="confirmDelete({{ $rate->id }})">
                        <i class="bi bi-trash"></i>
                        <span class="action-text">Delete</span>
                    </button>

                    {{-- Edit --}}
                    <button class="btn btn-warning btn-sm action-btn" data-bs-toggle="modal" data-bs-target="#editModal{{ $rate->id }}">
                        <i class="bi bi-pencil"></i>
                        <span class="action-text">Edit</span>
                    </button>

                    {{-- Edit Modal --}}
                    <div class="modal fade" id="editModal{{ $rate->id }}" tabindex="-1" aria-labelledby="editModalLabel{{ $rate->id }}" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered modal-md">
                            <form method="POST" action="{{ route('admin.settings.salary.update', $rate->id) }}">
                                @csrf
                                <div class="modal-content shadow-sm rounded">
                                    <div class="modal-header bg-theme text-white">
                                        <h5 class="modal-title" id="editModalLabel{{ $rate->id }}">Edit Salary Rate</h5>
                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="mb-2">
                                            <label class="form-label">Position</label>
                                            <input type="text" name="position" class="form-control shadow-sm" value="{{ $rate->position }}" required>
                                        </div>
                                        <div class="mb-2">
                                            <label class="form-label">Daily Rate</label>
                                            <input type="number" step="0.01" name="daily_rate" class="form-control shadow-sm" value="{{ $rate->daily_rate }}" required>
                                        </div>
                                        <div class="mb-2">
                                            <label class="form-label">Overtime</label>
                                            <input type="number" step="0.01" name="overtime" class="form-control shadow-sm" value="{{ $rate->overtime }}" required>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="submit" class="btn btn-theme shadow-sm w-100">Update</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

{{-- Styles --}}
<style>
.salary-table thead th {
    background-color: #17007C;
    color: #fff;
    font-weight: 600;
    text-align: left;
    padding: 10px;
    border: 1px solid #17007C;
}

.salary-table tbody td {
    vertical-align: middle;
    text-align: left;
    padding: 8px 10px;
    border: 1px solid #dee2e6;
}

.salary-table tbody tr:nth-child(even) { background-color: #f9f9ff; }
.salary-table tbody tr:hover { background-color: #eef2ff; transition: background-color 0.2s ease-in-out; }

.actions-col { width: 180px; text-align: center; }
.actions-col .action-btn { margin: 2px 0; white-space: nowrap; }
.actions-col .action-btn .action-text { display: none; margin-left: 4px; }
.actions-col .action-btn:hover .action-text { display: inline; }

.btn-outline-danger {
    border: 1px solid #dc3545;
    color: #dc3545;
    font-size: 0.875rem;
}
.btn-outline-danger:hover {
    background-color: #dc3545;
    color: #fff;
}

.btn-warning { font-size: 0.875rem; }

.bg-theme { background-color: #17007C !important; }
.btn-close-white { filter: invert(1) brightness(1.2); }

.table-responsive { overflow-x: auto; }
</style>

{{-- SweetAlert2 --}}
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
function confirmDelete(rateId) {
    Swal.fire({
        title: 'Are you sure?',
        text: "This salary rate will be permanently deleted!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, delete it!',
        cancelButtonText: 'Cancel',
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            const token = "{{ csrf_token() }}";
            fetch(`/admin/settings/salary/${rateId}`, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': token
                }
            })
            .then(response => {
                if (response.ok) {
                    // Remove row from table
                    const row = document.querySelector(`button[onclick='confirmDelete(${rateId})']`)?.closest('tr');
                    if(row) row.remove();

                    Swal.fire({
                        icon: 'success',
                        title: 'Deleted!',
                        text: 'Salary rate has been deleted.',
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

<div class="mb-3">
        <h5 class="fw-bold">Accounts List</h5>
        <p class="text-muted small mb-0">Manage dispatcher accounts. Admin accounts are protected and cannot be deleted.</p>
    </div>
<div class="table-responsive shadow-sm rounded">
    <table class="table table-bordered table-hover mb-0 align-middle accounts-table">
        <thead>
            <tr>
                <th>Employee</th>
                <th>Email</th>
                <th>Role</th>
                <th class="text-center">Action</th>
            </tr>
        </thead>
        <tbody>
            @forelse($users as $user)
                <tr>
                    <td>{{ $user->employee->full_name ?? '-' }}</td>
                    <td>{{ $user->email }}</td>
                    <td>{{ ucfirst($user->role) }}</td>
                    <td class="text-center actions-col">
                        @if($user->role !== 'admin')
                            <button class="btn btn-outline-danger btn-sm action-btn"
                                    onclick="confirmDeleteUser({{ $user->id }})" 
                                    title="Delete Account">
                                <i class="bi bi-trash"></i>
                                <span class="action-text">Delete</span>
                            </button>
                        @else
                            <span class="badge bg-secondary">Protected</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="text-center text-muted">No accounts found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<style>
/* Table header style */
.accounts-table thead th {
    background-color: #17007C;
    color: #fff;
    font-weight: 600;
    text-align: left;
    padding: 10px;
    border: 1px solid #17007C;
}

/* Table body style */
.accounts-table tbody td {
    vertical-align: middle;
    text-align: left;
    padding: 8px 10px;
    border: 1px solid #dee2e6;
}
.accounts-table tbody tr:nth-child(even) { background-color: #f9f9ff; }
.accounts-table tbody tr:hover { background-color: #eef2ff; transition: background-color 0.2s ease-in-out; }

.actions-col { width: 180px; text-align: center; }
.actions-col .action-btn { margin: 2px 0; white-space: nowrap; }
.actions-col .action-btn .action-text { display: none; margin-left: 4px; }
.actions-col .action-btn:hover .action-text { display: inline; }

/* Delete button style */
.btn-outline-danger {
    border: 1px solid #dc3545;
    color: #dc3545;
    font-size: 0.875rem;
}
.btn-outline-danger:hover {
    background-color: #dc3545;
    color: #fff;
}

/* Badge style */
.badge {
    font-size: 0.8rem;
    padding: 0.35em 0.6em;
}

/* Responsive table */
.table-responsive {
    overflow-x: auto;
}
</style>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
function confirmDeleteUser(userId) {
    Swal.fire({
        title: 'Are you sure?',
        text: "This account will be permanently deleted!",
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
            fetch(`/admin/settings/accounts/${userId}`, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': token
                }
            })
            .then(response => {
                if (response.ok) {
                    // Remove row from table
                    const row = document.querySelector(`button[action-id='${userId}']`)?.closest('tr');
                    if(row) row.remove();

                    Swal.fire({
                        icon: 'success',
                        title: 'Deleted!',
                        text: 'Account has been deleted.',
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

<h5>Create User Account</h5>

<form method="POST" action="{{ route('admin.settings.createUser') }}">
    @csrf
    <div class="row g-2 mb-3">
        <div class="col-md-6">
            <select name="employee_id" class="form-select" required>
                <option value="">-- Select Employee --</option>
                @foreach($availableEmployees as $employee)
                    <option value="{{ $employee->id }}">{{ $employee->full_name }} ({{ $employee->position }})</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <input name="email" type="email" class="form-control" placeholder="Email" required>
        </div>
        <div class="col-md-3">
            <select name="role" class="form-select" required>
                <option value="dispatcher">Dispatcher</option>
                <option value="admin">Admin</option>
            </select>
        </div>
    </div>

    <button type="submit" class="btn btn-success">
        <i class="bi bi-plus-circle"></i> Create Account
    </button>
</form>

@if(session('success'))
    <div class="alert alert-success mt-3">{{ session('success') }}</div>
@endif

{{-- Auto-shown modal with account credentials --}}
@if(session('new_account'))
<div class="modal fade show" id="credentialsModal" style="display:block;" tabindex="-1" aria-modal="true" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content shadow">
            <div class="modal-header">
                <h5 class="modal-title">Default Account Credentials</h5>
                <a href="{{ url()->current() }}" class="btn-close"></a>
            </div>
            <div class="modal-body">
                <p><strong>Name:</strong> {{ session('new_account.name') }}</p>
                <p><strong>Email:</strong> {{ session('new_account.email') }}</p>
                <p><strong>Role:</strong> {{ session('new_account.role') }}</p>
                <p><strong>Default Password:</strong> {{ session('new_account.password') }}</p>
                <p class="text-danger small mb-0">Make sure to copy and give these credentials to the user.</p>
            </div>
        </div>
    </div>
</div>

<script>
    setTimeout(() => {
        window.location.href = "{{ url()->current() }}";
    }, 10000); // auto close after 10 seconds
</script>
@endif

<hr class="my-4">

<h5 class="mt-4">Accounts List</h5>
<table class="table table-bordered">
    <thead class="table-light">
        <tr>
            <th>Employee</th>
            <th>Email</th>
            <th>Role</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        @foreach($dispatchers as $user)
            <tr>
                <td>{{ $user->employee->full_name ?? '-' }}</td>
                <td>{{ $user->email }}</td>
                <td>{{ ucfirst($user->role) }}</td>
                <td>
                    <form method="POST" action="{{ route('admin.settings.account.delete', $user->id) }}" onsubmit="return confirm('Are you sure you want to delete this account?')">
                        @csrf
                        @method('DELETE')
                        <button class="btn btn-danger btn-sm"><i class="bi bi-trash"></i></button>
                    </form>
                </td>
            </tr>
        @endforeach
    </tbody>
</table>

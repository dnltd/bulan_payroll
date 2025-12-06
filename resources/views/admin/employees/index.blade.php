@extends('layouts.app')

@section('content')
<div class="container py-4">

    {{-- Header --}}
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4">
        <div>
            <h2 class="fw-bold mb-1">Employee List</h2>
            <p class="text-muted small mb-0">Manage all employees, add new staff, and track details efficiently.</p>
        </div>

        <div class="mt-3 mt-md-0">
            <button class="btn btn-custom-blue px-4 py-2" data-bs-toggle="modal" data-bs-target="#addEmployeeModal">
                <i class="bi bi-person-plus-fill me-1"></i> Add Employee
            </button>
        </div>
    </div>

    {{-- Filters --}}
    <form method="GET" action="{{ route('admin.employees.index') }}" class="card shadow-sm border-0 mb-4">
        <div class="card-body row g-3 align-items-end">
            <div class="col-md-4">
                <label for="position" class="form-label fw-semibold small">Position</label>
                <select name="position" id="position" class="form-select form-select-sm shadow-sm border-0">
                    <option value="">All Positions</option>
                    @foreach(\App\Models\Employee::select('position')->distinct()->pluck('position') as $position)
                        <option value="{{ $position }}" {{ request('position') == $position ? 'selected' : '' }}>
                            {{ $position }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4 d-flex gap-2">
                <button type="submit" class="btn btn-sm text-white shadow-sm px-3" style="background-color:#17007C; border:none;">
                    <i class="bi bi-funnel"></i> Apply
                </button>
                <a href="{{ route('admin.employees.index') }}" class="btn btn-sm btn-outline-secondary shadow-sm px-3">
                    <i class="bi bi-x-circle"></i> Reset
                </a>
            </div>
        </div>
    </form>

    <div class="mb-3">
        <a href="{{ route('admin.employees.export.pdf') }}" class="btn btn-outline-danger btn-sm"><i class="bi bi-file-earmark-pdf"></i> PDF</a>
        <a href="{{ route('admin.employees.print', request()->query()) }}" target="_blank" class="btn btn-outline-secondary btn-sm"><i class="bi bi-printer"></i> Print</a>
    </div>

    <div class="card shadow-sm">
        <div class="card-body table-responsive">
            <table class="table table-hover bg-white employee-table">
                <thead>
                    <tr>
                        <th>No.</th>
                        <th>Name</th>
                        <th>Position</th>
                        <th>Salary Rate</th>
                        <th>Contact</th>
                        <th>Email</th>
                        <th class="actions-col">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($employees as $index => $employee)
                        <tr id="employee-row-{{ $employee->id }}">
            
                            <td>{{ ($employees->currentPage()-1) * $employees->perPage() + $index + 1 }}</td>
                            <td>{{ $employee->first_name }} {{ $employee->middle_name ? $employee->middle_name.' ' : '' }}{{ $employee->last_name }}</td>
                            <td>{{ $employee->position }}</td>
                            <td>₱{{ number_format($employee->salaryRate->daily_rate ?? 0,2) }}</td>
                            <td>{{ $employee->contact_number }}</td>
                            <td>{{ $employee->email }}</td>
                            <td class="actions-col">
    <!-- Replace the current edit link -->
<button class="btn btn-outline-primary btn-sm action-btn" 
        data-bs-toggle="modal" 
        data-bs-target="#editEmployeeModal{{ $employee->id }}" 
        title="Edit">
    <i class="bi bi-pencil-square"></i>
    <span class="action-text">Edit</span>
</button>


    <!-- Delete -->
    <button class="btn btn-outline-danger btn-sm action-btn" 
            onclick="confirmDelete({{ $employee->id }})" title="Delete">
        <i class="bi bi-trash"></i>
        <span class="action-text">Delete</span>
    </button>

    <!-- Make Admin -->
    @if(!$employee->user || $employee->user->role !== 'admin')
        <button class="btn btn-outline-success btn-sm action-btn" 
                onclick="openAdminPasswordModal({{ $employee->id }})" title="Make Admin">
            <i class="bi bi-shield-lock-fill"></i>
            <span class="action-text">Make Admin</span>
        </button>
    @endif
</td>


                        </tr>

                        <!-- Edit Employee Modal -->
<div class="modal fade" id="editEmployeeModal{{ $employee->id }}" tabindex="-1" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 800px;">
        <div class="modal-content shadow-lg rounded-4 border-0">
            
            <div class="modal-header text-white rounded-top-4" style="background-color: #17007C;">
                <h5 class="modal-title"><i class="bi bi-pencil-square me-2"></i>Edit Employee</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            
            <form action="{{ route('admin.employees.update', $employee->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                
                <div class="modal-body">

                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">First Name <span class="text-danger">*</span></label>
                            <input type="text" name="first_name" class="form-control rounded-3 @error('first_name') is-invalid @enderror" value="{{ old('first_name', $employee->first_name) }}" required>
                            @error('first_name')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Middle Name (optional)</label>
                            <input type="text" name="middle_name" class="form-control rounded-3 @error('middle_name') is-invalid @enderror" value="{{ old('middle_name', $employee->middle_name) }}">
                            @error('middle_name')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Last Name <span class="text-danger">*</span></label>
                            <input type="text" name="last_name" class="form-control rounded-3 @error('last_name') is-invalid @enderror" value="{{ old('last_name', $employee->last_name) }}" required>
                            @error('last_name')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Email <span class="text-danger">*</span></label>
                        <input type="email" name="email" class="form-control rounded-3 @error('email') is-invalid @enderror" value="{{ old('email', $employee->email) }}" required>
                        @error('email')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Address <span class="text-danger">*</span></label>
                        <textarea name="address" class="form-control rounded-3 @error('address') is-invalid @enderror" rows="2" required>{{ old('address', $employee->address) }}</textarea>
                        @error('address')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Contact Number <span class="text-danger">*</span></label>
                        <input type="tel" name="contact_number" id="contact_numberEdit{{ $employee->id }}" class="form-control rounded-3 @error('contact_number') is-invalid @enderror" value="{{ old('contact_number', $employee->contact_number) }}" placeholder="09xxxxxxxxx or +639xxxxxxxxx" required>
                        @error('contact_number')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Position & Salary Rate <span class="text-danger">*</span></label>
                        <select name="salary_rates_id" class="form-select rounded-3 @error('salary_rates_id') is-invalid @enderror" required>
                            <option value="">-- Select --</option>
                            @foreach($salaryRates as $rate)
                                <option value="{{ $rate->id }}" {{ old('salary_rates_id', $employee->salary_rates_id) == $rate->id ? 'selected':'' }}>
                                    {{ $rate->position }} - ₱{{ number_format($rate->daily_rate,2) }}
                                </option>
                            @endforeach
                        </select>
                        @error('salary_rates_id')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <hr>
                    <h6>Update Face (optional)</h6>
                    <button type="button" class="btn btn-secondary mb-2" onclick="toggleEditCamera({{ $employee->id }})">
                        <i class="bi bi-camera"></i> Start Camera
                    </button>
                    <div class="position-relative mx-auto" style="width:220px; text-align:center;">
                        <video id="videoEdit{{ $employee->id }}" class="circular" autoplay muted playsinline style="display:none;"></video>
                    </div>
                    <canvas id="canvasEdit{{ $employee->id }}" style="display:none;"></canvas>
                    <input type="hidden" name="captured_face" id="captured_faceEdit{{ $employee->id }}">
                    <img id="previewEdit{{ $employee->id }}" class="img-thumbnail mt-2" style="display:none; max-height:300px;">

                </div>

                <div class="modal-footer">
                    <button type="submit" class="btn btn-custom-blue">Update</button>
                    <button type="button" class="btn btn-outline-custom-blue rounded-3" data-bs-dismiss="modal">Close</button>
                </div>
            </form>
        </div>
    </div>
</div>


                    @empty
                        <tr><td colspan="6" class="text-center text-muted">No employees found.</td></tr>
                    @endforelse
                </tbody>
            </table>


            {{-- Pagination --}}
@if ($employees instanceof \Illuminate\Pagination\LengthAwarePaginator)
    <div class="d-flex justify-content-between align-items-center p-3 flex-wrap">
        <div class="small text-muted mb-2">
            Showing {{ $employees->firstItem() }} to {{ $employees->lastItem() }} of {{ $employees->total() }} results
        </div>
        <nav>
            <ul class="pagination mb-0 pagination-lg gap-2">
                {{-- Previous Button --}}
                <li class="page-item {{ $employees->onFirstPage() ? 'disabled' : '' }}">
                    <a class="page-link rounded-pill shadow-sm px-4" href="{{ $employees->previousPageUrl() ?? '#' }}">
                        Previous
                    </a>
                </li>

                {{-- Next Button --}}
                <li class="page-item {{ !$employees->hasMorePages() ? 'disabled' : '' }}">
                    <a class="page-link rounded-pill shadow-sm px-4" href="{{ $employees->nextPageUrl() ?? '#' }}">
                        Next
                    </a>
                </li>
            </ul>
        </nav>
    </div>
@endif


        </div>
    </div>
</div>
<!-- Add Employee Modal -->
<div class="modal fade" id="addEmployeeModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 800px;">
        <div class="modal-content shadow-lg rounded-4 border-0">
            <form id="addEmployeeForm" method="POST" action="{{ route('admin.employees.store') }}" enctype="multipart/form-data" novalidate>
                @csrf
                <div class="modal-header text-white rounded-top-4" style="background-color: #17007C;">
                    <h5 class="modal-title"><i class="bi bi-person-plus-fill me-2"></i>Add New Employee</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">

                    {{-- First, Middle, Last Name --}}
                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">First Name <span class="text-danger">*</span></label>
                            <input type="text" name="first_name"
                                class="form-control rounded-3 @error('first_name') is-invalid @enderror"
                                value="{{ old('first_name') }}" required>
                            @error('first_name')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Middle Name (optional)</label>
                            <input type="text" name="middle_name"
                                class="form-control rounded-3"
                                value="{{ old('middle_name') }}">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Last Name <span class="text-danger">*</span></label>
                            <input type="text" name="last_name"
                                class="form-control rounded-3 @error('last_name') is-invalid @enderror"
                                value="{{ old('last_name') }}" required>
                            @error('last_name')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    {{-- Email --}}
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Email <span class="text-danger">*</span></label>
                        <input type="email" name="email"
                            class="form-control rounded-3 @error('email') is-invalid @enderror"
                            value="{{ old('email') }}" required>
                        @error('email')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Address --}}
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Address <span class="text-danger">*</span></label>
                        <textarea name="address" class="form-control rounded-3 @error('address') is-invalid @enderror"
                            rows="2" required>{{ old('address') }}</textarea>
                        @error('address')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Contact Number --}}
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Contact Number <span class="text-danger">*</span></label>
                        <input type="tel" name="contact_number" id="contact_number"
                            class="form-control rounded-3 @error('contact_number') is-invalid @enderror"
                            value="{{ old('contact_number') }}"
                            placeholder="09xxxxxxxxx or +639xxxxxxxxx" required>
                        @error('contact_number')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Salary Rate --}}
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Position & Salary Rate <span class="text-danger">*</span></label>
                        <select name="salary_rates_id"
                            class="form-select rounded-3 @error('salary_rates_id') is-invalid @enderror" required>
                            <option value="">-- Select --</option>
                            @foreach($salaryRates as $rate)
                                <option value="{{ $rate->id }}" {{ old('salary_rates_id') == $rate->id ? 'selected' : '' }}>
                                    {{ $rate->position }} - ₱{{ number_format($rate->daily_rate, 2) }}
                                </option>
                            @endforeach
                        </select>
                        @error('salary_rates_id')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <hr>
                    <h6>Capture Face <span class="text-danger">*</span></h6>
                    <button type="button" class="btn btn-secondary mb-2" onclick="toggleCamera()">
                        <i class="bi bi-camera"></i> Start Camera
                    </button>
                    <div class="position-relative mx-auto" style="width:220px; text-align:center;">
                        <video id="video" class="circular" autoplay muted playsinline style="display:none;"></video>
                    </div>
                    <canvas id="canvas" style="display:none;"></canvas>
                    <input type="hidden" name="captured_face" id="captured_face">
                    <img id="preview" class="img-thumbnail mt-2" style="display:none; max-height:300px;">
                </div>

                <div class="modal-footer">
    <button type="button" id="submitEmployeeBtn" class="btn btn-custom-blue" onclick="submitEmployeeForm()">
        <i class="bi bi-plus-circle me-1"></i> Add & Save
    </button>
    <button type="button" class="btn btn-outline-custom-blue rounded-3" data-bs-dismiss="modal">Close</button>
</div>
            </form>
        </div>
    </div>
</div>



<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteConfirmModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content shadow rounded-4 border-0">
      <div class="modal-header bg-danger text-white">
        <h5 class="modal-title"><i class="bi bi-exclamation-triangle-fill me-2"></i> Confirm Delete</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        Are you sure you want to delete this employee? This action cannot be undone.
      </div>
      <div class="modal-footer">
        <form id="deleteForm" method="POST">@csrf @method('DELETE')
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-danger">Yes, Delete</button>
        </form>
      </div>
    </div>
  </div>
</div>

<!-- Notification Modal -->
<div class="modal fade" id="notificationModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content shadow rounded-4 border-0">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title"><i class="bi bi-info-circle-fill me-2"></i> Notification</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body" id="notificationBody"></div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">OK</button>
      </div>
    </div>
  </div>
</div>

<!-- Admin Password Modal -->
<div class="modal fade" id="adminPasswordModal" tabindex="-1" aria-hidden="true" 
     data-bs-backdrop="static" data-bs-keyboard="false">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content shadow-lg rounded-4 border-0">
      <div class="modal-header" style="background:#17007C;color:white;">
        <h5 class="modal-title"><i class="bi bi-shield-lock-fill me-2"></i> Confirm with Admin Password</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <form id="adminPasswordForm">
        <div class="modal-body">
          <p class="text-muted">For security, enter your admin password to continue.</p>
          <input type="hidden" id="targetEmployeeId">

          <!-- Admin Password -->
          <div id="passwordInputWrapper" class="form-floating mb-3">
            <input type="password" class="form-control rounded-3" id="adminPassword" 
                   name="admin_password" placeholder="Admin Password" required>
            <label for="adminPassword"><i class="bi bi-lock-fill me-2"></i> Admin Password</label>
          </div>

          <!-- Confirm Password -->
          <div id="confirmPasswordWrapper" class="form-floating mb-3">
            <input type="password" class="form-control rounded-3" id="confirmAdminPassword" 
                   name="confirm_admin_password" placeholder="Confirm Password" required>
            <label for="confirmAdminPassword"><i class="bi bi-lock-fill me-2"></i> Confirm Password</label>
          </div>

          <!-- Error Messages -->
          <div id="passwordError" class="text-danger small d-none mb-2">
            <i class="bi bi-exclamation-triangle-fill me-1"></i> Invalid password, please try again.
          </div>
          <div id="confirmPasswordError" class="text-danger small d-none mb-2">
            <i class="bi bi-exclamation-triangle-fill me-1"></i> Passwords do not match.
          </div>

          <!-- Success Box -->
          <div id="credentialsBox" class="alert alert-success d-none mt-3 rounded-3">
            <h6 class="mb-2"><i class="bi bi-check-circle-fill me-1"></i> New Admin Created</h6>
            <div><strong>Email:</strong> <span id="credEmail"></span></div>
            <div><strong>Password:</strong> <span id="credPassword"></span></div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-light" data-bs-dismiss="modal" onclick="location.reload()">Close</button>
          <button type="submit" id="confirmBtn" class="btn btn-primary">Confirm</button>
        </div>
      </form>
    </div>
  </div>
</div>


<!-- Dispatcher Credentials Modal -->
<div class="modal fade" id="dispatcherCredentialsModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content shadow rounded-4 border-0">
      <div class="modal-header bg-info text-white">
        <h5 class="modal-title"><i class="bi bi-person-badge-fill me-2"></i> Dispatcher Credentials</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <p><strong>Email:</strong> <span id="dispEmail"></span></p>
        <p><strong>Password:</strong> <span id="dispPassword"></span></p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" onclick="location.reload()">Close</button>
      </div>
    </div>
  </div>
</div>

<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">

<style>
    
.btn-custom-blue {
    background-color: #17007C; /* deep blue */
    color: #fff;
    border: none;
    border-radius: 0.5rem; /* rounded corners like employee button */
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1); /* subtle shadow */
    transition: background-color 0.3s, transform 0.2s;
}

.btn-custom-blue:hover,
.btn-custom-blue:focus,
.btn-custom-blue:active {
    background-color: #17007C; /* same color, no change on hover */
    color: #fff;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.15);
    transform: translateY(-1px); /* subtle lift effect */
}


/* Outline button with brand color */
.btn-outline-custom-blue {
    color: #17007C;
    border: 1px solid #17007C;
    border-radius: 0.5rem;
    padding: 0.5rem 1.2rem;
    transition: all 0.2s ease-in-out;
}
.btn-outline-custom-blue:hover,
.btn-outline-custom-blue:focus {
    background-color: #cd1b00ff;
    color: #fff;
    border-color: #cd1b00ff;
}
/* === Employee Table Styling (Payroll-like) === */
.employee-table {
    width: 100%;
    font-size: 0.85rem;
    border-collapse: collapse;
}

/* Table Header */
.employee-table thead th {
    background-color: #17007C !important;
    color: #fff !important;
    font-weight: 600;
    text-align: left;
    padding: 10px;
    border: 1px solid #17007C !important;
    white-space: nowrap;
}

/* Table Body Cells */
.employee-table tbody td {
    text-align: left;
    vertical-align: middle;
    padding: 8px 10px;
    border: 1px solid #dee2e6;
}

/* Alternate Row Striping */
.employee-table tbody tr:nth-child(even) {
    background-color: #f9f9ff; /* soft blue like payroll */
}

/* Hover Effect */
.employee-table tbody tr:hover {
    background-color: #eef2ff; /* same hover as payroll */
}

/* === Actions Column === */
.actions-col { 
    width: 220px; 
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

/* Table hover remains same */
.employee-table tbody tr:hover {
    background-color: #eef2ff;
}

/* Circular capture preview */
#preview {
    border-radius: 50% !important;
    width: 220px;
    height: 220px;
    object-fit: cover;
    display: none;
    border: 3px solid #333;
    margin-top: 10px;
}

/* Circular live video feed */
#video.circular {
    border-radius: 50% !important;
    width: 220px;
    height: 220px;
    object-fit: cover;
    border: 3px solid #666;
}
/* Circular capture preview */
#preview, [id^="previewEdit"] {
    border-radius: 50% !important;
    width: 220px;
    height: 220px;
    object-fit: cover;
    display: none;
    border: 3px solid #333;
    margin-top: 10px;
}

/* Circular live video feed */
#video.circular, [id^="videoEdit"] {
    border-radius: 50% !important;
    width: 220px;
    height: 220px;
    object-fit: cover;
    border: 3px solid #666;
}

/* === Pagination (Payroll style) === */
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
.invalid-feedback {
    font-size: 0.85rem;
    color: #dc3545;
}
.text-danger {
    font-weight: 600;
}
label .text-danger {
    transition: visibility 0.2s ease, opacity 0.2s ease;
    opacity: 1;
}
label .text-danger[style*="hidden"] {
    opacity: 0;
}


</style>
@endsection
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@push('scripts')

<script>
document.addEventListener('DOMContentLoaded', function () {
    const addForm = document.getElementById('addEmployeeForm');
    if (!addForm) return;

    const contactRegex = /^(09\d{9}|\+639\d{9})$/;
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

    // === Helper: Create or get error div ===
    function ensureErrorDiv(input, className) {
        let err = input.closest('.mb-3')?.querySelector(`.${className}`);
        if (!err) {
            err = document.createElement('div');
            err.classList.add('text-danger', 'small', 'mt-1', className);
            input.closest('.mb-3')?.appendChild(err);
        }
        return err;
    }

    // === Validation helpers ===
    function validateRequired(input) {
        const err = ensureErrorDiv(input, 'required-error');
        if (input.value.trim() === '') {
            input.classList.add('is-invalid');
            err.textContent = 'This field is required.';
            return false;
        } else {
            input.classList.remove('is-invalid');
            err.textContent = '';
            return true;
        }
    }

    function validateContact(input) {
        const err = ensureErrorDiv(input, 'contact-error');
        if (!contactRegex.test(input.value.trim())) {
            input.classList.add('is-invalid');
            err.textContent = 'Please enter a valid PH mobile number (09xxxxxxxxx or +639xxxxxxxxx).';
            return false;
        } else {
            input.classList.remove('is-invalid');
            err.textContent = '';
            return true;
        }
    }

    function validateEmail(input) {
        const err = ensureErrorDiv(input, 'email-error');
        if (!emailRegex.test(input.value.trim())) {
            input.classList.add('is-invalid');
            err.textContent = 'Please enter a valid email address.';
            return false;
        } else {
            input.classList.remove('is-invalid');
            err.textContent = '';
            return true;
        }
    }

    // === Contact number auto-format ===
    function setupContactAutoFormat(input) {
        input.addEventListener('input', () => {
            let value = input.value.replace(/[^\d+]/g, '');
            if (value.startsWith('9')) value = '+63' + value;
            else if (value.startsWith('63')) value = '+' + value;
            else if (value.startsWith('0')) value = value;

            if (value.startsWith('+63') && value.length > 13) value = value.substring(0, 13);
            else if (value.startsWith('09') && value.length > 11) value = value.substring(0, 11);

            input.value = value;
        });
    }

    // === AJAX Duplicate Checker ===
    async function checkDuplicate() {
        const first_name = addForm.querySelector('[name="first_name"]')?.value.trim() || '';
        const middle_name = addForm.querySelector('[name="middle_name"]')?.value.trim() || '';
        const last_name = addForm.querySelector('[name="last_name"]')?.value.trim() || '';
        const email = addForm.querySelector('[name="email"]')?.value.trim() || '';

        // Only check when at least one of these has value
        if (!first_name && !last_name && !email) return;

        try {
            const response = await fetch('{{ route('admin.employees.checkDuplicate') }}', { 
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({ first_name, middle_name, last_name, email })
            });

            const data = await response.json();

            const emailInput = addForm.querySelector('[name="email"]');
            const firstInput = addForm.querySelector('[name="first_name"]');
            const lastInput = addForm.querySelector('[name="last_name"]');

            const emailErr = ensureErrorDiv(emailInput, 'email-duplicate-error');
            const nameErrFirst = ensureErrorDiv(firstInput, 'name-duplicate-error');
            const nameErrLast = ensureErrorDiv(lastInput, 'name-duplicate-error');

            // === Email duplicate check ===
            if (data.duplicateEmail) {
                emailInput.classList.add('is-invalid');
                emailErr.textContent = 'Email already exists.';
            } else {
                emailInput.classList.remove('is-invalid');
                emailErr.textContent = '';
            }

            // === Full name duplicate check ===
            if (data.duplicateName) {
                firstInput.classList.add('is-invalid');
                lastInput.classList.add('is-invalid');
                nameErrFirst.textContent = 'An employee with this full name already exists.';
                nameErrLast.textContent = 'An employee with this full name already exists.';
            } else {
                firstInput.classList.remove('is-invalid');
                lastInput.classList.remove('is-invalid');
                nameErrFirst.textContent = '';
                nameErrLast.textContent = '';
            }
        } catch (error) {
            console.error('Duplicate check failed:', error);
        }
    }

    // === Debounce for duplicate checking ===
    function debounce(fn, delay) {
        let timeout;
        return (...args) => {
            clearTimeout(timeout);
            timeout = setTimeout(() => fn(...args), delay);
        };
    }
    const debouncedCheck = debounce(checkDuplicate, 400);

    // === Attach real-time validation ===
    addForm.querySelectorAll('input, select').forEach(input => {
        if (input.name === 'contact_number') setupContactAutoFormat(input);

        input.addEventListener('input', () => {
            if (input.required) validateRequired(input);
            if (input.name === 'contact_number') validateContact(input);
            if (input.name === 'email') validateEmail(input);

            // Run real-time duplicate checks
            if (['first_name', 'middle_name', 'last_name', 'email'].includes(input.name)) {
                debouncedCheck();
            }
        });
    });

    // === Final form submission validation ===
    addForm.addEventListener('submit', e => {
        let valid = true;
        addForm.querySelectorAll('[required]').forEach(i => {
            if (!validateRequired(i)) valid = false;
        });

        const contact = addForm.querySelector('[name="contact_number"]');
        const email = addForm.querySelector('[name="email"]');
        if (contact && !validateContact(contact)) valid = false;
        if (email && !validateEmail(email)) valid = false;

        // Require face capture before submit
        const captured = document.getElementById('captured_face');
        if (captured && !captured.value.trim()) {
            alert('Please capture a face image before submitting.');
            valid = false;
        }

        if (!valid) e.preventDefault();
    });

    // === Reset modal on close ===
    document.getElementById('addEmployeeModal').addEventListener('hidden.bs.modal', () => {
        addForm.reset();
        addForm.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
        addForm.querySelectorAll('.text-danger.small').forEach(el => el.textContent = '');
    });
});
</script>



<script>
document.addEventListener('DOMContentLoaded', function() {
    const contactRegex = /^(09\d{9}|\+639\d{9})$/;
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

    // === Utility: create or find error div ===
    function ensureErrorDiv(input, className) {
        let err = input.closest('.mb-3')?.querySelector(`.${className}`);
        if (!err) {
            err = document.createElement('div');
            err.classList.add('text-danger', 'small', 'mt-1', className);
            input.closest('.mb-3')?.appendChild(err);
        }
        return err;
    }

    // === Validation functions ===
    function validateRequired(input) {
        const errDiv = ensureErrorDiv(input, 'required-error');
        if (input.value.trim() === '') {
            input.classList.add('is-invalid');
            errDiv.textContent = 'This field is required.';
            return false;
        } else {
            input.classList.remove('is-invalid');
            errDiv.textContent = '';
            return true;
        }
    }

    function validateContact(input) {
        removeOldError(input);
        if (!contactRegex.test(input.value.trim())) {
            input.classList.add('is-invalid');
            showError(input, 'Please enter a valid PH mobile number (09xxxxxxxxx or +639xxxxxxxxx).');
            return false;
        }
        input.classList.remove('is-invalid');
        return true;
    }

    function validateEmail(input) {
        removeOldError(input);
        if (!emailRegex.test(input.value.trim())) {
            input.classList.add('is-invalid');
            showError(input, 'Please enter a valid email address.');
            return false;
        }
        input.classList.remove('is-invalid');
        return true;
    }

    // === PH Contact Auto-format ===
    function formatContactInput(input) {
        let value = input.value.replace(/[^\d+]/g, '');
        if (value.startsWith('9')) value = '+63' + value;
        else if (value.startsWith('63')) value = '+' + value;
        else if (value.startsWith('0')) value = value;

        if (value.startsWith('+63') && value.length > 13) value = value.substring(0, 13);
        else if (value.startsWith('09') && value.length > 11) value = value.substring(0, 11);
        input.value = value;
    }

    // === Real-time validation for all inputs inside any Edit modal ===
    document.body.addEventListener('input', function(e) {
        const input = e.target;
        if (!input.closest('[id^="editEmployeeModal"]')) return; // Only target edit modals

        if (input.required) validateRequired(input);

        if (input.name === 'contact_number') {
            formatContactInput(input);
            validateContact(input);
        }

        if (input.name === 'email') {
            validateEmail(input);
        }
    });

    // === When modal closes, clear all errors ===
    document.body.addEventListener('hidden.bs.modal', function(e) {
        const modal = e.target;
        if (!modal.id.startsWith('editEmployeeModal')) return;
        modal.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
        modal.querySelectorAll('.text-danger.small').forEach(el => el.textContent = '');
    });
});
</script>


<script>
document.addEventListener('DOMContentLoaded', function () {

    // ===================================================
    // 5️⃣ HIDE ASTERISK FOR FILLED REQUIRED FIELDS
    // ===================================================
    const fields = document.querySelectorAll('input[required], textarea[required], select[required]');
    fields.forEach(field => {
        const label = field.closest('.mb-3, .col-md-4')?.querySelector('label');
        if (!label) return;
        const asterisk = label.querySelector('.text-danger');
        if (!asterisk) return;

        toggleAsterisk(field, asterisk);
        field.addEventListener('input', () => toggleAsterisk(field, asterisk));
        field.addEventListener('blur', () => toggleAsterisk(field, asterisk));
    });

    function toggleAsterisk(input, asterisk) {
        if (input.value.trim() !== "") {
            asterisk.style.visibility = "hidden";
            asterisk.style.opacity = "0";
        } else {
            asterisk.style.visibility = "visible";
            asterisk.style.opacity = "1";
        }
    }

});
</script>


<script>
let stream=null, 
    video=document.getElementById('video'), 
    canvas=document.getElementById('canvas'), 
    preview=document.getElementById('preview'), 
    capturedInput=document.getElementById('captured_face');

let employeeToDelete=null;

// ====== Face Capture ======
function toggleCamera(){ if(stream){ stopCamera(); } else { startCamera(); } }
function startCamera(){ 
    video.style.display='block'; 
    navigator.mediaDevices.getUserMedia({video:true})
        .then(s=>{ stream=s; video.srcObject=stream; video.play(); video.addEventListener('click',captureImage); })
        .catch(()=>showNotification("Unable to access camera.")); 
}
function stopCamera(){ 
    if(stream){ stream.getTracks().forEach(t=>t.stop()); stream=null; } 
    video.style.display='none'; 
}
function captureImage(){ 

    const ctx = canvas.getContext('2d'); 
    canvas.width = video.videoWidth; 
    canvas.height = video.videoHeight; 

    ctx.drawImage(video, 0, 0, canvas.width, canvas.height); 

    const dataURL = canvas.toDataURL('image/jpeg'); 
    capturedInput.value = dataURL; 
    preview.src = dataURL; 
    preview.style.display = 'block'; 

    stopCamera();

}

document.getElementById('addEmployeeModal').addEventListener('hidden.bs.modal',()=>{ stopCamera(); preview.style.display='none'; capturedInput.value=''; });

// Restrict contact number input to numbers and '+'
const contactInput = document.getElementById('contact_number');

contactInput.addEventListener('keypress', function(e) {
    const char = String.fromCharCode(e.which);
    if (!/[0-9+]/.test(char)) { // allow only digits and '+'
        e.preventDefault();
    }
});

// Optional: prevent pasting invalid characters
contactInput.addEventListener('paste', function(e) {
    const paste = (e.clipboardData || window.clipboardData).getData('text');
    if (!/^[0-9+]*$/.test(paste)) {
        e.preventDefault();
    }
});


// Add Employee
function submitEmployeeForm() {
    const form = document.getElementById('addEmployeeForm');

    // HTML5 validation
    if (!form.checkValidity()) {
        form.reportValidity();
        return;
    }
    const submitBtn = document.getElementById('submitEmployeeBtn');
    submitBtn.disabled = true;

    const formData = new FormData(form);

    fetch('{{ route("admin.employees.store") }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN':'{{ csrf_token() }}',
            'Accept':'application/json'
        },
        body: formData
    })
    .then(async res => {
        if (res.ok) return res.json();
        if (res.status === 422) { // Laravel validation errors
            const data = await res.json();
            Object.values(data.errors).forEach(arr => arr.forEach(msg => showNotification(msg, false)));
            throw new Error('Validation failed');
        }
        throw new Error('Something went wrong');
    })
    .then(data => {
        form.reset();
        document.getElementById('preview').style.display = 'none';
        document.getElementById('captured_face').value = '';
        contactInput.classList.remove('is-invalid');

        Swal.fire({
            icon: 'success',
            title: 'Employee Added',
            text: data.message || 'Employee has been successfully added!',
            confirmButtonColor: '#198754'
        }).then(() => location.reload());
    })
    .catch(err => {
        if (err.message !== 'Validation failed') showNotification(err.message, false);
    })
    .finally(() => submitBtn.disabled = false);
}

// ====== Edit Employee Camera ======
let editStreams = {}; // track streams per employee

function toggleEditCamera(empId) {
    const video = document.getElementById('videoEdit' + empId);
    const canvas = document.getElementById('canvasEdit' + empId);
    const preview = document.getElementById('previewEdit' + empId);
    const input = document.getElementById('captured_faceEdit' + empId);

    if (video.style.display === 'block') {
        stopEditCamera(empId);
    } else {
        startEditCamera(empId, video, canvas, preview, input);
    }
}

function startEditCamera(empId, video, canvas, preview, input){
    navigator.mediaDevices.getUserMedia({ video: true })
        .then(stream => {
            editStreams[empId] = stream;
            video.srcObject = stream;
            video.style.display = 'block';

            // Capture photo on click
            const captureHandler = function capture() {
                canvas.width = video.videoWidth;
                canvas.height = video.videoHeight;
                canvas.getContext('2d').drawImage(video, 0, 0);
                const dataURL = canvas.toDataURL('image/jpeg');
                input.value = dataURL;
                preview.src = dataURL;
                preview.style.display = 'block';
                stopEditCamera(empId);
                video.removeEventListener('click', captureHandler);
            };

            video.addEventListener('click', captureHandler);
        })
        .catch(() => alert("Unable to access camera."));
}

function stopEditCamera(empId){
    const video = document.getElementById('videoEdit' + empId);
    if(editStreams[empId]){
        editStreams[empId].getTracks().forEach(track => track.stop());
        editStreams[empId] = null;
    }
    video.style.display = 'none';
}

// ====== Automatically stop camera when modal closes ======
document.querySelectorAll('[id^="editEmployeeModal"]').forEach(modalEl => {
    modalEl.addEventListener('hidden.bs.modal', () => {
        const empId = modalEl.id.replace('editEmployeeModal', '');
        stopEditCamera(empId);
    });
});

// ====== Contact number validation ======
document.querySelectorAll('[id^="contact_numberEdit"]').forEach(inputEl => {
    inputEl.addEventListener('keypress', function(e) {
        const char = String.fromCharCode(e.which);
        if (!/[0-9+]/.test(char)) e.preventDefault();
    });

    inputEl.addEventListener('paste', function(e){
        const paste = (e.clipboardData || window.clipboardData).getData('text');
        if (!/^[0-9+]*$/.test(paste)) e.preventDefault();
    });
});

// ====== Edit Save Confirmation ======
function confirmEditSave(form) {
    Swal.fire({
        icon: 'success',
        title: 'Updated',
        text: 'Employee updated successfully!',
        confirmButtonColor: '#198754'
    }).then(() => form.submit());
    return false; // prevent default submit
}


// ====== Make Admin ======
let employeeToPromote = null;

function openAdminPasswordModal(employeeId) {
    employeeToPromote = employeeId;
    document.getElementById('adminPassword').value = '';
    document.getElementById('confirmAdminPassword').value = '';
    document.getElementById('passwordError').classList.add('d-none');
    document.getElementById('confirmPasswordError').classList.add('d-none');
    document.getElementById('credentialsBox').classList.add('d-none');
    document.getElementById('passwordInputWrapper').classList.remove('d-none');
    document.getElementById('confirmPasswordWrapper').classList.remove('d-none');
    document.getElementById('confirmBtn').classList.remove('d-none');

    let adminModalEl = document.getElementById('adminPasswordModal');
    let adminModal = bootstrap.Modal.getOrCreateInstance(adminModalEl);
    adminModal.show();
}

document.getElementById('adminPasswordForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const password = document.getElementById('adminPassword').value;
    const confirmPassword = document.getElementById('confirmAdminPassword').value;

    // Reset error messages
    document.getElementById('passwordError').classList.add('d-none');
    document.getElementById('confirmPasswordError').classList.add('d-none');

    // Password match validation
    if (password !== confirmPassword) {
        document.getElementById('confirmPasswordError').classList.remove('d-none');
        return;
    }

    if (!employeeToPromote) return;

    fetch(`{{ url('admin/employees/make-admin') }}/${employeeToPromote}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ admin_password: password })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            bootstrap.Modal.getInstance(document.getElementById('adminPasswordModal')).hide();
            Swal.fire({
                icon: 'success',
                title: 'Admin Created',
                text: 'Login credentials have been sent to the user\'s email.',
                confirmButtonColor: '#17007C'
            }).then(() => location.reload());
        } else {
            document.getElementById('passwordError').classList.remove('d-none');
        }
    })
    .catch(() => {
        Swal.fire("Error", "Something went wrong while making admin.", "error");
    });
});


// ====== Delete Employee ======
function confirmDelete(employeeId) {
    Swal.fire({
        title: "Are you sure?",
        text: "This action cannot be undone.",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#d33",
        cancelButtonColor: "#6c757d",
        confirmButtonText: "Yes, delete it!"
    }).then((result) => {
        if (result.isConfirmed) {
            fetch(`{{ url('admin/employees') }}/${employeeId}`, {
                method: "DELETE",
                headers: {
                    "X-CSRF-TOKEN": "{{ csrf_token() }}",
                    "Accept": "application/json"
                }
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    Swal.fire("Deleted!", "Employee has been removed.", "success");
                    // Remove row instantly without reload
                    document.getElementById(`employee-row-${employeeId}`).remove();
                } else {
                    Swal.fire("Error", data.message || "Failed to delete employee.", "error");
                }
            })
            .catch(() => Swal.fire("Error", "Something went wrong.", "error"));
        }
    });
}



// ====== Notification ======
// ====== SweetAlert Notification ======
function showNotification(message, success = true, reload = false) {
    Swal.fire({
        icon: success ? 'success' : 'error',
        title: success ? 'Success' : 'Error',
        text: message,
        confirmButtonColor: success ? '#198754' : '#dc3545'
    }).then(() => {
        if (reload) {
            location.reload();
        }
    });
}






</script>


@endpush

<form method="POST" action="{{ route('admin.settings.salary.store') }}">
    @csrf
    <div class="row g-2 mb-3">
        <div class="col-md-4">
            <select name="position" class="form-select" required>
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
            <input name="daily_rate" type="number" step="0.01" class="form-control" placeholder="Daily Rate" required>
        </div>
        <div class="col-md-3">
            <input name="overtime" type="number" step="0.01" class="form-control" placeholder="Overtime Rate" required>
        </div>
        <div class="col-md-2">
            <button class="btn btn-success w-100">Add</button>
        </div>
    </div>
</form>

<table class="table table-bordered bg-white">
    <thead class="table-light">
        <tr><th>Position</th><th>Daily Rate</th><th>Overtime</th><th>Actions</th></tr>
    </thead>
    <tbody>
        @foreach($salaryRates as $rate)
        <tr>
            <td>{{ $rate->position }}</td>
            <td>₱{{ number_format($rate->daily_rate, 2) }}</td>
            <td>₱{{ number_format($rate->overtime, 2) }}</td>
            <td class="d-flex gap-1">
                <form method="POST" action="{{ route('admin.settings.salary.delete', $rate->id) }}" onsubmit="return confirm('Delete this salary rate?')">
                    @csrf @method('DELETE')
                    <button class="btn btn-danger btn-sm"><i class="bi bi-trash"></i></button>
                </form>

                <!-- Edit Button -->
                <button class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#editModal{{ $rate->id }}">
                    <i class="bi bi-pencil"></i>
                </button>

                <!-- Edit Modal -->
                <div class="modal fade" id="editModal{{ $rate->id }}" tabindex="-1" aria-labelledby="editModalLabel{{ $rate->id }}" aria-hidden="true">
                    <div class="modal-dialog">
                        <form method="POST" action="{{ route('admin.settings.salary.update', $rate->id) }}">
                            @csrf
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="editModalLabel{{ $rate->id }}">Edit Salary Rate</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="mb-2">
                                        <label>Position</label>
                                        <input type="text" name="position" class="form-control" value="{{ $rate->position }}" required>
                                    </div>
                                    <div class="mb-2">
                                        <label>Daily Rate</label>
                                        <input type="number" step="0.01" name="daily_rate" class="form-control" value="{{ $rate->daily_rate }}" required>
                                    </div>
                                    <div class="mb-2">
                                        <label>Overtime</label>
                                        <input type="number" step="0.01" name="overtime" class="form-control" value="{{ $rate->overtime }}" required>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="submit" class="btn btn-primary">Update</button>
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

@extends('admin_panel.layout.app')

@section('main-content')
<div class="container mt-4">
    <h2>Update Profile</h2>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <form method="POST" action="{{ route('admin_panel.admin.update.profile') }}">
        @csrf

        <div class="mb-3">
            <label class="form-label">Name</label>
            <input type="text" name="name" value="{{ old('name', $admin->name) }}" class="form-control" required>
            @error('name') <span class="text-danger">{{ $message }}</span> @enderror
        </div>

        <div class="mb-3">
            <label class="form-label">Phone Number</label>
            <input type="text" name="phone" value="{{ old('phone', $admin->phone) }}" class="form-control" {{ $admin->isMainAdmin() ? 'readonly' : 'required' }}>
            @if($admin->isMainAdmin())
                <small class="text-muted">Main admin phone number cannot be changed.</small>
            @endif
            @error('phone') <span class="text-danger d-block">{{ $message }}</span> @enderror
        </div>

        <button type="submit" class="btn btn-primary">Update Profile</button>
    </form>
</div>
@endsection

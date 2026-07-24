@extends('admin_panel.layout.app')
@section('title', 'Push Notifications')

@section('main-content')

{{-- ─────────────────── SEND FORM ─────────────────── --}}
<div class="card shadow-sm rounded-3 border-0 p-4 mb-4">
    <h5 class="fw-bold mb-4"><i class="bi bi-bell-fill me-2 text-primary"></i>Send Push Notification</h5>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show">
            <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- multipart required for file upload --}}
    <form method="POST" action="{{ route('admin_panel.admin.notifications.send') }}"
          enctype="multipart/form-data">
        @csrf
        <div class="row g-3">

            {{-- Title --}}
            <div class="col-md-6">
                <label class="form-label fw-semibold">Title</label>
                <input type="text" name="title" class="form-control"
                       placeholder="Notification title" required maxlength="255"
                       value="{{ old('title') }}">
            </div>

            {{-- Target --}}
            <div class="col-md-6">
                <label class="form-label fw-semibold">Target</label>
                <div class="input-group">
                    <input type="text" id="targetDisplay" class="form-control bg-white"
                           value="🌐 All Users" readonly style="cursor:pointer"
                           data-bs-toggle="modal" data-bs-target="#userSelectModal">
                    <input type="hidden" name="target" id="targetValue" value="all">
                    <button type="button" class="btn btn-outline-primary"
                            data-bs-toggle="modal" data-bs-target="#userSelectModal">
                        <i class="bi bi-funnel-fill"></i>
                    </button>
                </div>
            </div>

            {{-- Message --}}
            <div class="col-12">
                <label class="form-label fw-semibold">Message</label>
                <textarea name="body" class="form-control" rows="3"
                          placeholder="Notification body..." required maxlength="1000">{{ old('body') }}</textarea>
            </div>

            {{-- Image upload --}}
            <div class="col-12">
                <label class="form-label fw-semibold">
                    Image <span class="text-muted fw-normal">(optional · JPG/PNG/WebP · max 2 MB)</span>
                </label>

                {{-- Drop zone --}}
                <label for="imageFile" id="dropZone" class="border border-2 border-dashed rounded-3 p-4 text-center d-block"
                     style="border-color:#ced4da!important;cursor:pointer;transition:border-color .2s">
                    <input type="file" name="image" id="imageFile" accept="image/jpeg,image/png,image/webp"
                           class="visually-hidden">
                    <div id="dropPrompt">
                        <i class="bi bi-cloud-arrow-up fs-2 text-muted"></i>
                        <p class="mb-1 text-muted mt-2">Drag &amp; drop an image here, or <span class="text-primary fw-semibold">browse</span></p>
                        <p class="text-muted small mb-0">Supported: JPG, PNG, WebP</p>
                    </div>
                    <div id="imagePreviewWrap" style="display:none">
                        <img id="imagePreview" src="" alt="Preview"
                             class="rounded img-fluid mx-auto d-block"
                             style="max-height:180px;max-width:100%;object-fit:contain;">
                        <button type="button" id="btnRemoveImage"
                                class="btn btn-sm btn-outline-danger mt-2 position-relative" style="z-index: 10;">
                            <i class="bi bi-trash me-1"></i>Remove
                        </button>
                    </div>
                </label>
            </div>

            <div class="col-12">
                <button type="submit" class="btn btn-primary px-4">
                    <i class="bi bi-send-fill me-1"></i> Send Notification
                </button>
            </div>
        </div>
    </form>
</div>

{{-- ─────────────────── HISTORY TABLE ─────────────────── --}}
<div class="card shadow-sm rounded-3 border-0 p-4">
    <h5 class="fw-bold mb-3"><i class="bi bi-clock-history me-2"></i>Notification History</h5>
    <div class="table-responsive">
        <table id="notifHistoryTable" class="table table-hover align-middle w-100">
            <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Title</th>
                    <th>Message</th>
                    <th>Image</th>
                    <th>Target</th>
                    <th>✓ Sent</th>
                    <th>✗ Failed</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                @foreach($history as $i => $n)
                    <tr>
                        <td>{{ $i + 1 }}</td>
                        <td class="fw-semibold">{{ $n->title }}</td>
                        <td>{{ Str::limit($n->body, 70) }}</td>
                        <td>
                            @if($n->image_url)
                                <a href="{{ $n->image_url }}" target="_blank">
                                    <img src="{{ $n->image_url }}" alt="img" class="rounded"
                                         style="height:38px;width:52px;object-fit:cover"
                                         onerror="this.parentElement.innerHTML='<span class=text-muted>broken</span>'">
                                </a>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td>
                            <span class="badge {{ $n->target === 'all' ? 'bg-primary' : 'bg-secondary' }}">
                                {{ $n->target_label ?? ($n->target === 'all' ? 'All Users' : 'User #'.$n->target) }}
                            </span>
                        </td>
                        <td><span class="badge bg-success">{{ $n->success_count }}</span></td>
                        <td><span class="badge bg-danger">{{ $n->failure_count }}</span></td>
                        <td data-order="{{ $n->created_at->timestamp }}">
                            {{ $n->created_at->format('d M Y, h:i A') }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

{{-- ─────────────────── USER SELECT MODAL ─────────────────── --}}
<div class="modal fade" id="userSelectModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content border-0 shadow">

            <div class="modal-header bg-primary text-white py-3 align-items-start">
                <div>
                    <h5 class="modal-title mb-1">
                        <i class="bi bi-people-fill me-2"></i>Select Notification Target
                    </h5>
                    <div class="small text-white-50">Choose specific users, filtered users, or all eligible users.</div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            {{-- Filter bar --}}
            <div class="px-4 pt-3 pb-3 border-bottom bg-light">
                <div class="row gx-3 gy-2 align-items-end mb-2">
                    <div class="col-sm-10">
                        <label class="form-label small fw-bold text-secondary mb-1">Search</label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-white"><i class="bi bi-search text-muted"></i></span>
                            <input type="text" id="filterSearch" class="form-control border-start-0 ps-0"
                                   placeholder="Name, phone, or email...">
                            <button type="button" class="btn btn-outline-secondary bg-white border-start-0" id="btnClearSearch"
                                    title="Clear search">
                                <i class="bi bi-x-circle text-muted"></i>
                            </button>
                        </div>
                    </div>
                    <div class="col-sm-2">
                        <button type="button" class="btn btn-light btn-sm border w-100 fw-semibold text-secondary" id="btnResetFilters">
                            Reset
                        </button>
                    </div>
                </div>
                <div class="row gx-3 gy-2 align-items-end">
                    <div class="col-sm-3">
                        <label class="form-label small fw-bold text-secondary mb-1">Role</label>
                        <select id="filterRole" class="form-select form-select-sm">
                            <option value="">All Roles</option>
                            <option value="customer">Customer</option>
                            <option value="agent">Agent</option>
                            <option value="subadmin">Subadmin</option>
                        </select>
                    </div>
                    <div class="col-sm-3">
                        <label class="form-label small fw-bold text-secondary mb-1">Approval</label>
                        <select id="filterApproval" class="form-select form-select-sm">
                            <option value="">All Statuses</option>
                            <option value="approved">Approved</option>
                            <option value="pending">Pending</option>
                            <option value="rejected">Rejected</option>
                        </select>
                    </div>
                    <div class="col-sm-3">
                        <label class="form-label small fw-bold text-secondary mb-1">CRM Status</label>
                        <select id="filterCrm" class="form-select form-select-sm">
                            <option value="">All CRM Statuses</option>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                            <option value="lead">Lead</option>
                        </select>
                    </div>
                    <div class="col-sm-3">
                        <label class="form-label small fw-bold text-secondary mb-1">Location</label>
                        <select id="filterLocation" class="form-select form-select-sm">
                            <option value="">All Locations</option>
                            @foreach($locations as $loc)
                                <option value="{{ $loc->id }}">{{ $loc->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="mt-3 d-flex justify-content-between align-items-center">
                    <span class="badge bg-primary rounded-pill px-3 py-2 shadow-sm" id="resultCount">{{ $users->count() }} Users Found</span>
                    <div class="form-check form-switch mb-0">
                        <input class="form-check-input" type="checkbox" id="selectAllVisible">
                        <label class="form-check-label small fw-semibold" for="selectAllVisible">Select all visible</label>
                    </div>
                </div>
            </div>

            <div class="modal-body p-0" style="overflow-y:auto;max-height:400px">
                {{-- All Users Special Toggle --}}
                <div class="user-row border-bottom px-4 py-3 d-flex align-items-center gap-3 bg-light"
                     data-id="all" data-role="" data-approval="" data-search=""
                     style="cursor:pointer">
                    <div class="form-check mb-0">
                        <input class="form-check-input user-checkbox" type="checkbox" value="all" style="width:20px;height:20px;cursor:pointer;">
                    </div>
                    <span class="badge bg-primary px-2 py-1">ALL</span>
                    <div class="flex-grow-1">
                        <div class="fw-bold text-dark">All Users</div>
                        <div class="text-muted small">Send to every registered device in the database</div>
                    </div>
                </div>

                {{-- Individual users --}}
                @foreach($users as $u)
                <div class="user-row border-bottom px-4 py-3 d-flex align-items-center gap-3"
                     data-id="{{ $u->id }}"
                     data-role="{{ $u->role }}"
                     data-approval="{{ $u->approval_status ?? '' }}"
                     data-crm="{{ $u->crm_status ?? '' }}"
                     data-location="{{ $u->location_id ?? '' }}"
                     data-search="{{ strtolower($u->name . ' ' . ($u->phone_number ?? '') . ' ' . ($u->email ?? '') . ' ' . ($u->location ? $u->location->name : '')) }}"
                     style="cursor:pointer; transition: background-color 0.2s;">
                    
                    <div class="form-check mb-0">
                        <input class="form-check-input user-checkbox individual-checkbox" type="checkbox" value="{{ $u->id }}" style="width:20px;height:20px;cursor:pointer;">
                    </div>

                    <div class="rounded-circle d-flex align-items-center justify-content-center text-white fw-bold shadow-sm" 
                         style="width:42px;height:42px; background-color: @if($u->role === 'customer') #198754 @elseif($u->role === 'agent') #0dcaf0 @else #ffc107 @endif">
                        {{ strtoupper(substr($u->name ?? $u->role, 0, 1)) }}
                    </div>
                    <div class="flex-grow-1">
                        <div class="fw-bold text-dark">{{ $u->name ?: 'No Name' }}</div>
                        <div class="text-muted small mt-1">
                            <i class="bi bi-telephone text-primary me-1"></i>{{ $u->phone_number ?? '—' }}
                            &nbsp;·&nbsp;
                            <i class="bi bi-person-badge text-primary me-1"></i>{{ ucfirst($u->role) }}
                            @if($u->approval_status)
                            &nbsp;·&nbsp;
                            <span class="badge bg-light text-dark border">{{ ucfirst($u->approval_status) }}</span>
                            @endif
                            @if($u->crm_status)
                            &nbsp;·&nbsp;
                            <span class="badge bg-light text-secondary border">CRM: {{ ucfirst($u->crm_status) }}</span>
                            @endif
                            @if($u->location)
                            &nbsp;·&nbsp;
                            <span class="badge bg-light text-secondary border"><i class="bi bi-geo-alt me-1"></i>{{ $u->location->name }}</span>
                            @endif
                        </div>
                    </div>
                </div>
                @endforeach

                {{-- Empty state --}}
                <div id="noResults" class="text-center text-muted py-5" style="display:none">
                    <i class="bi bi-search fs-1 d-block mb-3 text-black-50"></i>
                    <h5>No users match your filters.</h5>
                    <p class="small">Try clearing your search or changing the role filter.</p>
                </div>
            </div>

            <div class="modal-footer bg-light d-flex justify-content-between align-items-center py-3">
                <span class="text-muted fw-semibold">
                    Selected: <span id="modalSelectedCount" class="badge bg-dark rounded-pill fs-6 px-3">0</span>
                </span>
                <div>
                    <button type="button" class="btn btn-outline-secondary me-2" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary fw-bold px-4" id="btnApplySelection">Apply Selection</button>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
$(function () {

    // ── DataTable ────────────────────────────────────────────────────────────
    $('#notifHistoryTable').DataTable({
        order: [[7, 'desc']],
        pageLength: 10,
        lengthMenu: [10, 25, 50, 100],
        columnDefs: [{ orderable: false, targets: [3] }],
        language: { search: 'Filter:', info: 'Showing _START_–_END_ of _TOTAL_' }
    });

    // ── Image upload / drop zone ─────────────────────────────────────────────
    var $zone   = $('#dropZone');
    var $file   = $('#imageFile');
    var $prompt = $('#dropPrompt');
    var $wrap   = $('#imagePreviewWrap');
    var $prev   = $('#imagePreview');

    // Click anywhere on zone → native label handles the file picker click

    $file.on('change', function () {
        showPreview(this.files[0]);
    });

    $('#btnRemoveImage').on('click', function (e) {
        e.preventDefault();
        e.stopPropagation();
        $file.val('');
        $wrap.hide();
        $prompt.show();
        $zone.css('border-color', '#ced4da');
    });

    // Drag & drop
    $zone.on('dragover', function (e) {
        e.preventDefault();
        $zone.css('border-color', '#0d6efd');
    }).on('dragleave drop', function (e) {
        e.preventDefault();
        $zone.css('border-color', '#ced4da');
        if (e.type === 'drop') {
            var file = e.originalEvent.dataTransfer.files[0];
            if (file && file.type.startsWith('image/')) {
                // Transfer to real input (DataTransfer API)
                var dt = new DataTransfer();
                dt.items.add(file);
                $file[0].files = dt.files;
                showPreview(file);
            }
        }
    });

    function showPreview(file) {
        if (!file) return;
        var reader = new FileReader();
        reader.onload = function (e) {
            $prev.attr('src', e.target.result);
            $prompt.hide();
            $wrap.show();
            $zone.css('border-color', '#198754');
        };
        reader.readAsDataURL(file);
    }

    // ── User selection modal ─────────────────────────────────────────────────
    var selectedIds = new Set(['all']); // Start with 'all' selected

    function applyFilters() {
        var role     = $('#filterRole').val();
        var approval = $('#filterApproval').val();
        var crm      = $('#filterCrm').val();
        var location = $('#filterLocation').val();
        var search   = $('#filterSearch').val().toLowerCase().trim();
        var count    = 0;

        $('.user-row').each(function () {
            var $r         = $(this);
            var rowId      = $r.data('id');
            var rowRole    = $r.data('role');   
            var rowApp     = $r.data('approval');   
            var rowCrm     = $r.data('crm');   
            var rowLoc     = $r.data('location');   
            var rowSearch  = $r.data('search'); 

            if (rowId === 'all') { $r.show(); return; }

            var matchRole     = !role || rowRole === role;
            var matchApproval = !approval || rowApp === approval;
            var matchCrm      = !crm || rowCrm === crm;
            var matchLoc      = !location || String(rowLoc) === location;
            var matchSearch   = !search || String(rowSearch).indexOf(search) !== -1;
            
            var visible = matchRole && matchApproval && matchCrm && matchLoc && matchSearch;
            $r.toggleClass('d-none', !visible);
            if (visible) count++;
        });

        $('#resultCount').text(count + ' user' + (count !== 1 ? 's' : ''));
        $('#noResults').toggle(count === 0);
        updateSelectAllState();
    }

    $('#filterRole, #filterApproval, #filterCrm, #filterLocation').on('change', applyFilters);
    $('#filterSearch').on('input', applyFilters);

    $('#btnClearSearch').on('click', function () {
        $('#filterSearch').val('');
        applyFilters();
    });

    $('#btnResetFilters').on('click', function () {
        $('#filterRole').val('');
        $('#filterApproval').val('');
        $('#filterCrm').val('');
        $('#filterLocation').val('');
        $('#filterSearch').val('');
        applyFilters();
    });

    // Checkbox and Row Click Logic
    $(document).on('click', '.user-row', function (e) {
        if ($(e.target).is('input[type="checkbox"]')) return; // let checkbox handle itself
        var cb = $(this).find('input[type="checkbox"]');
        cb.prop('checked', !cb.prop('checked')).trigger('change');
    });

    $(document).on('change', '.user-checkbox', function() {
        var val = $(this).val();
        var isChecked = $(this).prop('checked');
        
        if (val === 'all') {
            if (isChecked) {
                selectedIds.clear();
                selectedIds.add('all');
                $('.individual-checkbox').prop('checked', false);
            } else {
                selectedIds.delete('all');
            }
        } else {
            if (isChecked) {
                selectedIds.add(val.toString());
                selectedIds.delete('all'); // Uncheck 'all' if we check an individual
                $('.user-checkbox[value="all"]').prop('checked', false);
            } else {
                selectedIds.delete(val.toString());
            }
        }
        updateSelectionUI();
    });

    $('#selectAllVisible').on('change', function() {
        var isChecked = $(this).prop('checked');
        if (isChecked) {
            selectedIds.delete('all');
            $('.user-checkbox[value="all"]').prop('checked', false);
            
            $('.user-row').not('.d-none').each(function() {
                var id = $(this).data('id');
                if (id !== 'all') {
                    selectedIds.add(id.toString());
                    $(this).find('.individual-checkbox').prop('checked', true);
                }
            });
        } else {
            $('.user-row').not('.d-none').each(function() {
                var id = $(this).data('id');
                if (id !== 'all') {
                    selectedIds.delete(id.toString());
                    $(this).find('.individual-checkbox').prop('checked', false);
                }
            });
        }
        updateSelectionUI();
    });

    function updateSelectAllState() {
        var totalVisible = $('.user-row').not('.d-none').not('[data-id="all"]').length;
        var checkedVisible = $('.user-row').not('.d-none').not('[data-id="all"]').find('.individual-checkbox:checked').length;
        
        var cb = $('#selectAllVisible');
        if (totalVisible === 0) {
            cb.prop('checked', false).prop('disabled', true);
        } else {
            cb.prop('disabled', false);
            cb.prop('checked', totalVisible === checkedVisible);
        }
    }

    function updateSelectionUI() {
        // Sync checkboxes with internal state
        $('.user-checkbox').each(function() {
            $(this).prop('checked', selectedIds.has($(this).val().toString()));
        });
        
        // Update row background color for active state
        $('.user-row').each(function() {
            var isChecked = selectedIds.has($(this).data('id').toString());
            $(this).toggleClass('bg-white', !isChecked).toggleClass('bg-light', isChecked);
        });

        // Update counter
        if (selectedIds.has('all')) {
            $('#modalSelectedCount').text('All Users');
        } else {
            $('#modalSelectedCount').text(selectedIds.size);
        }
        updateSelectAllState();
    }

    // Apply Button
    $('#btnApplySelection').on('click', function() {
        if (selectedIds.size === 0) {
            alert("Please select at least one target.");
            return;
        }

        var targetVal = Array.from(selectedIds).join(',');
        var displayStr = "";
        
        if (selectedIds.has('all')) {
            targetVal = "all";
            displayStr = "🌐 All Users";
        } else if (selectedIds.size === 1) {
            // Find name for the single selection
            var id = Array.from(selectedIds)[0];
            displayStr = $('.user-row[data-id="'+id+'"]').find('.fw-bold').first().text().trim();
        } else {
            displayStr = "👥 " + selectedIds.size + " Users Selected";
        }

        $('#targetValue').val(targetVal);
        $('#targetDisplay').val(displayStr);
        $('#userSelectModal').modal('hide');
    });

    // Initialize UI on open
    $('#userSelectModal').on('show.bs.modal', function () {
        // Read current targetValue if it was set
        var currentTarget = $('#targetValue').val();
        selectedIds.clear();
        if (currentTarget === 'all' || !currentTarget) {
            selectedIds.add('all');
        } else {
            currentTarget.split(',').forEach(id => selectedIds.add(id));
        }
        
        applyFilters();
        updateSelectionUI();
    });

});
</script>
@endpush

@endsection

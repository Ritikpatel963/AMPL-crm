@extends('admin_panel.layout.app')

@section('title', 'Chat Logs')

@section('main-content')
<div class="card shadow-sm border-0 rounded-3 p-4">
    <h5 class="fw-bold text-primary mb-4">Chat Logs</h5>
    <small class="text-muted mb-4 d-block">View all chat interactions between agents and assigned customers</small>

    <!-- Select Agent -->
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body">
            <form class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Select Agent</label>
                    <select class="form-select form-select-lg rounded-3" id="agentSelect">
                        <option selected disabled>Choose Agent...</option>
                        <option value="1">Agent Rahul</option>
                        <option value="2">Agent Priya</option>
                        <option value="3">Agent Arjun</option>
                    </select>
                </div>
                <div class="col-md-auto">
                    <button type="button" class="btn btn-primary rounded-pill px-4 shadow-sm" id="loadCustomersBtn">
                        <i class="bi bi-people-fill me-2"></i>Load Customers
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Customers Table -->
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body">
            <h6 class="fw-semibold text-dark mb-3">
                <i class="bi bi-people me-2 text-primary"></i> Assigned Customers
            </h6>

            <table id="customerTable" class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Customer Name</th>
                        <th>Phone</th>
                        <th>KYC Status</th>
                        <th class="text-center">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>1</td>
                        <td>Adarsh Kumar</td>
                        <td>+91 9876543210</td>
                        <td><span class="badge bg-success">Approved</span></td>
                        <td class="text-center">
                            <button class="btn btn-outline-primary btn-sm view-chat-btn" data-customer="Adarsh Kumar" data-agent="Agent Rahul">
                                <i class="bi bi-chat-dots"></i> View Chat
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Chat Modal -->
<div class="modal fade" id="chatModal" tabindex="-1" aria-labelledby="chatModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable modal-lg">
        <div class="modal-content rounded-4">
            <div class="modal-header bg-primary text-white rounded-top-4">
                <h5 class="modal-title" id="chatModalLabel">Chat between Agent & Customer</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="chatBody" style="height:400px; overflow-y:auto;">
                <!-- Chat messages will be loaded here -->
            </div>
        </div>
    </div>
</div>
@endsection

{{-- @push('scripts') --}}
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script>
$(document).ready(function() {
    var table = $('#customerTable').DataTable({
        pageLength: 6,
        lengthChange: true,
        language: { searchPlaceholder: "Search customer...", search: "" }
    });

    // Handle chat view
    $('#customerTable tbody').on('click', '.view-chat-btn', function(){
        var customer = $(this).data('customer');
        var agent = $(this).data('agent');

        $('#chatModalLabel').text('Chat between ' + agent + ' & ' + customer);

        // Example messages (you can load dynamically later)
        $('#chatBody').html(`
            <div class="mb-2 text-start">
                <span class="badge bg-light text-dark me-2">Customer</span>
                <span class="bg-light p-2 rounded-3 shadow-sm d-inline-block">Hello, I need help with my order.</span>
            </div>
            <div class="mb-2 text-end">
                <span class="badge bg-primary text-white me-2">Agent</span>
                <span class="bg-primary text-white p-2 rounded-3 shadow-sm d-inline-block">Sure! I will check it for you.</span>
            </div>
        `);

        $('#chatBody').scrollTop($('#chatBody')[0].scrollHeight);

        // Show modal
        var chatModal = new bootstrap.Modal(document.getElementById('chatModal'));
        chatModal.show();
    });
});
</script>
{{-- @endpush --}}

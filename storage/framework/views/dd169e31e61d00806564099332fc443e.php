
<?php $__env->startSection('title', 'Payment Management'); ?>

<?php $__env->startSection('main-content'); ?>
<div class="card shadow-sm border-0 rounded-3 p-4">

    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h5 class="fw-bold text-primary mb-0"><i class="bi bi-credit-card-2-front me-2"></i> Payment Management</h5>
    </div>

    <!-- Table -->
    <div class="table-responsive">
        <table id="paymentTable" class="table table-hover align-middle w-100">
            <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Transaction ID</th>
                    <th>Customer</th>
                    <th>Amount</th>
                    <th>Method</th>
                    <th>Status</th>
                    <th>Date</th>
                    <th class="text-center">Actions</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>1</td>
                    <td>#TXN123456</td>
                    <td>John Doe</td>
                    <td>$120.00</td>
                    <td>Razorpay</td>
                    <td><span class="badge bg-success">Success</span></td>
                    <td>2025-10-15</td>
                    <td class="text-center">
                        <button class="btn btn-sm btn-outline-info"><i class="bi bi-eye"></i></button>
                        <button class="btn btn-sm btn-outline-danger"><i class="bi bi-arrow-counterclockwise"></i> Refund</button>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('styles'); ?>
<!-- ✅ Include DataTables CSS -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
<style>
    .dataTables_wrapper .dataTables_filter input {
        border-radius: 6px;
        border: 1px solid #ced4da;
        padding: 5px 10px;
    }
    .dataTables_wrapper .dataTables_paginate .paginate_button {
        padding: 0.3rem 0.7rem;
        border-radius: 6px;
    }
    .btn-outline-info:hover {
        background-color: #0dcaf0;
        color: #fff;
    }
    .btn-outline-danger:hover {
        background-color: #dc3545;
        color: #fff;
    }
</style>
<?php $__env->stopPush(); ?>


<!-- ✅ Include jQuery + DataTables JS -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

<script>
$(document).ready(function () {
    // ✅ Initialize DataTable
    $('#paymentTable').DataTable({
        pageLength: 5,
        responsive: true,
        language: {
            searchPlaceholder: "Search payments...",
            search: "",
        }
    });
});
</script>


<?php echo $__env->make('admin_panel.layout.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH E:\website-project\Amplchat\CMS\resources\views\admin_panel\payments\payment_management.blade.php ENDPATH**/ ?>
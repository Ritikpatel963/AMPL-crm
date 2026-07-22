<?php $__env->startSection('title', 'Calling CRM Follow-Up Report'); ?>
<?php $__env->startPush('styles'); ?>
<link rel="stylesheet" href="<?php echo e(asset('css/crm/calling-crm.css')); ?>">
<?php $__env->stopPush(); ?>

<?php $__env->startSection('main-content'); ?>
<div class="calling-crm-canvas calling-crm-user-report" data-report-endpoint="reports/follow-ups">
<main class="crm-page-main" data-report-list-url="<?php echo e(route('admin_panel.admin.callingcrm.report')); ?>">
    <div class="user-report-heading">
        <button class="user-report-back" type="button" onclick="window.location='<?php echo e(route('admin_panel.admin.callingcrm.report')); ?>'" aria-label="Back to reports">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><polyline points="15 18 9 12 15 6"/></svg>
        </button>
        <h3>Follow-Up Report</h3>
    </div>

    <div class="user-report-toolbar">
        <div class="user-report-filters">
            <div class="report-filter" data-filter-menu="status">
                <button class="report-filter-btn active" type="button" data-filter-toggle>
                    <span data-status-label>All Statuses</span>
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"/></svg>
                </button>
                <div class="report-filter-menu">
                    <div class="filter-menu-title">Choose Status</div>
                    <div class="date-range-options">
                        <label class="date-range-option">
                            <input type="radio" name="follow_up_status" value="" checked>
                            <span>All</span>
                        </label>
                        <label class="date-range-option">
                            <input type="radio" name="follow_up_status" value="pending">
                            <span>Pending</span>
                        </label>
                        <label class="date-range-option">
                            <input type="radio" name="follow_up_status" value="completed">
                            <span>Completed</span>
                        </label>
                        <label class="date-range-option">
                            <input type="radio" name="follow_up_status" value="missed">
                            <span>Missed</span>
                        </label>
                    </div>
                    <div class="filter-menu-footer">
                        <button class="filter-apply-btn" type="button" data-status-apply>Apply</button>
                    </div>
                </div>
            </div>
            
            <div class="report-filter" data-filter-menu="users">
                <button class="report-filter-btn" type="button" data-filter-toggle>
                    <span data-user-label>Users</span>
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"/></svg>
                </button>
                <div class="report-filter-menu multi-menu">
                    <div class="filter-menu-title">Choose Users</div>
                    <input class="filter-search" type="search" placeholder="Search" data-filter-search="users">
                    <div class="filter-options" data-user-options></div>
                    <div class="filter-menu-footer">
                        <button class="filter-apply-btn" type="button" data-filter-apply>Apply</button>
                    </div>
                </div>
            </div>

            <div class="report-filter" data-filter-menu="campaigns">
                <button class="report-filter-btn" type="button" data-filter-toggle>
                    <span data-campaign-label>Campaigns</span>
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"/></svg>
                </button>
                <div class="report-filter-menu multi-menu">
                    <div class="filter-menu-title">Choose Campaigns</div>
                    <input class="filter-search" type="search" placeholder="Search" data-filter-search="campaigns">
                    <div class="filter-options" data-campaign-options></div>
                    <div class="filter-menu-footer">
                        <button class="filter-apply-btn" type="button" data-filter-apply>Apply</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="user-report-table-card">
        <table id="followUpReportTable" class="user-report-table">
            <thead>
                <tr>
                    <th>Lead Name</th>
                    <th>User</th>
                    <th>Campaign</th>
                    <th>Scheduled At</th>
                    <th>Status</th>
                    <th>Note</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
        
        <div class="pagination-controls" style="padding: 15px; display: flex; justify-content: space-between; align-items: center;">
            <div class="pagination-info" data-pagination-info></div>
            <div class="pagination-buttons" data-pagination-buttons></div>
        </div>
    </div>
</main>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script src="<?php echo e(asset('js/crm/pages/follow-up-report.js')); ?>"></script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('admin_panel.layout.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH E:\website-project\Amplchat\CMS\resources\views\admin_panel\callingcrm\follow-up-report.blade.php ENDPATH**/ ?>
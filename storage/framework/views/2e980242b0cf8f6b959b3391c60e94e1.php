<?php $__env->startSection('title', 'Calling CRM User Stage Report'); ?>
<?php $__env->startPush('styles'); ?>
<link rel="stylesheet" href="<?php echo e(asset('css/crm/calling-crm.css')); ?>">
<?php $__env->stopPush(); ?>

<?php $__env->startSection('main-content'); ?>
<div class="calling-crm-canvas calling-crm-user-report calling-crm-user-stage-report" data-report-endpoint="reports/user-stage">
<main class="crm-page-main" data-report-list-url="<?php echo e(route('admin_panel.admin.callingcrm.report')); ?>">
    <div class="user-report-heading">
        <button class="user-report-back" type="button" data-report-back aria-label="Back to reports">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><polyline points="15 18 9 12 15 6"/></svg>
        </button>
        <h3>User Stage Report</h3>
    </div>

    <div class="user-report-toolbar">
        <div class="user-report-filters">
            <div class="report-filter" data-filter-menu="date">
                <button class="report-filter-btn active" type="button" data-filter-toggle>
                    <span data-date-label>Last 30 days</span>
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"/></svg>
                </button>
                <div class="report-filter-menu date-menu">
                    <div class="filter-menu-title">Choose Creation Date</div>
                    <div class="date-range-options">
                        <label class="date-range-option">
                            <input type="radio" name="user_stage_report_date_range" value="today" data-date-range-option>
                            <span>Today</span>
                        </label>
                        <label class="date-range-option">
                            <input type="radio" name="user_stage_report_date_range" value="yesterday" data-date-range-option>
                            <span>Yesterday</span>
                        </label>
                        <label class="date-range-option">
                            <input type="radio" name="user_stage_report_date_range" value="last7" data-date-range-option>
                            <span>Last 7 days</span>
                        </label>
                        <label class="date-range-option">
                            <input type="radio" name="user_stage_report_date_range" value="last30" data-date-range-option checked>
                            <span>Last 30 days</span>
                        </label>
                        <label class="date-range-option">
                            <input type="radio" name="user_stage_report_date_range" value="month" data-date-range-option>
                            <span>This Month</span>
                        </label>
                        <label class="date-range-option">
                            <input type="radio" name="user_stage_report_date_range" value="custom" data-date-range-option>
                            <span>Custom Range</span>
                        </label>
                    </div>
                    <div class="custom-date-range" data-custom-date-range hidden>
                        <input type="date" data-custom-date-from aria-label="From date">
                        <input type="date" data-custom-date-to aria-label="To date">
                    </div>
                    <div class="filter-menu-footer">
                        <button class="filter-apply-btn" type="button" data-date-apply>Apply</button>
                    </div>
                </div>
            </div>

            <div class="report-filter" data-filter-menu="pipelines">
                <button class="report-filter-btn" type="button" data-filter-toggle>
                    <span data-pipeline-label>Pipeline</span>
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"/></svg>
                </button>
                <div class="report-filter-menu multi-menu">
                    <div class="filter-menu-title">Choose Pipeline</div>
                    <input class="filter-search" type="search" placeholder="Search" data-filter-search="pipelines">
                    <div class="filter-options" data-pipeline-options></div>
                    <div class="filter-menu-footer">
                        <button class="filter-apply-btn" type="button" data-filter-apply>Apply</button>
                    </div>
                </div>
            </div>

            <div class="report-filter" data-filter-menu="campaigns">
                <button class="report-filter-btn" type="button" data-filter-toggle>
                    <span data-campaign-label>Campaign</span>
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"/></svg>
                </button>
                <div class="report-filter-menu multi-menu">
                    <div class="filter-menu-title">Choose Campaign</div>
                    <input class="filter-search" type="search" placeholder="Search" data-filter-search="campaigns">
                    <div class="filter-options" data-campaign-options></div>
                    <div class="filter-menu-footer">
                        <button class="filter-apply-btn" type="button" data-filter-apply>Apply</button>
                    </div>
                </div>
            </div>

            <div class="report-filter" data-filter-menu="users">
                <button class="report-filter-btn" type="button" data-filter-toggle>
                    <span data-user-label>User</span>
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"/></svg>
                </button>
                <div class="report-filter-menu multi-menu">
                    <div class="filter-menu-title">Choose User</div>
                    <input class="filter-search" type="search" placeholder="Search" data-filter-search="users">
                    <div class="filter-options" data-user-options></div>
                    <div class="filter-menu-footer">
                        <button class="filter-apply-btn" type="button" data-filter-apply>Apply</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="user-report-table-card">
        <table id="userStageReportDataTable" class="user-report-table">
            <thead>
                <tr data-stage-report-head>
                    <th>No.</th>
                    <th>User Name</th>
                    <th>Reporting Manager</th>
                    <th>Date</th>
                    <th>Conversion %</th>
                    <th>Total Assigned Leads</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
</main>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script src="<?php echo e(asset('js/crm/pages/user-stage-report.js')); ?>"></script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('admin_panel.layout.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH E:\website-project\Amplchat\CMS\resources\views\admin_panel\callingcrm\user-stage-report.blade.php ENDPATH**/ ?>
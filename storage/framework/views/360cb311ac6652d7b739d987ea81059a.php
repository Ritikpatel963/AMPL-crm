<?php $__env->startSection('title', 'Custom Contact Property'); ?>
<?php $__env->startPush('styles'); ?>
<link rel="stylesheet" href="<?php echo e(asset('css/crm/calling-crm.css')); ?>">
<?php $__env->stopPush(); ?>

<?php $__env->startSection('main-content'); ?>
<div class="ccp-page">
  <div class="ccp-heading">Settings</div>

  <div class="ccp-tabs">
    <button class="ccp-tab">Users</button>
    <button class="ccp-tab">Preference</button>
    <button class="ccp-tab">Accessibility</button>
    <button class="ccp-tab">Pipelines</button>
    <button class="ccp-tab">Profile</button>
    <button class="ccp-tab">Roles and Permission</button>
    <button class="ccp-tab">Automatic Report</button>
    <button class="ccp-tab">Manage Columns</button>
    <button class="ccp-tab">Retry Setting</button>
    <button class="ccp-tab">Lead Priority</button>
    <button class="ccp-tab active">Custom Contact Property</button>
    <button class="ccp-tab">Notification</button>
  </div>

  <div class="ccp-wrapper">
    <div class="ccp-top">
      <div>
        <div class="ccp-title">Custom Contact Property</div>
        <div class="ccp-description">Create and customize lead properties that fit your business needs. Easily search, filter, and manage leads using criteria that matter to you.</div>
      </div>
    </div>

    <div class="ccp-table-wrap">
      <table class="ccp-table">
        <thead>
          <tr>
            <th>No.</th>
            <th>Property Name</th>
            <th>Data Type</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
          <?php $__currentLoopData = [
            ['Company Name', 'text'],
            ['Address Line 1', 'text'],
            ['Address Line 2', 'text'],
            ['Town/City', 'text'],
            ['State', 'text'],
            ['Pincode', 'number'],
            ['GST', 'text'],
          ]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $property): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <tr>
              <td><?php echo e($index + 1); ?></td>
              <td class="ccp-property-name"><?php echo e($property[0]); ?></td>
              <td><?php echo e($property[1]); ?></td>
              <td>
                <div class="ccp-actions">
                  <span class="ccp-toggle"><span class="ccp-toggle-dot"></span></span>
                  <button class="ccp-icon-btn" type="button" aria-label="Edit">
                    <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 20h9"/><path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L7 19l-4 1 1-4 12.5-12.5z"/></svg>
                  </button>
                  <button class="ccp-icon-btn" type="button" aria-label="Delete">
                    <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M3 6h18"/><path d="M8 6V4h8v2"/><path d="M19 6l-1 14H6L5 6"/></svg>
                  </button>
                </div>
              </td>
            </tr>
          <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </tbody>
      </table>
    </div>

    <div class="ccp-info">
      <span class="ccp-info-dot">i</span>
      <span>You have added 7/40 custom contact property.</span>
    </div>

    <div class="ccp-add-wrap">
      <button class="ccp-add-btn" type="button" data-property-open>
        <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2.4" viewBox="0 0 24 24"><path d="M12 5v14"/><path d="M5 12h14"/></svg>
        Add New Property
      </button>
    </div>
  </div>

  <div class="ccp-modal-backdrop" data-property-modal>
    <div class="ccp-modal" role="dialog" aria-modal="true" aria-labelledby="addCustomPropertyTitle">
      <div class="ccp-modal-head">
        <div class="ccp-modal-title" id="addCustomPropertyTitle">Add Custom Property</div>
        <button type="button" class="ccp-modal-close" data-property-close aria-label="Close">&times;</button>
      </div>
      <div class="ccp-modal-form">
        <div class="ccp-form-group">
          <label class="ccp-form-label">Property Name</label>
          <input class="ccp-input" maxlength="60" placeholder="Enter">
          <div class="ccp-counter">0/60</div>
        </div>
        <div class="ccp-form-group">
          <label class="ccp-form-label">Data Type</label>
          <div class="ccp-select">
            <span class="ccp-select-placeholder">Select</span>
            <svg width="18" height="18" fill="currentColor" viewBox="0 0 20 20"><path d="M5.5 7.5 10 12l4.5-4.5H5.5z"/></svg>
          </div>
        </div>
      </div>
      <div class="ccp-modal-actions">
        <button type="button" class="ccp-save-btn" data-property-close>Save</button>
      </div>
    </div>
  </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script src="<?php echo e(asset('js/crm/pages/contact-properties.js')); ?>"></script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('admin_panel.layout.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH E:\website-project\Amplchat\CMS\resources\views/admin_panel/callingcrm/contact-properties.blade.php ENDPATH**/ ?>
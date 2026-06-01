<div class="card border rounded-3 mb-4">
    <input type="hidden" name="attributes_json" id="attributesJson">
    <input type="hidden" name="variations_json" id="variationsJson">

    <div class="card-header bg-light">
        <ul class="nav nav-pills" id="productBuilderTabs">
            <li class="nav-item">
                <button type="button" class="nav-link active" data-builder-tab="attributes">
                    <i class="bi bi-list-check me-1"></i> Attributes
                </button>
            </li>
            <li class="nav-item">
                <button type="button" class="nav-link" data-builder-tab="variations">
                    <i class="bi bi-diagram-3 me-1"></i> Variations
                    <span class="badge bg-success ms-1" id="variationNavCount">0</span>
                </button>
            </li>
        </ul>
    </div>

    <div class="card-body">
        <div class="builder-panel active" id="attributesPanel">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h6 class="fw-bold mb-1">Product Attributes</h6>
                    <small class="text-muted">Select from global attributes created in Product Management > Attributes.</small>
                </div>
                <a href="<?php echo e(route('admin_panel.admin.product_attributes.index')); ?>" target="_blank" class="btn btn-outline-primary btn-sm">
                    Manage Attributes
                </a>
            </div>

            <div class="row g-3 align-items-end bg-light rounded p-3 mb-3">
                <div class="col-md-8">
                    <label class="form-label fw-semibold">Attribute</label>
                    <select class="form-select" id="globalAttributeSelect">
                        <option value="">Select Attribute</option>
                        <?php $__currentLoopData = $productAttributes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $attribute): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($attribute->id); ?>"><?php echo e($attribute->name); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <button type="button" class="btn btn-success w-100" id="addGlobalAttributeBtn">
                        <i class="bi bi-plus-circle me-1"></i>Add To Product
                    </button>
                </div>
            </div>

            <div id="attributesWrap"></div>
        </div>

        <div class="builder-panel d-none" id="variationsPanel">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h6 class="fw-bold mb-1">Variations</h6>
                    <small class="text-muted">Generate variations from selected attribute values.</small>
                </div>
                <button type="button" class="btn btn-success" id="generateVariationsBtn">
                    Create variations from all attributes
                </button>
            </div>

            <div id="variationsWrap"></div>
            <div id="emptyVariations" class="text-center text-muted border rounded py-4">
                Add attributes and generate variations.
            </div>
        </div>
    </div>
</div>
<?php /**PATH E:\website-project\Amplchat\CMS\resources\views/admin_panel/product/partials/product_variations_builder.blade.php ENDPATH**/ ?>
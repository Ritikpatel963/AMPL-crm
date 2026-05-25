<?php

use Illuminate\Support\Facades\Route;
use App\Models\User;
use App\Http\Controllers\AdminAuthController;
use App\Models\AgentCustomerAssignment;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ProductManageController;
use App\Http\Controllers\AdminUserController;
use App\Http\Controllers\ShippingMethodController;
use App\Http\Controllers\StockController;
use App\Http\Controllers\Frontend\ProductController;
use App\Http\Controllers\Frontend\OrderController;
use App\Http\Controllers\AdminVendorController;
use App\Http\Controllers\AdminVendorCategoryController;
use App\Http\Controllers\AdminCustomerController;

//order route witout user login only for testing 
Route::get('/front', [ProductController::class, 'index'])->name('shop.index');
Route::get('/product/{id}', [ProductController::class, 'show'])->name('shop.show');
Route::post('/order', [OrderController::class, 'store'])->name('shop.order');
Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
// Route::post('/orders/{id}/status', [OrderController::class, 'updateStatus'])->name('orders.updateStatus');

// chatsystem routes
// 🟩 User Dashboard
Route::get('/dashboard', function () {
    $user = Auth::user(); // current logged-in user

    if ($user->role === 'subadmin') {
        // Subadmin sees other users (limit to 100 and select only required fields)
        $users = User::where('id', '!=', $user->id)
            ->select(['id', 'name', 'email'])
            ->take(100)
            ->get();
    } elseif ($user->role === 'agent') {
        // Agent sees customers assigned to them (limit to 100 and select only required fields)
        $users = AgentCustomerAssignment::where('agent_id', $user->id)
            ->with('customer:id,name,email')
            ->take(100)
            ->get()
            ->pluck('customer'); // extract customer models only

    } elseif ($user->role === 'customer') {
        // Customer sees only their assigned agent
        $assignment = AgentCustomerAssignment::where('customer_id', $user->id)
            ->with('agent:id,name,email')
            ->first();
        $users = collect();
        if ($assignment && $assignment->agent) {
            $users->push($assignment->agent);
        }
    } else {
        // Default fallback (no users)
        $users = collect();
    }

    return view('dashboard', [
        'users' => $users
    ]);
})->middleware(['auth', 'verified'])->name('dashboard');

// Admin login stays separate from the default user login routes in auth.php.
Route::middleware('guest:admin')->group(function () {
    Route::get('/', [AdminAuthController::class, 'showLoginForm']);
    Route::get('/admin', [AdminAuthController::class, 'showLoginForm'])->name('admin_panel.admin.login');
    Route::post('/admin/send-otp', [AdminAuthController::class, 'sendOtp'])->name('admin_panel.admin.send.otp');
    Route::post('/admin/login', [AdminAuthController::class, 'login'])->name('admin_panel.admin.login.submit');
});

// Admin protected routes (still under /admin prefix)
Route::prefix('admin_panel/admin')->name('admin_panel.admin.')->middleware(['auth:admin'])->group(function () {
    Route::get('/profile', [AdminAuthController::class, 'editProfile'])->name('edit.profile');
    Route::post('/profile', [AdminAuthController::class, 'updateProfile'])->name('update.profile');
    Route::get('/admins', [AdminAuthController::class, 'admins'])->name('admins.index');
    Route::post('/admins', [AdminAuthController::class, 'storeAdmin'])->name('admins.store');
    Route::put('/admins/{admin}', [AdminAuthController::class, 'updateAdmin'])->name('admins.update');
    Route::delete('/admins/{admin}', [AdminAuthController::class, 'destroyAdmin'])->name('admins.destroy');
    Route::get('/index', [AdminAuthController::class, 'dashboard'])->name('index');
    Route::post('/logout', [AdminAuthController::class, 'logout'])->name('logout');
    //categories
    Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');
    Route::post('/categories/store', [CategoryController::class, 'store'])->name('categories.store');
    Route::get('/categories/{id}/edit', [CategoryController::class, 'edit'])->name('categories.edit');
    Route::post('/categories/{id}/update', [CategoryController::class, 'update'])->name('categories.update');
    Route::delete('/categories/{id}/delete', [CategoryController::class, 'destroy'])->name('categories.destroy');
    Route::get('/products', [ProductManageController::class, 'index'])->name('products.index');
    //products
    Route::get('/products/create', [ProductManageController::class, 'create'])->name('products.create');
    Route::post('/products/store', [ProductManageController::class, 'store'])->name('products.store');
    Route::get('/products/edit/{id}', [ProductManageController::class, 'edit'])->name('products.edit');
    Route::post('/products/update/{id}', [ProductManageController::class, 'update'])->name('products.update');
    Route::delete('/products/delete/{id}', [ProductManageController::class, 'destroy'])->name('products.destroy');
    //users(role)
    Route::get('/users', [AdminUserController::class, 'index'])->name('users.index');
    Route::post('/users/store', [AdminUserController::class, 'store'])->name('users.store');
    Route::post('/users/update', [AdminUserController::class, 'update'])->name('users.update');
    Route::post('/users/delete', [AdminUserController::class, 'destroy'])->name('users.destroy');
    //shipping
    Route::get('/shipping', [ShippingMethodController::class, 'index'])->name('shipping.index');
    Route::post('/shipping/store', [ShippingMethodController::class, 'store'])->name('shipping.store');
    Route::post('shipping/update/{id}', [ShippingMethodController::class, 'update'])->name('shipping.update');
    Route::delete('/shipping/delete/{id}', [ShippingMethodController::class, 'destroy'])->name('shipping.delete');
    //// Stock Routes
    Route::get('/stocks', [StockController::class, 'index'])->name('stocks.index');
    Route::post('/stocks/store', [StockController::class, 'store'])->name('stocks.store');

    //order route
    Route::post('/orders/{id}/status', [OrderController::class, 'updateStatus'])->name('orders.updateStatus');

    // Calling CRM
    Route::get('/calling-crm', function () {
        return view('admin_panel.callingcrm.dashboard');
    })->name('callingcrm.dashboard');
    Route::get('/calling-crm/contact', function () {
        return view('admin_panel.callingcrm.contact');
    })->name('callingcrm.contact');
    Route::get('/calling-crm/contact/properties', function () {
        return view('admin_panel.callingcrm.contact-properties');
    })->name('callingcrm.contact.properties');
    Route::get('/calling-crm/pipeline', function () {
        return view('admin_panel.callingcrm.pipeline');
    })->name('callingcrm.pipeline');
    Route::get('/calling-crm/report', function () {
        return view('admin_panel.callingcrm.report');
    })->name('callingcrm.report');
    Route::get('/calling-crm/report/user', function () {
        return view('admin_panel.callingcrm.user-report');
    })->name('callingcrm.report.user');
    Route::get('/calling-crm/report/login', function () {
        return view('admin_panel.callingcrm.login-report');
    })->name('callingcrm.report.login');
    Route::get('/calling-crm/trends', function () {
        return view('admin_panel.callingcrm.trends');
    })->name('callingcrm.trends');
    Route::get('/calling-crm/settings', function () {
        return view('admin_panel.callingcrm.settings');
    })->name('callingcrm.settings');

    Route::prefix('/api/calling-crm')->group(function () {
        Route::get('/settings/bootstrap', [\App\Http\Controllers\Api\CallingCrm\SettingsController::class, 'bootstrap']);
        Route::get('/settings/profile', [\App\Http\Controllers\Api\CallingCrm\SettingsController::class, 'profile']);
        Route::put('/settings/profile', [\App\Http\Controllers\Api\CallingCrm\SettingsController::class, 'updateProfile']);
        Route::get('/settings/users', [\App\Http\Controllers\Api\CallingCrm\SettingsController::class, 'users']);
        Route::post('/settings/users', [\App\Http\Controllers\Api\CallingCrm\SettingsController::class, 'storeUsers']);
        Route::put('/settings/users/{user}', [\App\Http\Controllers\Api\CallingCrm\SettingsController::class, 'updateUser']);
        Route::patch('/settings/users/{user}/status', [\App\Http\Controllers\Api\CallingCrm\SettingsController::class, 'updateUserStatus']);
        Route::patch('/settings/users/{user}/password', [\App\Http\Controllers\Api\CallingCrm\SettingsController::class, 'updateUserPassword']);
        Route::delete('/settings/users/{user}', [\App\Http\Controllers\Api\CallingCrm\SettingsController::class, 'destroyUser']);
        Route::get('/settings/users/{user}/campaigns', [\App\Http\Controllers\Api\CallingCrm\SettingsController::class, 'userCampaigns']);
        Route::get('/settings/users/{user}/reassign-campaigns', [\App\Http\Controllers\Api\CallingCrm\SettingsController::class, 'userReassignCampaigns']);
        Route::get('/settings/retry-reasons', [\App\Http\Controllers\Api\CallingCrm\SettingsController::class, 'retryReasons']);
        Route::post('/settings/retry-reasons', [\App\Http\Controllers\Api\CallingCrm\SettingsController::class, 'storeRetryReason']);
        Route::put('/settings/retry-reasons/{retryReason}', [\App\Http\Controllers\Api\CallingCrm\SettingsController::class, 'updateRetryReason']);
        Route::delete('/settings/retry-reasons/{retryReason}', [\App\Http\Controllers\Api\CallingCrm\SettingsController::class, 'destroyRetryReason']);
        Route::put('/settings/retry-reasons/{retryReason}/rule', [\App\Http\Controllers\Api\CallingCrm\SettingsController::class, 'updateRetryRule']);
        Route::get('/settings/lead-priority', [\App\Http\Controllers\Api\CallingCrm\SettingsController::class, 'leadPriorityRules']);
        Route::put('/settings/lead-priority', [\App\Http\Controllers\Api\CallingCrm\SettingsController::class, 'updateLeadPriority']);

        Route::get('/contact-properties', [\App\Http\Controllers\Api\CallingCrm\SettingsController::class, 'contactProperties']);
        Route::post('/contact-properties', [\App\Http\Controllers\Api\CallingCrm\SettingsController::class, 'storeContactProperty']);
        Route::put('/contact-properties/{property}', [\App\Http\Controllers\Api\CallingCrm\SettingsController::class, 'updateContactProperty']);
        Route::patch('/contact-properties/{property}/toggle', [\App\Http\Controllers\Api\CallingCrm\SettingsController::class, 'toggleContactProperty']);
        Route::delete('/contact-properties/{property}', [\App\Http\Controllers\Api\CallingCrm\SettingsController::class, 'destroyContactProperty']);

        Route::apiResource('pipelines', \App\Http\Controllers\Api\CallingCrm\PipelineController::class);
        Route::post('/pipelines/{pipeline}/stages', [\App\Http\Controllers\Api\CallingCrm\PipelineController::class, 'storeStage']);
        Route::post('/pipelines/{pipeline}/stages/reorder', [\App\Http\Controllers\Api\CallingCrm\PipelineController::class, 'reorderStages']);
        Route::put('/stages/{stage}', [\App\Http\Controllers\Api\CallingCrm\PipelineController::class, 'updateStage']);
        Route::delete('/stages/{stage}', [\App\Http\Controllers\Api\CallingCrm\PipelineController::class, 'destroyStage']);
        Route::put('/stages/{stage}/transitions', [\App\Http\Controllers\Api\CallingCrm\PipelineController::class, 'updateStageTransitions']);
        Route::post('/stages/{stage}/tags', [\App\Http\Controllers\Api\CallingCrm\PipelineController::class, 'storeTag']);
        Route::put('/stage-tags/{tag}', [\App\Http\Controllers\Api\CallingCrm\PipelineController::class, 'updateTag']);
        Route::delete('/stage-tags/{tag}', [\App\Http\Controllers\Api\CallingCrm\PipelineController::class, 'destroyTag']);

        Route::get('/teams', [\App\Http\Controllers\Api\CallingCrm\TeamsController::class, 'index']);
        Route::post('/teams', [\App\Http\Controllers\Api\CallingCrm\TeamsController::class, 'store']);
        Route::put('/teams/{team}', [\App\Http\Controllers\Api\CallingCrm\TeamsController::class, 'update']);
        Route::post('/teams/{team}/members', [\App\Http\Controllers\Api\CallingCrm\TeamsController::class, 'addMembers']);
        Route::delete('/teams/{team}/members/{user}', [\App\Http\Controllers\Api\CallingCrm\TeamsController::class, 'removeMember']);

        Route::apiResource('campaigns', \App\Http\Controllers\Api\CallingCrm\CampaignController::class);
        Route::patch('/campaigns/{campaign}/status', [\App\Http\Controllers\Api\CallingCrm\CampaignController::class, 'updateStatus']);
        Route::patch('/campaigns/{campaign}/priority', [\App\Http\Controllers\Api\CallingCrm\CampaignController::class, 'updatePriority']);
        Route::patch('/campaigns/{campaign}/pin', [\App\Http\Controllers\Api\CallingCrm\CampaignController::class, 'pin']);
        Route::get('/campaigns/{campaign}/summary', [\App\Http\Controllers\Api\CallingCrm\CampaignController::class, 'summary']);
        Route::get('/campaigns/{campaign}/lead-funnel', [\App\Http\Controllers\Api\CallingCrm\CampaignController::class, 'leadFunnel']);
        Route::get('/campaigns/{campaign}/tags-summary', [\App\Http\Controllers\Api\CallingCrm\CampaignController::class, 'tagsSummary']);
        Route::post('/campaigns/{campaign}/agents', [\App\Http\Controllers\Api\CallingCrm\CampaignController::class, 'addAgents']);
        Route::delete('/campaigns/{campaign}/agents/{user}', [\App\Http\Controllers\Api\CallingCrm\CampaignController::class, 'removeAgent']);
        Route::get('/campaigns/{campaign}/assignment-rules', [\App\Http\Controllers\Api\CallingCrm\CampaignController::class, 'assignmentRules']);
        Route::post('/campaigns/{campaign}/assignment-rules', [\App\Http\Controllers\Api\CallingCrm\CampaignController::class, 'storeAssignmentRule']);
        Route::put('/campaigns/{campaign}/assignment-rules/{rule}', [\App\Http\Controllers\Api\CallingCrm\CampaignController::class, 'updateAssignmentRule']);
        Route::delete('/campaigns/{campaign}/assignment-rules/{rule}', [\App\Http\Controllers\Api\CallingCrm\CampaignController::class, 'destroyAssignmentRule']);

        Route::get('/leads', [\App\Http\Controllers\Api\CallingCrm\LeadController::class, 'index']);
        Route::post('/leads', [\App\Http\Controllers\Api\CallingCrm\LeadController::class, 'store']);
        Route::get('/leads/{lead}', [\App\Http\Controllers\Api\CallingCrm\LeadController::class, 'show']);
        Route::put('/leads/{lead}', [\App\Http\Controllers\Api\CallingCrm\LeadController::class, 'update']);
        Route::delete('/leads/{lead}', [\App\Http\Controllers\Api\CallingCrm\LeadController::class, 'destroy']);
        Route::get('/leads/{lead}/timeline', [\App\Http\Controllers\Api\CallingCrm\LeadController::class, 'timeline']);
        Route::get('/leads/{lead}/history', [\App\Http\Controllers\Api\CallingCrm\LeadController::class, 'history']);
        Route::post('/leads/{lead}/notes', [\App\Http\Controllers\Api\CallingCrm\LeadController::class, 'storeNote']);
        Route::post('/leads/{lead}/phone-numbers', [\App\Http\Controllers\Api\CallingCrm\LeadController::class, 'storePhoneNumber']);
        Route::post('/leads/reassign', [\App\Http\Controllers\Api\CallingCrm\LeadController::class, 'reassign']);
        Route::post('/leads/claim-next', [\App\Http\Controllers\Api\CallingCrm\LeadController::class, 'claimNext']);
        Route::post('/leads/bulk/update', [\App\Http\Controllers\Api\CallingCrm\LeadController::class, 'bulkUpdate']);
        Route::post('/leads/bulk/delete', [\App\Http\Controllers\Api\CallingCrm\LeadController::class, 'bulkDelete']);
        Route::post('/leads/bulk/move', [\App\Http\Controllers\Api\CallingCrm\LeadController::class, 'bulkMove']);
        Route::post('/leads/bulk/copy', [\App\Http\Controllers\Api\CallingCrm\LeadController::class, 'bulkCopy']);
        Route::post('/leads/bulk/close', [\App\Http\Controllers\Api\CallingCrm\LeadController::class, 'bulkClose']);

        Route::get('/calls', [\App\Http\Controllers\Api\CallingCrm\CallController::class, 'index']);
        Route::post('/calls/start', [\App\Http\Controllers\Api\CallingCrm\CallController::class, 'start']);
        Route::post('/calls/webhook', [\App\Http\Controllers\Api\CallingCrm\CallController::class, 'webhook']);
        Route::get('/calls/{call}', [\App\Http\Controllers\Api\CallingCrm\CallController::class, 'show']);
        Route::patch('/calls/{call}', [\App\Http\Controllers\Api\CallingCrm\CallController::class, 'update']);
        Route::get('/campaigns/{campaign}/call-logs', [\App\Http\Controllers\Api\CallingCrm\CallController::class, 'campaignCallLogs']);
        Route::get('/users/{user}/call-logs', [\App\Http\Controllers\Api\CallingCrm\CallController::class, 'userCallLogs']);

        Route::apiResource('dispositions', \App\Http\Controllers\Api\CallingCrm\DispositionController::class)->except(['show']);
        Route::post('/leads/{lead}/dispose', [\App\Http\Controllers\Api\CallingCrm\DispositionController::class, 'disposeLead']);
        Route::apiResource('follow-ups', \App\Http\Controllers\Api\CallingCrm\FollowUpController::class)->except(['show']);
        Route::patch('/follow-ups/{followUp}/complete', [\App\Http\Controllers\Api\CallingCrm\FollowUpController::class, 'complete']);

        Route::get('/imports', [\App\Http\Controllers\Api\CallingCrm\ImportController::class, 'index']);
        Route::post('/campaigns/{campaign}/imports', [\App\Http\Controllers\Api\CallingCrm\ImportController::class, 'store']);
        Route::get('/imports/{import}', [\App\Http\Controllers\Api\CallingCrm\ImportController::class, 'show']);
        Route::get('/imports/{import}/rows', [\App\Http\Controllers\Api\CallingCrm\ImportController::class, 'rows']);
        Route::delete('/imports/{import}', [\App\Http\Controllers\Api\CallingCrm\ImportController::class, 'destroy']);
        Route::get('/imports/sample', [\App\Http\Controllers\Api\CallingCrm\ImportController::class, 'sample']);

        Route::get('/dashboard/overview', [\App\Http\Controllers\Api\CallingCrm\ReportController::class, 'dashboardOverview']);
        Route::get('/dashboard/agent-activity', [\App\Http\Controllers\Api\CallingCrm\ReportController::class, 'agentActivity']);
        Route::get('/dashboard/leads-by-stage', [\App\Http\Controllers\Api\CallingCrm\ReportController::class, 'leadsByStage']);
        Route::get('/reports/catalog', [\App\Http\Controllers\Api\CallingCrm\ReportController::class, 'catalog']);
        Route::get('/reports/user-call', [\App\Http\Controllers\Api\CallingCrm\ReportController::class, 'userCallReport']);
        Route::get('/reports/lead-disposition', [\App\Http\Controllers\Api\CallingCrm\ReportController::class, 'leadDispositionReport']);
        Route::get('/reports/follow-ups', [\App\Http\Controllers\Api\CallingCrm\ReportController::class, 'followUpReport']);
        Route::get('/reports/campaign', [\App\Http\Controllers\Api\CallingCrm\ReportController::class, 'campaignReport']);
        Route::get('/reports/login', [\App\Http\Controllers\Api\CallingCrm\ReportController::class, 'loginReport']);
        Route::get('/reports/hourly', [\App\Http\Controllers\Api\CallingCrm\ReportController::class, 'hourlyReport']);
        Route::get('/reports/day', [\App\Http\Controllers\Api\CallingCrm\ReportController::class, 'dayReport']);
        Route::post('/reports/exports', [\App\Http\Controllers\Api\CallingCrm\ReportController::class, 'queueExport']);
        Route::get('/reports/exports/{export}', [\App\Http\Controllers\Api\CallingCrm\ReportController::class, 'showExport']);
        Route::get('/trends/widgets', [\App\Http\Controllers\Api\CallingCrm\ReportController::class, 'trendWidgets']);
        Route::get('/trends/calls-vs-connected', [\App\Http\Controllers\Api\CallingCrm\ReportController::class, 'callsVsConnected']);
        Route::get('/trends/call-duration', [\App\Http\Controllers\Api\CallingCrm\ReportController::class, 'callDurationTrend']);
        Route::get('/trends/conversion-ratio', [\App\Http\Controllers\Api\CallingCrm\ReportController::class, 'conversionRatioTrend']);
        Route::get('/trends/leads-added', [\App\Http\Controllers\Api\CallingCrm\ReportController::class, 'leadsAddedTrend']);
        Route::get('/trends/lead-sources', [\App\Http\Controllers\Api\CallingCrm\ReportController::class, 'leadSources']);
        Route::get('/trends/lost-leads', [\App\Http\Controllers\Api\CallingCrm\ReportController::class, 'lostLeadsTrend']);
        Route::apiResource('saved-filters', \App\Http\Controllers\Api\CallingCrm\SavedFiltersController::class)->except(['show']);
        Route::post('/communications/sms', [\App\Http\Controllers\Api\CallingCrm\CommunicationsController::class, 'sendSms']);
        Route::post('/communications/email', [\App\Http\Controllers\Api\CallingCrm\CommunicationsController::class, 'sendEmail']);
        Route::post('/communications/whatsapp', [\App\Http\Controllers\Api\CallingCrm\CommunicationsController::class, 'sendWhatsApp']);
        Route::get('/communications', [\App\Http\Controllers\Api\CallingCrm\CommunicationsController::class, 'index']);
    });


    //kyc maneus
    // Route::get('/kyc/pending', function () {
    //     return view('admin_panel.kyc.pending');
    // })->name('kyc.pending');
    // Route::get('/kyc/approved', function () {
    //     return view('admin_panel.kyc.approved');
    // })->name('kyc.approved');
    // Route::get('/kyc/rejected', function () {
    //     return view('admin_panel.kyc.rejected');
    // })->name('kyc.rejected');

    Route::get('/kyc/pending',  [AdminVendorController::class, 'pending'])->name('kyc.pending');
    Route::get('/kyc/approved', [AdminVendorController::class, 'approved'])->name('kyc.approved');
    Route::get('/kyc/rejected', [AdminVendorController::class, 'rejected'])->name('kyc.rejected');
    Route::get('/kyc/{id}',     [AdminVendorController::class, 'show'])->name('kyc.show');
    Route::post('/kyc/{id}/approve', [AdminVendorController::class, 'approve'])->name('kyc.approve');
    Route::post('/kyc/{id}/reject',  [AdminVendorController::class, 'reject'])->name('kyc.reject');

    // Vendors management
    Route::get('/vendors',       [AdminVendorController::class, 'index'])->name('vendors.index');
    Route::get('/vendors/{id}',  [AdminVendorController::class, 'vendorsShow'])->name('vendors.show');
    Route::get('/vendors/{id}/products', [AdminVendorController::class, 'vendorProducts'])->name('vendors.products');

    // Vendor Categories management
    Route::get('/vendor-categories',                [AdminVendorCategoryController::class, 'index'])->name('vendor_categories.index');
    Route::get('/vendor-categories/create',        [AdminVendorCategoryController::class, 'create'])->name('vendor_categories.create');
    Route::post('/vendor-categories',               [AdminVendorCategoryController::class, 'store'])->name('vendor_categories.store');
    Route::get('/vendor-categories/{id}/edit',     [AdminVendorCategoryController::class, 'edit'])->name('vendor_categories.edit');
    Route::put('/vendor-categories/{id}',           [AdminVendorCategoryController::class, 'update'])->name('vendor_categories.update');
    Route::delete('/vendor-categories/{id}',        [AdminVendorCategoryController::class, 'destroy'])->name('vendor_categories.destroy');
 
    // customer menus
    Route::get('/customers/view', [AdminCustomerController::class, 'index'])->name('customers.view');
    Route::get('/customers/management', [AdminCustomerController::class, 'index'])->name('customers.customer_manage');
    Route::post('/customers', [AdminCustomerController::class, 'store'])->name('customers.store');
    Route::post('/customers/{customer}/assign', [AdminCustomerController::class, 'assign'])->name('customers.assign');
    Route::post('/customers/{customer}/approve', [AdminCustomerController::class, 'approve'])->name('customers.approve');
    Route::post('/customers/{customer}/reject', [AdminCustomerController::class, 'reject'])->name('customers.reject');
    Route::delete('/customers/{customer}', [AdminCustomerController::class, 'destroy'])->name('customers.destroy');
    Route::get('/payments/management', function () {
        return view('admin_panel.payments.payment_management');
    })->name('payments.payment_management');
    //

    // permission manues
    Route::prefix('permission')->group(function () {
        Route::get('/roles', [App\Http\Controllers\RoleController::class, 'index'])->name('role');
        Route::post('/roles/store', [App\Http\Controllers\RoleController::class, 'store'])->name('roles.store');
        Route::post('/roles/update/{id}', [App\Http\Controllers\RoleController::class, 'update'])->name('roles.update');
        Route::delete('/roles/delete/{id}', [App\Http\Controllers\RoleController::class, 'destroy'])->name('roles.destroy');

        // Permissions
        Route::get('/role_p', [PermissionController::class, 'index'])->name('permissions.index');
        Route::post('/assign', [PermissionController::class, 'store'])->name('permissions.assign');
        Route::get('/get-role-permissions/{id}', [PermissionController::class, 'getRolePermissions'])->name('permissions.get');
    });

    // 

    // chathistory manus
    Route::get('/chatlogs/history', function () {
        return view('admin_panel.chatlogs.logs');
    })->name('chatlogs.logs');

    //stock manus 
    Route::get('/stock/stocklist', function () {
        return view('admin_panel.stock.stock-list');
    })->name('stock.stock-list');

    Route::get('/stock/addstocklist', function () {
        return view('admin_panel.stock.add-update-stock');
    })->name('stock.add-update-stock');


    Route::get('/stock/stockhistory', function () {
        return view('admin_panel.stock.stock-history');
    })->name('stock.stock-history');
    //orders manus route
    Route::get('/orders', function () {
        return view('admin_panel.orders.index');
    })->name('orders');

    Route::get('/orders/details', function () {
        return view('admin_panel.orders.order_details');
    })->name('orders.order_details');
});


// 🟩 Chat Route (User Auth)
Route::get('/chat/{id}', function ($id) {
    return view('chat', [
        'id' => $id
    ]);
})->middleware(['auth', 'verified'])->name('chat');

// 🟩 Profile Page
Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

// 🟩 Auth scaffolding routes
require __DIR__ . '/auth.php';


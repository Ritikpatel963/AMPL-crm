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


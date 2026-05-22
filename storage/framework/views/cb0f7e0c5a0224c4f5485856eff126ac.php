<!doctype html>
<html class="no-js" lang="en" dir="ltr">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=Edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title><?php echo $__env->yieldContent('title', 'AMPL Chat admin'); ?></title>
    <link rel="icon" href="favicon.ico" type="image/x-icon">

    <!-- Plugin CSS -->
    <link rel="stylesheet" href="include/assets/plugin/datatables/responsive.dataTables.min.css">
    <link rel="stylesheet" href="include/assets/plugin/datatables/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"
        integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

    <!-- Project CSS -->
    <link rel="stylesheet" href="<?php echo e(asset('assets/css/ebazar.style.min.css')); ?>?v=1.0">

    <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/app.js']); ?>
    <?php echo $__env->yieldPushContent('styles'); ?>
</head>

<body>
    <div id="ebazar-layout" class="theme-blue">

        <!-- sidebar -->
        <div class="sidebar px-4 py-4 py-md-4 me-0">
            <div class="d-flex flex-column h-100">
                <a href="index.php" class="mb-0 brand-icon">
                    <span class="logo-icon">
                        <i class="bi bi-bag-check-fill fs-4"></i>
                    </span>
                    <span class="logo-text">Admin Panel</span>
                </a>
                <!-- Menu: main ul -->
                <?php
                    $isUserMenu = request()->routeIs('admin_panel.admin.users.*');
                    $isVendorMenu = request()->routeIs('admin_panel.admin.vendors.*') || request()->routeIs('admin_panel.admin.vendor_categories.*');
                    $isKycMenu = request()->routeIs('admin_panel.admin.kyc.*');
                    $isProductMenu = request()->routeIs('admin_panel.admin.products.*') || request()->routeIs('admin_panel.admin.categories.*');
                    $isShippingMenu = request()->routeIs('admin_panel.admin.shipping.*');
                    $isCustomerMenu = request()->routeIs('admin_panel.admin.customers.*');
                    $isPaymentMenu = request()->routeIs('admin_panel.admin.payments.*');
                    $isPermissionMenu = request()->routeIs('admin_panel.admin.role') || request()->routeIs('admin_panel.admin.permissions.*');
                    $isHistoryMenu = request()->routeIs('admin_panel.admin.chatlogs.*');
                    $isOrderMenu = request()->routeIs('admin_panel.admin.orders.*') || request()->routeIs('orders.*');
                    $isStockMenu = request()->routeIs('admin_panel.admin.stocks.*') || request()->routeIs('admin_panel.admin.stock.*');
                    $isCallingCrmMenu = request()->routeIs('admin_panel.admin.callingcrm.*');
                ?>
                <ul class="menu-list flex-grow-1 mt-3">
                    <li><a class="m-link active" href="<?php echo e(route('admin_panel.admin.index')); ?>"><i
                                class="icofont-home fs-5"></i> <span>Dashboard</span></a></li>
                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('Manage Users')): ?>
                        <li class="collapsed">
                            <a class="m-link" data-bs-toggle="collapse" data-bs-target="#menu-product_user" href="#">
                                <i class="icofont-users-alt-2 fs-5"></i> <span>User Management</span> <span
                                    class="arrow icofont-rounded-down ms-auto text-end fs-5"></span></a>
                            <!-- Menu: Sub menu ul -->
                            <ul class="sub-menu collapse<?php echo e($isUserMenu ? ' show' : ''); ?>" id="menu-product_user">
                                <li><a class="ms-link" href="<?php echo e(route('admin_panel.admin.users.index')); ?>">Users</a></li>
                            </ul>
                        </li>
                    <?php endif; ?>
                    <li class="collapsed">
                        <a class="m-link" data-bs-toggle="collapse" data-bs-target="#menu-vendor" href="#">
                            <i class="icofont-users fs-5"></i> <span>Vendor Management</span> <span
                                class="arrow icofont-rounded-down ms-auto text-end fs-5"></span></a>
                        <!-- Menu: Sub menu ul -->
                        <ul class="sub-menu collapse<?php echo e($isVendorMenu ? ' show' : ''); ?>" id="menu-vendor">
                            <li><a class="ms-link" href="<?php echo e(route('admin_panel.admin.vendors.index')); ?>">All Vendors</a></li>
                            <li><a class="ms-link" href="<?php echo e(route('admin_panel.admin.vendor_categories.index')); ?>">Vendor Categories</a></li>
                        </ul>
                    </li>
                    <li class="collapsed">
                        <a class="m-link" data-bs-toggle="collapse" data-bs-target="#menu-product_kyc" href="#">
                            <i class="icofont-file-alt fs-5"></i> <span>Kyc Management</span> <span
                                class="arrow icofont-rounded-down ms-auto text-end fs-5"></span></a>
                        <!-- Menu: Sub menu ul -->
                        <ul class="sub-menu collapse<?php echo e($isKycMenu ? ' show' : ''); ?>" id="menu-product_kyc">
                            <li><a class="ms-link" href="<?php echo e(route('admin_panel.admin.kyc.pending')); ?>">Pending Kyc</a>
                            </li>
                            <li><a class="ms-link" href="<?php echo e(route('admin_panel.admin.kyc.approved')); ?>">Approved Kyc</a>
                            </li>
                            <li><a class="ms-link" href="<?php echo e(route('admin_panel.admin.kyc.rejected')); ?>">Rejected Kyc</a>
                            </li>
                            
                        </ul>
                    </li>
                    <li class="collapsed">
                        <a class="m-link" data-bs-toggle="collapse" data-bs-target="#menu-product_p" href="#">
                            <i class="icofont-box  fs-5"></i> <span>Product Management</span> <span
                                class="arrow icofont-rounded-down ms-auto text-end fs-5"></span></a>
                        <!-- Menu: Sub menu ul -->
                        <ul class="sub-menu collapse<?php echo e($isProductMenu ? ' show' : ''); ?>" id="menu-product_p">
                            <li><a class="ms-link" href="<?php echo e(route('admin_panel.admin.products.index')); ?>">Products
                                    List</a>
                            </li>
                            <li><a class="ms-link" href="<?php echo e(route('admin_panel.admin.products.create')); ?>">Add
                                    Products</a></li>
                            <li><a class="ms-link" href="<?php echo e(route('admin_panel.admin.categories.index')); ?>">Manage
                                    Category</a>
                            </li>
                            
                        </ul>
                    </li>
                    <li class="collapsed">
                        <a class="m-link" data-bs-toggle="collapse" data-bs-target="#menu-product_ship" href="#">
                            <i class="icofont-truck-loaded fs-5"></i> <span>Shipping</span> <span
                                class="arrow icofont-rounded-down ms-auto text-end fs-5"></span></a>
                        <!-- Menu: Sub menu ul -->
                        <ul class="sub-menu collapse<?php echo e($isShippingMenu ? ' show' : ''); ?>" id="menu-product_ship">
                            <li><a class="ms-link" href="<?php echo e(route('admin_panel.admin.shipping.index')); ?>">Shipping</a>
                                
                        </ul>
                    </li>
                    <li class="collapsed">
                        <a class="m-link" data-bs-toggle="collapse" data-bs-target="#menu-product_cust" href="#">
                            <i class="icofont-user-alt-3 fs-5"></i> <span>Customers</span> <span
                                class="arrow icofont-rounded-down ms-auto text-end fs-5"></span></a>
                        <!-- Menu: Sub menu ul -->
                        <ul class="sub-menu collapse<?php echo e($isCustomerMenu ? ' show' : ''); ?>" id="menu-product_cust">
                            <li><a class="ms-link" href="<?php echo e(route('admin_panel.admin.customers.view')); ?>">Customer
                                    Profile</a></li>
                            <li><a class="ms-link"
                                    href="<?php echo e(route('admin_panel.admin.customers.customer_manage')); ?>">Manage
                                    Customer</a></li>
                            
                        </ul>
                    </li>
                    <li class="collapsed">
                        <a class="m-link" data-bs-toggle="collapse" data-bs-target="#menu-product_pay"
                            href="#">
                            <i class="icofont-credit-card fs-5"></i> <span>Payments</span> <span
                                class="arrow icofont-rounded-down ms-auto text-end fs-5"></span></a>
                        <!-- Menu: Sub menu ul -->
                        <ul class="sub-menu collapse<?php echo e($isPaymentMenu ? ' show' : ''); ?>" id="menu-product_pay">
                            <li><a class="ms-link"
                                    href="<?php echo e(route('admin_panel.admin.payments.payment_management')); ?>">Payments</a>
                            </li>
                            
                        </ul>
                    </li>
                    <li class="collapsed">
                        <a class="m-link" data-bs-toggle="collapse" data-bs-target="#menu-product_per"
                            href="#">
                            <i class="icofont-shield  fs-5"></i> <span>Permission</span> <span
                                class="arrow icofont-rounded-down ms-auto text-end fs-5"></span></a>
                        <!-- Menu: Sub menu ul -->
                        <ul class="sub-menu collapse<?php echo e($isPermissionMenu ? ' show' : ''); ?>" id="menu-product_per">
                            
                            <li><a class="ms-link" href="<?php echo e(route('admin_panel.admin.role')); ?>">Role</a>
                            </li>
                            <li><a class="ms-link" href="<?php echo e(route('admin_panel.admin.permissions.index')); ?>">Manage
                                    Permission</a></li>
                            
                        </ul>
                    </li>
                    <li class="collapsed">
                        <a class="m-link" data-bs-toggle="collapse" data-bs-target="#menu-product_chat"
                            href="#">
                            <i class="icofont-clock-time fs-5"></i> <span>History</span> <span
                                class="arrow icofont-rounded-down ms-auto text-end fs-5"></span></a>
                        <!-- Menu: Sub menu ul -->
                        <ul class="sub-menu collapse<?php echo e($isHistoryMenu ? ' show' : ''); ?>" id="menu-product_chat">
                            <li><a class="ms-link" href="<?php echo e(route('admin_panel.admin.chatlogs.logs')); ?>">Chat
                                    History</a></li>
                            
                        </ul>
                    </li>
                    <li class="collapsed">
                        <a class="m-link" data-bs-toggle="collapse" data-bs-target="#menu-product_call"
                            href="#">
                            <i class="icofont-phone fs-5"></i> <span>Calling CRM</span> <span
                                class="arrow icofont-rounded-down ms-auto text-end fs-5"></span></a>
                        <!-- Menu: Sub menu ul -->
                        <ul class="sub-menu collapse<?php echo e($isCallingCrmMenu ? ' show' : ''); ?>" id="menu-product_call">
                            <li><a class="ms-link" href="<?php echo e(route('admin_panel.admin.callingcrm.dashboard')); ?>">Dashboard</a></li>
                            <li><a class="ms-link" href="<?php echo e(route('admin_panel.admin.callingcrm.contact')); ?>">Contact</a></li>
                            <li><a class="ms-link" href="<?php echo e(route('admin_panel.admin.callingcrm.pipeline')); ?>">Pipeline</a></li>
                            <li><a class="ms-link" href="<?php echo e(route('admin_panel.admin.callingcrm.report')); ?>">Report</a></li>
                            <li><a class="ms-link" href="<?php echo e(route('admin_panel.admin.callingcrm.trends')); ?>">Trends</a></li>
                            <li><a class="ms-link" href="<?php echo e(route('admin_panel.admin.callingcrm.settings')); ?>">Settings</a></li>
                        </ul>
                    </li>
                    <li class="collapsed">
                        <a class="m-link" data-bs-toggle="collapse" data-bs-target="#menu-product_order"
                            href="#">
                            <i class="icofont-cart fs-5"></i> <span>Orders</span> <span
                                class="arrow icofont-rounded-down ms-auto text-end fs-5"></span></a>
                        <!-- Menu: Sub menu ul -->
                        <ul class="sub-menu collapse<?php echo e($isOrderMenu ? ' show' : ''); ?>" id="menu-product_order">
                            <li><a class="ms-link" href="<?php echo e(route('orders.index')); ?>">All Orders</a></li>
                            <li><a class="ms-link" href="<?php echo e(route('admin_panel.admin.orders.order_details')); ?>">Order
                                    Details</a></li>
                            
                        </ul>
                    </li>


                    <li class="collapsed">
                        <a class="m-link" data-bs-toggle="collapse" data-bs-target="#menu-product_sto"
                            href="#">
                            <i class="icofont-box fs-5"></i> <span>Stock</span> <span
                                class="arrow icofont-rounded-down ms-auto text-end fs-5"></span></a>
                        <!-- Menu: Sub menu ul -->
                        <ul class="sub-menu collapse<?php echo e($isStockMenu ? ' show' : ''); ?>" id="menu-product_sto">
                            
                            <li><a class="ms-link" href="<?php echo e(route('admin_panel.admin.stocks.index')); ?>">Add/Update
                                    Stock</a></li>
                            
                        </ul>
                    </li>

                    <li><a class="m-link active" href="<?php echo e(route('admin_panel.admin.edit.profile')); ?>"><i
                                class="icofont-home fs-5"></i> <span>Profile</span></a></li>
                    
                    <button type="button" class="btn btn-link sidebar-mini-btn text-light">
                        <span class="ms-2"><i class="icofont-bubble-right"></i></span>
                    </button>
            </div>
        </div>

        <!-- main body area -->
        <div class="main px-lg-4 px-md-4">

            <!-- Body: Header -->
            <div class="header">
                <nav class="navbar py-4">
                    <div class="container-xxl">

                        <!-- header rightbar icon -->
                        <div class="h-right d-flex align-items-center mr-5 mr-lg-0 order-1">
                            <div class="dropdown user-profile ml-2 ml-sm-3 d-flex align-items-center zindex-popover">
                                <div class="u-info me-2">
                                    <p class="mb-0 text-end line-height-sm "><span class="font-weight-bold">John
                                            Quinn</span></p>
                                    <small>Admin Profile</small>
                                </div>
                                <a class="nav-link dropdown-toggle pulse p-0" href="#" role="button"
                                    data-bs-toggle="dropdown" data-bs-display="static">
                                    <img class="avatar lg rounded-circle img-thumbnail"
                                        src="<?php echo e(asset('assets/images/profile_av.svg')); ?>" alt="profile">
                                </a>
                                <div
                                    class="dropdown-menu rounded-lg shadow border-0 dropdown-animation dropdown-menu-end p-0 m-0">
                                    <div class="card border-0 w280">
                                        <div class="card-body pb-0">
                                            <div class="d-flex py-1">
                                                <img class="avatar rounded-circle" src="assets/images/profile_av.svg"
                                                    alt="profile">
                                                <div class="flex-fill ms-3">
                                                    <p class="mb-0"><span class="font-weight-bold">John Quinn</span>
                                                    </p>
                                                    <small class="">Johnquinn@gmail.com</small>
                                                </div>
                                            </div>

                                            <div>
                                                <hr class="dropdown-divider border-dark">
                                            </div>
                                        </div>
                                        <div class="list-group m-2 ">
                                            <a href="admin-profile.php"
                                                class="list-group-item list-group-item-action border-0 "><i
                                                    class="icofont-ui-user fs-5 me-3"></i>Profile Page</a>
                                            <a href="order-invoices.php"
                                                class="list-group-item list-group-item-action border-0 "><i
                                                    class="icofont-file-text fs-5 me-3"></i>Order Invoices</a>
                                            <a href="<?php echo e(route('admin_panel.admin.logout')); ?>"
                                                class="list-group-item list-group-item-action border-0"
                                                onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                                <i class="icofont-logout fs-5 me-3"></i>Signout
                                            </a>
                                            <form id="logout-form" action="<?php echo e(route('admin_panel.admin.logout')); ?>"
                                                method="POST" style="display: none;">
                                                <?php echo csrf_field(); ?>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- menu toggler -->
                        <button class="navbar-toggler p-0 border-0 menu-toggle order-3" type="button"
                            data-bs-toggle="collapse" data-bs-target="#mainHeader">
                            <span class="fa fa-bars"></span>
                        </button>
                    </div>
                </nav>
            </div>

            <!-- Body: Body -->
            <div class="body d-flex py-3">
                <div class="container-xxl">


                    <?php echo $__env->yieldContent('main-content'); ?>


                </div>

            </div>

            <!-- Jquery Core Js -->
            <script src="<?php echo e(asset('assets/bundles/libscripts.bundle.js')); ?>"></script>

            <!-- Plugin Js -->
            <script src="<?php echo e(asset('assets/bundles/apexcharts.bundle.js')); ?>"></script>
            <script src="<?php echo e(asset('assets/bundles/dataTables.bundle.js')); ?>"></script>

            <!-- Jquery Page Js -->
            <script src="<?php echo e(asset('assets/js/template.js')); ?>"></script>
            <script src="<?php echo e(asset('assets/js/page/index.js')); ?>"></script>
            
            <?php if(request()->routeIs('admin_panel.admin.index')): ?>
            <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyB1Jr7axGGkwvHRnNfoOzoVRFV3yOPHJEU&amp;callback=myMap" defer></script>
            <?php endif; ?>

            <script>
                if ($('#myDataTable').length) {
                    $('#myDataTable')
                        .addClass('nowrap')
                        .dataTable({
                            columnDefs: [{
                                targets: [-1, -3],
                                className: 'dt-body-right'
                            }]
                        });
                }
                if ($('#servicesDataTable').length) {
                    $('#servicesDataTable')
                        .addClass('nowrap')
                        .dataTable({
                            columnDefs: [{
                                targets: [-1, -3],
                                className: 'dt-body-right'
                            }]
                        });
                }
                if ($('#offerDataTable').length) {
                    $('#offerDataTable')
                        .addClass('nowrap')
                        .dataTable({
                            columnDefs: [{
                                targets: [-1, -3],
                                className: 'dt-body-right'
                            }]
                        });
                }
                if ($('#testonomailsDataTable').length) {
                    $('#testonomailsDataTable')
                        .addClass('nowrap')
                        .dataTable({
                            columnDefs: [{
                                targets: [-1, -3],
                                className: 'dt-body-right'
                            }]
                        });
                }
            </script>
            <?php if(request()->routeIs('admin_panel.admin.callingcrm.*')): ?>
                <?php
                    $callingCrmConfig = [
                        'baseUrl' => url('/api/calling-crm'),
                        'csrfToken' => csrf_token(),
                        'currentUserId' => auth('admin')->check() ? null : optional(auth()->user())->id,
                    ];
                ?>
                <script id="callingCrmConfig" type="application/json">
                    <?php echo json_encode($callingCrmConfig, 15, 512) ?>
                </script>
                <script src="<?php echo e(asset('js/crm/calling-crm.js')); ?>"></script>
                <script src="<?php echo e(asset('js/crm/crm-core.js')); ?>"></script>
            <?php endif; ?>

            <?php echo $__env->yieldPushContent('scripts'); ?>
</body>
</html>
<?php /**PATH E:\website-project\Amplchat\CMS\resources\views\admin_panel\layout\app.blade.php ENDPATH**/ ?>
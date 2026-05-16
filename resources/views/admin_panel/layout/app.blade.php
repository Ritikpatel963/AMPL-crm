<!doctype html>
<html class="no-js" lang="en" dir="ltr">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=Edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>@yield('title', 'AMPL Chat admin')</title>
    <link rel="icon" href="favicon.ico" type="image/x-icon">

    <!-- Plugin CSS -->
    <link rel="stylesheet" href="include/assets/plugin/datatables/responsive.dataTables.min.css">
    <link rel="stylesheet" href="include/assets/plugin/datatables/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"
        integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">

    <!-- Project CSS -->
    <link rel="stylesheet" href="{{ asset('assets/css/ebazar.style.min.css') }}?v=1.0">

    @stack('styles')

    <!-- jQuery (required by DataTables) -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
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
                <ul class="menu-list flex-grow-1 mt-3">
                    <li><a class="m-link active" href="{{ route('admin_panel.admin.index') }}"><i
                                class="icofont-home fs-5"></i> <span>Dashboard</span></a></li>
                    @can('Manage Users')
                        <li class="collapsed">
                            <a class="m-link" data-bs-toggle="collapse" data-bs-target="#menu-product_user" href="#">
                                <i class="icofont-users-alt-2 fs-5"></i> <span>User Management</span> <span
                                    class="arrow icofont-rounded-down ms-auto text-end fs-5"></span></a>
                            <!-- Menu: Sub menu ul -->
                            <ul class="sub-menu collapse" id="menu-product_user">
                                <li><a class="ms-link" href="{{ route('admin_panel.admin.users.index') }}">Users</a></li>
                            </ul>
                        </li>
                    @endcan
                    <li class="collapsed">
                        <a class="m-link" data-bs-toggle="collapse" data-bs-target="#menu-vendor" href="#">
                            <i class="icofont-users fs-5"></i> <span>Vendor Management</span> <span
                                class="arrow icofont-rounded-down ms-auto text-end fs-5"></span></a>
                        <!-- Menu: Sub menu ul -->
                        <ul class="sub-menu collapse" id="menu-vendor">
                            <li><a class="ms-link" href="{{ route('admin_panel.admin.vendors.index') }}">All Vendors</a></li>
                            <li><a class="ms-link" href="{{ route('admin_panel.admin.vendor_categories.index') }}">Vendor Categories</a></li>
                        </ul>
                    </li>
                    <li class="collapsed">
                        <a class="m-link" data-bs-toggle="collapse" data-bs-target="#menu-product_kyc" href="#">
                            <i class="icofont-file-alt fs-5"></i> <span>Kyc Management</span> <span
                                class="arrow icofont-rounded-down ms-auto text-end fs-5"></span></a>
                        <!-- Menu: Sub menu ul -->
                        <ul class="sub-menu collapse" id="menu-product_kyc">
                            <li><a class="ms-link" href="{{ route('admin_panel.admin.kyc.pending') }}">Pending Kyc</a>
                            </li>
                            <li><a class="ms-link" href="{{ route('admin_panel.admin.kyc.approved') }}">Approved Kyc</a>
                            </li>
                            <li><a class="ms-link" href="{{ route('admin_panel.admin.kyc.rejected') }}">Rejected Kyc</a>
                            </li>
                            {{-- <li><a class="ms-link" href="add-service.php">Add Service</a></li> --}}
                        </ul>
                    </li>
                    <li class="collapsed">
                        <a class="m-link" data-bs-toggle="collapse" data-bs-target="#menu-product_p" href="#">
                            <i class="icofont-box  fs-5"></i> <span>Product Management</span> <span
                                class="arrow icofont-rounded-down ms-auto text-end fs-5"></span></a>
                        <!-- Menu: Sub menu ul -->
                        <ul class="sub-menu collapse" id="menu-product_p">
                            <li><a class="ms-link" href="{{ route('admin_panel.admin.products.index') }}">Products
                                    List</a>
                            </li>
                            <li><a class="ms-link" href="{{ route('admin_panel.admin.products.create') }}">Add
                                    Products</a></li>
                            <li><a class="ms-link" href="{{ route('admin_panel.admin.categories.index') }}">Manage
                                    Category</a>
                            </li>
                            {{-- <li><a class="ms-link" href="add-service.php">Add Service</a></li> --}}
                        </ul>
                    </li>
                    <li class="collapsed">
                        <a class="m-link" data-bs-toggle="collapse" data-bs-target="#menu-product_ship" href="#">
                            <i class="icofont-truck-loaded fs-5"></i> <span>Shipping</span> <span
                                class="arrow icofont-rounded-down ms-auto text-end fs-5"></span></a>
                        <!-- Menu: Sub menu ul -->
                        <ul class="sub-menu collapse" id="menu-product_ship">
                            <li><a class="ms-link" href="{{ route('admin_panel.admin.shipping.index') }}">Shipping</a>
                                {{-- <li><a class="ms-link" href="add-service.php">Add Service</a></li> --}}
                        </ul>
                    </li>
                    <li class="collapsed">
                        <a class="m-link" data-bs-toggle="collapse" data-bs-target="#menu-product_cust" href="#">
                            <i class="icofont-user-alt-3 fs-5"></i> <span>Customers</span> <span
                                class="arrow icofont-rounded-down ms-auto text-end fs-5"></span></a>
                        <!-- Menu: Sub menu ul -->
                        <ul class="sub-menu collapse" id="menu-product_cust">
                            <li><a class="ms-link" href="{{ route('admin_panel.admin.customers.view') }}">Customer
                                    Profile</a></li>
                            <li><a class="ms-link"
                                    href="{{ route('admin_panel.admin.customers.customer_manage') }}">Manage
                                    Customer</a></li>
                            {{-- <li><a class="ms-link" href="add-service.php">Add Service</a></li> --}}
                        </ul>
                    </li>
                    <li class="collapsed">
                        <a class="m-link" data-bs-toggle="collapse" data-bs-target="#menu-product_pay"
                            href="#">
                            <i class="icofont-credit-card fs-5"></i> <span>Payments</span> <span
                                class="arrow icofont-rounded-down ms-auto text-end fs-5"></span></a>
                        <!-- Menu: Sub menu ul -->
                        <ul class="sub-menu collapse" id="menu-product_pay">
                            <li><a class="ms-link"
                                    href="{{ route('admin_panel.admin.payments.payment_management') }}">Payments</a>
                            </li>
                            {{-- <li><a class="ms-link" href="add-service.php">Add Service</a></li> --}}
                        </ul>
                    </li>
                    <li class="collapsed">
                        <a class="m-link" data-bs-toggle="collapse" data-bs-target="#menu-product_per"
                            href="#">
                            <i class="icofont-shield  fs-5"></i> <span>Permission</span> <span
                                class="arrow icofont-rounded-down ms-auto text-end fs-5"></span></a>
                        <!-- Menu: Sub menu ul -->
                        <ul class="sub-menu collapse" id="menu-product_per">
                            {{-- <li><a class="ms-link" href="{{ route('admin_panel.admin.permission.assign') }}">Permissions</a></li> --}}
                            <li><a class="ms-link" href="{{ route('admin_panel.admin.role') }}">Role</a>
                            </li>
                            <li><a class="ms-link" href="{{ route('admin_panel.admin.permissions.index') }}">Manage
                                    Permission</a></li>
                            {{-- <li><a class="ms-link" href="add-service.php">Add Service</a></li> --}}
                        </ul>
                    </li>
                    <li class="collapsed">
                        <a class="m-link" data-bs-toggle="collapse" data-bs-target="#menu-callingcrm"
                            href="#">
                            <i class="icofont-ui-call fs-5"></i> <span>Calling CRM</span> <span
                                class="arrow icofont-rounded-down ms-auto text-end fs-5"></span></a>
                        <ul class="sub-menu collapse" id="menu-callingcrm">
                            <li><a class="ms-link" href="{{ route('callingcrm.dashboard') }}">Dashboard</a></li>
                            <li><a class="ms-link" href="{{ route('callingcrm.contacts.index') }}">Contacts</a></li>
                            <li><a class="ms-link" href="{{ route('callingcrm.contacts.create') }}">Add Lead</a></li>
                            <li><a class="ms-link" href="{{ route('callingcrm.contacts.upload') }}">Upload Excel</a></li>
                            <li><a class="ms-link" href="{{ route('callingcrm.pipeline.index') }}">Pipeline</a></li>
                            <li><a class="ms-link" href="{{ route('callingcrm.reports.index') }}">Reports</a></li>
                            <li><a class="ms-link" href="{{ route('callingcrm.trends.index') }}">Trends</a></li>
                        </ul>
                    </li>
                    <li class="collapsed">
                        <a class="m-link" data-bs-toggle="collapse" data-bs-target="#menu-product_chat"
                            href="#">
                            <i class="icofont-clock-time fs-5"></i> <span>History</span> <span
                                class="arrow icofont-rounded-down ms-auto text-end fs-5"></span></a>
                        <!-- Menu: Sub menu ul -->
                        <ul class="sub-menu collapse" id="menu-product_chat">
                            <li><a class="ms-link" href="{{ route('admin_panel.admin.chatlogs.logs') }}">Chat
                                    History</a></li>
                            {{-- <li><a class="ms-link" href="add-service.php">Add Service</a></li> --}}
                        </ul>
                    </li>
                    <li class="collapsed">
                        <a class="m-link" data-bs-toggle="collapse" data-bs-target="#menu-product_order"
                            href="#">
                            <i class="icofont-cart fs-5"></i> <span>Orders</span> <span
                                class="arrow icofont-rounded-down ms-auto text-end fs-5"></span></a>
                        <!-- Menu: Sub menu ul -->
                        <ul class="sub-menu collapse" id="menu-product_order">
                            <li><a class="ms-link" href="{{ route('orders.index') }}">All Orders</a></li>
                            <li><a class="ms-link" href="{{ route('admin_panel.admin.orders.order_details') }}">Order
                                    Details</a></li>
                            {{-- <li><a class="ms-link" href="add-service.php">Add Service</a></li> --}}
                        </ul>
                    </li>


                    </li>
                    <li class="collapsed">
                        <a class="m-link" data-bs-toggle="collapse" data-bs-target="#menu-product_sto"
                            href="#">
                            <i class="icofont-box fs-5"></i> <span>Stock</span> <span
                                class="arrow icofont-rounded-down ms-auto text-end fs-5"></span></a>
                        <!-- Menu: Sub menu ul -->
                        <ul class="sub-menu collapse" id="menu-product_sto">
                            {{-- <li><a class="ms-link" href="{{ route('admin_panel.admin.stock.stock-list') }}">Stock
                                    List</a></li> --}}
                            <li><a class="ms-link" href="{{ route('admin_panel.admin.stocks.index') }}">Add/Update
                                    Stock</a></li>
                            {{-- <li><a class="ms-link"
                                    href="{{ route('admin_panel.admin.stock.stock-history') }}">Stock-History</a></li> --}}
                        </ul>
                    </li>

                    <li><a class="m-link active" href="{{ route('admin_panel.admin.edit.profile') }}"><i
                                class="icofont-home fs-5"></i> <span>Profile</span></a></li>
                    {{--
                     <li class="collapsed">
                        <a class="m-link" data-bs-toggle="collapse" data-bs-target="#categories" href="{{ route('admin.edit.profile') }}">
                            <i class="icofont-chart-flow fs-5"></i> <span>Profile</span> <span class="arrow icofont-rounded-down ms-auto text-end fs-5"></span></a>
                            <!-- Menu: Sub menu ul -->
                             <ul class="sub-menu collapse" id="categories">
                                <li><a class="ms-link" href="testimonial.php">Testimonial Add</a></li>
                                <li><a class="ms-link" href="testimonial-list.php">Testimonial List</a></li>
                            </ul>
                    </li>
                    
                    <li><a class="m-link" href="contactpage.php"><i class="icofont-focus fs-5"></i> <span>Contact Page</span></a></li>
                    <li><a class="m-link" href="website-info.php"><i class="icofont-comment fs-5"></i> <span>Header - Footer</span></a></li>
                    <li class="collapsed">
                        <a class="m-link" data-bs-toggle="collapse" data-bs-target="#product" href="#">
                            <i class="fa fa-shopping-bag" aria-hidden="true"></i> <span>Product</span> <span class="arrow icofont-rounded-down ms-auto text-end fs-5"></span></a>
                            <!-- Menu: Sub menu ul -->
                            <ul class="sub-menu collapse" id="product">
                                <li><a class="ms-link" href="product-add.php">Product Add</a></li>
                                <li><a class="ms-link" href="product-list.php">Product List</a></li>
                            </ul>
                    </li>
                     <li><a class="m-link" href=""><i class="fa fa-dollar fa-lg"></i> <span>Order</span></a></li>
                     <li><a class="m-link" href="gallery.php"><i class="icofont-photobucket fs-5"></i> <span>Gallery</span></a></li>
                     <li><a class="m-link" href="offer.php"><i class="icofont-notepad fs-5"></i> <span>Offers</span></a></li> --}}
                    <!-- Menu: menu collepce btn -->
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
                                        src="{{ asset('assets/images/profile_av.svg') }}" alt="profile">
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
                                            <a href="{{ route('admin_panel.admin.logout') }}"
                                                class="list-group-item list-group-item-action border-0"
                                                onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                                <i class="icofont-logout fs-5 me-3"></i>Signout
                                            </a>
                                            <form id="logout-form" action="{{ route('admin_panel.admin.logout') }}"
                                                method="POST" style="display: none;">
                                                @csrf
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

                        <!-- main menu Search-->
                        {{-- <div class="order-0 col-lg-4 col-md-4 col-sm-12 col-12 mb-3 mb-md-0 ">
                            <div class="input-group flex-nowrap input-group-lg">
                                <input type="search" class="form-control" placeholder="Search" aria-label="search"
                                    aria-describedby="addon-wrapping">
                                <button type="button" class="input-group-text" id="addon-wrapping"><i
                                        class="fa fa-search"></i></button>
                            </div>
                        </div> --}}

                    </div>
                </nav>
            </div>

            <!-- Body: Body -->
            <div class="body d-flex py-3">
                <div class="container-xxl">


                    @yield('main-content')


                    <!-- Modal Custom Settings-->


                </div>

            </div>

            <!-- Jquery Core Js -->
            <script src="{{ asset('assets/bundles/libscripts.bundle.js') }}"></script>

            <!-- Plugin Js -->
            <script src="{{ asset('assets/bundles/apexcharts.bundle.js') }}"></script>
            <script src="{{ asset('assets/bundles/dataTables.bundle.js') }}"></script>

            <!-- Jquery Page Js -->
            <script src="{{ asset('assets/js/template.js') }}"></script>
            <script src="{{ asset('assets/js/page/index.js') }}"></script>
            <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyB1Jr7axGGkwvHRnNfoOzoVRFV3yOPHJEU&amp;callback=myMap">
            </script>
            <script>
                $('#myDataTable')
                    .addClass('nowrap')
                    .dataTable({
                        columnDefs: [{
                            targets: [-1, -3],
                            className: 'dt-body-right'
                        }]
                    });
            </script>
            <script>
                $('#servicesDataTable')
                    .addClass('nowrap')
                    .dataTable({
                        columnDefs: [{
                            targets: [-1, -3],
                            className: 'dt-body-right'
                        }]
                    });
            </script>
            <script>
                $('#offerDataTable')
                    .addClass('nowrap')
                    .dataTable({
                        columnDefs: [{
                            targets: [-1, -3],
                            className: 'dt-body-right'
                        }]
                    });
            </script>
            <script>
                $('#testonomailsDataTable')
                    .addClass('nowrap')
                    .dataTable({
                        columnDefs: [{
                            targets: [-1, -3],
                            className: 'dt-body-right'
                        }]
                    });
            </script>
</body>

@stack('scripts')

</html>

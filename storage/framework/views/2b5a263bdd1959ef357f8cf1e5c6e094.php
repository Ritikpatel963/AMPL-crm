
<?php $__env->startSection('title', 'AMPL Chat'); ?>


<?php $__env->startSection('main-content'); ?>
    <div class="row g-3 mb-3 row-cols-1 row-cols-sm-2 row-cols-md-2 row-cols-lg-2 row-cols-xl-4">
        <div class="col">
            <div class="alert-success alert mb-0">
                <div class="d-flex align-items-center">
                    <div class="avatar rounded no-thumbnail bg-success text-light"><i class="fa fa-dollar fa-lg"></i></div>
                    <div class="flex-fill ms-3 text-truncate">
                        <div class="h6 mb-0">Totel Users</div>
                        <span class="small">232</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="alert-danger alert mb-0">
                <div class="d-flex align-items-center">
                    <div class="avatar rounded no-thumbnail bg-danger text-light"><i class="fa fa-credit-card fa-lg"></i>
                    </div>
                    <div class="flex-fill ms-3 text-truncate">
                        <div class="h6 mb-0">Totel Customers</div>
                        <span class="small">568</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="alert-warning alert mb-0">
                <div class="d-flex align-items-center">
                    <div class="avatar rounded no-thumbnail bg-warning text-light"><i class="fa fa-smile-o fa-lg"></i></div>
                    <div class="flex-fill ms-3 text-truncate">
                        <div class="h6 mb-0">Totel Agents</div>
                        <span class="small">8</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="alert-info alert mb-0">
                <div class="d-flex align-items-center">
                    <div class="avatar rounded no-thumbnail bg-info text-light"><i class="fa fa-shopping-bag"
                            aria-hidden="true"></i></div>
                    <div class="flex-fill ms-3 text-truncate">
                        <div class="h6 mb-0">Totel Products</div>
                        <span class="small">85</span>
                    </div>
                </div>
            </div>
        </div>
    </div><!-- Row end  -->
    <div class="row g-3 mb-3 row-cols-1 row-cols-sm-2 row-cols-md-2 row-cols-lg-2 row-cols-xl-4">
        <div class="col">
            <div class="alert-success alert mb-0">
                <div class="d-flex align-items-center">
                    <div class="avatar rounded no-thumbnail bg-success text-light"><i class="fa fa-dollar fa-lg"></i></div>
                    <div class="flex-fill ms-3 text-truncate">
                        <div class="h6 mb-0">Active Chats</div>
                        <span class="small">232</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="alert-danger alert mb-0">
                <div class="d-flex align-items-center">
                    <div class="avatar rounded no-thumbnail bg-danger text-light"><i class="fa fa-credit-card fa-lg"></i>
                    </div>
                    <div class="flex-fill ms-3 text-truncate">
                        <div class="h6 mb-0">Pending Request</div>
                        <span class="small">568</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="alert-warning alert mb-0">
                <div class="d-flex align-items-center">
                    <div class="avatar rounded no-thumbnail bg-warning text-light"><i class="fa fa-smile-o fa-lg"></i></div>
                    <div class="flex-fill ms-3 text-truncate">
                        <div class="h6 mb-0">Totel Agents</div>
                        <span class="small">8</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="alert-info alert mb-0">
                <div class="d-flex align-items-center">
                    <div class="avatar rounded no-thumbnail bg-info text-light"><i class="fa fa-shopping-bag"
                            aria-hidden="true"></i></div>
                    <div class="flex-fill ms-3 text-truncate">
                        <div class="h6 mb-0">Totel Products</div>
                        <span class="small">85</span>
                    </div>
                </div>
            </div>
        </div>
    </div><!-- Row end  -->

    


    <div class="row g-3 mb-3">
        <div class="col-xxl-8 col-xl-8">
            <div class="card mb-3">
                <div
                    class="card-header py-3 d-flex justify-content-between align-items-center bg-transparent border-bottom-0">
                    <h6 class="m-0 fw-bold">Shopping Status</h6>
                </div>
                <div class="card-body">
                    <div class="ac-line-transparent" id="apex-shoppingstatus"></div>
                </div>
            </div>
            <div class="card">
                <div
                    class="card-header py-3 d-flex justify-content-between align-items-center bg-transparent border-bottom-0">
                    <h6 class="m-0 fw-bold">Top Selling Product</h6>
                </div>
                <div class="card-body">
                    <div id="topselling"></div>
                </div>
            </div>
        </div>
          <div class="col-lg-4 col-md-12">
            <div class="card">
                <div
                    class="card-header py-3 d-flex justify-content-between align-items-center bg-transparent border-bottom-0">
                    <h6 class="m-0 fw-bold">Active Users Status</h6>
                </div>
                <div class="card-body">
                    <div class="p-4 active-user bg-lightblue rounded-2 mb-2">
                        <span class="fw-bold d-flex justify-content-center fs-3">1345</span>
                    </div>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th scope="col">Active pages</th>
                                    <th scope="col">Users</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><a href="#">/dist/product.html</a></td>
                                    <td>245</td>
                                </tr>
                                <tr>
                                    <td><a href="#">/dist/product-cart.html</a></td>
                                    <td>455</td>
                                </tr>
                                <tr>
                                    <td><a href="#">/dist/admin-profile.html</a></td>
                                    <td>45</td>
                                </tr>
                                <tr>
                                    <td><a href="#">/dist/order-history.html</a></td>
                                    <td>545</td>
                                </tr>
                                <tr>
                                    <td><a href="#">/dist/product-detail.html</a></td>
                                    <td>55</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div><!-- Row end  -->

    <div class="row g-3 mb-3 row-deck">
      
        <div class="col-lg-12 col-md-12">
            <div class="card">
                <div
                    class="card-header py-3 d-flex justify-content-between align-items-center bg-transparent border-bottom-0">
                    <h6 class="m-0 fw-bold">Avg Expense Costs</h6>
                </div>
                <div class="card-body">
                    <div class="h2 mb-0">$1105.5</div>
                    <span class="text-muted small">Avg Expense Costs All Month</span>
                    <div id="apex-expense"></div>
                </div>
            </div>
        </div>
    </div><!-- Row end  -->

    <div class="row g-3 mb-3">
        <div class="col-md-12">
            <div class="card">
                <div
                    class="card-header py-3 d-flex justify-content-between align-items-center bg-transparent border-bottom-0">
                    <h6 class="m-0 fw-bold">Recent Transactions</h6>
                </div>
                <div class="card-body">
                    <table id="myDataTable" class="table table-hover align-middle mb-0" style="width: 100%;">
                        <thead>
                            <tr>
                                <th>Id</th>
                                <th>Item</th>
                                <th>Customer Name</th>
                                <th>Payment Info</th>
                                <th>Price</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><strong>#Order-78414</strong></td>
                                <td><img src="assets/images/product/product-1.jpg" class="avatar lg rounded me-2"
                                        alt="profile-image"><span> Oculus VR </span></td>
                                <td>Molly</td>
                                <td>Credit Card</td>
                                <td>
                                    $420
                                </td>
                                <td><span class="badge bg-warning">Progress</span></td>
                            </tr>
                            <tr>
                                <td><strong>#Order-58414</strong></td>
                                <td><img src="assets/images/product/product-2.jpg" class="avatar lg rounded me-2"
                                        alt="profile-image"><span>Wall Clock</span></td>
                                <td>Brian</td>
                                <td>Debit Card</td>
                                <td>
                                    $220
                                </td>
                                <td><span class="badge bg-success">Complited</span></td>
                            </tr>
                            <tr>
                                <td><strong>#Order-48414</strong></td>
                                <td><img src="assets/images/product/product-3.jpg" class="avatar lg rounded me-2"
                                        alt="profile-image"><span>Note Diaries</span></td>
                                <td>Julia</td>
                                <td>Debit Card</td>
                                <td>
                                    $250
                                </td>
                                <td><span class="badge bg-success">Complited</span></td>
                            </tr>
                            <tr>
                                <td><strong>#Order-38414</strong></td>
                                <td><img src="assets/images/product/product-4.jpg" class="avatar lg rounded me-2"
                                        alt="profile-image"><span>Flower Port</span></td>
                                <td>Sonia</td>
                                <td>Credit Card</td>
                                <td>
                                    $320
                                </td>
                                <td><span class="badge bg-warning">Progress</span></td>
                            </tr>
                            <tr>
                                <td><strong>#Order-28414</strong></td>
                                <td><img src="assets/images/product/product-1.jpg" class="avatar lg rounded me-2"
                                        alt="profile-image"><span>Oculus VR</span></td>
                                <td>Adam H</td>
                                <td>Debit Card</td>
                                <td>
                                    $20
                                </td>
                                <td><span class="badge bg-warning">Progress</span></td>
                            </tr>
                            <tr>
                                <td><strong>#Order-18414</strong></td>
                                <td><img src="assets/images/product/product-2.jpg" class="avatar lg rounded me-2"
                                        alt="profile-image"><span>Wall Clock</span></td>
                                <td>Alexander</td>
                                <td>Debit Card</td>
                                <td>
                                    $820
                                </td>
                                <td><span class="badge bg-success">Complited</span></td>
                            </tr>
                            <tr>
                                <td><strong>#Order-11414</strong></td>
                                <td><img src="assets/images/product/product-3.jpg" class="avatar lg rounded me-2"
                                        alt="profile-image"><span>Note Diaries</span></td>
                                <td>Gabrielle</td>
                                <td>Bank Emi</td>
                                <td>
                                    $620
                                </td>
                                <td><span class="badge bg-success">Complited</span></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div><!-- Row end  -->

    </div>
    </div>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('admin_panel.layout.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH E:\allproject\ampl_crm_project\AMPL-crm\resources\views/admin_panel/index.blade.php ENDPATH**/ ?>
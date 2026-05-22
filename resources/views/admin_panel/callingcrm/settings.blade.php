@extends('admin_panel.layout.app')

@section('title', 'Calling CRM Settings')
@push('styles')
<link rel="stylesheet" href="{{ asset('css/crm/calling-crm.css') }}">
@endpush

@include('admin_panel.callingcrm.partials.ui-polish')

@section('main-content')
    @php
        $tabs = [
            ['id' => 'users', 'label' => 'Users'],
            ['id' => 'pipelines', 'label' => 'Pipelines'],
            ['id' => 'profile', 'label' => 'Profile'],
            ['id' => 'roles', 'label' => 'Roles and Permission'],
            ['id' => 'retry', 'label' => 'Retry Setting'],
            ['id' => 'priority', 'label' => 'Lead Priority'],
            ['id' => 'properties', 'label' => 'Custom Contact Property'],
             ];

        $users = [
            ['Abhishek Thakur', '6232122642', 'Shubham Birla', '', 'Executive', '10-10-2026'],
            ['Aishee Bansriar', '9201977461', 'Swagat Patra', '', 'Executive', '10-10-2026'],
            ['Akash Pandey', '7470768021', 'Swagat Patra', '', 'Executive', '10-10-2026'],
            ['Anshul Vishwakarma', '8269136055', 'Swagat Patra', '', 'Executive', '10-10-2026'],
            ['Archita Dora', '9753000545', 'Swagat Patra', '', 'Executive', '10-10-2026'],
            ['Bhranti Bopache', '9201977469', 'Shubham Birla', '', 'Executive', '10-10-2026'],
            ['Blank', '9993596924', 'Swagat Patra', '', 'Executive', '10-10-2026'],
            ['Blank', '9201977467', 'Shubham Birla', '', 'Executive', '10-10-2026'],
            ['Hanshraj Yadav', '7470768022', 'Harshit Saini', '', 'Executive', '10-10-2026'],
            ['Harshit Saini', '7015293292', '', 'parikshit1697@gmail.com', 'Admin', '10-10-2026'],
            ['Jai Krishna Patware', '9993574730', 'Swagat Patra', '', 'Executive', '10-10-2026'],
            ['Jiya Srivastava', '9201977471', 'Harshit Saini', '', 'Executive', '10-10-2026'],
            ['Kashmira Rudra', '9201977474', 'Harshit Saini', '', 'Executive', '10-10-2026'],
            ['Muskan Prajapati', '9201977477', 'Swagat Patra', '', 'Executive', '10-10-2026'],
            ['Niharika Eligeti', '9203390532', 'Harshit Saini', '', 'Executive', '10-10-2026'],
            ['Parv Yadav', '8770719711', '', '', 'Admin', '10-10-2026'],
            ['Priyanshu Singh', '9201977462', 'Harshit Saini', '', 'Executive', '10-10-2026'],
            ['Purva Bhosle', '9201977478', 'Swagat Patra', '', 'Executive', '10-10-2026'],
            ['Rahul Kourav', '7470768023', 'Swagat Patra', '', 'Executive', '10-10-2026'],
            ['Ravi Kumar', '9201977466', 'Swagat Patra', '', 'Executive', '10-10-2026'],
            ['Ritik Patel', '9630884927', '', '', 'Admin', '10-10-2026'],
            ['Rupali Rai', '9201977468', 'Shubham Birla', '', 'Executive', '10-10-2026'],
            ['Saksham Patware', '9201977460', 'Swagat Patra', '', 'Executive', '10-10-2026'],
            ['Shivam Gurjar', '9201977465', 'Swagat Patra', '', 'Executive', '10-10-2026'],
            ['Shivani Sengar', '8085764760', 'Swagat Patra', '', 'Executive', '10-10-2026'],
            ['Shubham Birla', '9753000546', 'Swagat Patra', '', 'Executive', '10-10-2026'],
            ['Sujal Gupta', '7879569063', 'Swagat Patra', '', 'Executive', '10-10-2026'],
            ['Swagat Patra', '9993593320', 'Harshit Saini', '', 'Team. Lead', '10-10-2026'],
            ['Sweta Kumari', '9201977470', 'Shubham Birla', '', 'Executive', '10-10-2026'],
            ['Vivek Chaudhary', '9201977463', 'Swagat Patra', '', 'Executive', '10-10-2026'],
            ['Yuvraj Singh', '9201977464', 'Shubham Birla', '', 'Executive', '10-10-2026'],
        ];

        $customContactProperties = [
            ['Company Name', 'text'],
            ['Address Line 1', 'text'],
            ['Address Line 2', 'text'],
            ['Town/City', 'text'],
            ['State', 'text'],
            ['Pincode', 'number'],
            ['GST', 'text'],
        ];
    @endphp

    <div class="calling-crm-settings">
        <h1 class="settings-title">Settings</h1>

        <div class="settings-tabs-row" aria-label="Settings tabs">
            <button type="button" class="tab-arrow" aria-label="Previous settings tabs">
                <i class="fa-solid fa-chevron-left"></i>
            </button>
            <div class="tabs-track">
                @foreach ($tabs as $index => $tab)
                    <button type="button" class="tab-item {{ $index === 0 ? 'active' : '' }}" data-settings-tab="{{ $tab['id'] }}">{{ $tab['label'] }}</button>
                @endforeach
            </div>
            <button type="button" class="tab-arrow" aria-label="Next settings tabs">
                <i class="fa-solid fa-chevron-right"></i>
            </button>
        </div>

        <div class="content-wrap">
            <section class="settings-panel active" data-settings-panel="users">
                <div class="users-flow-view active" data-users-flow="list">
                <div class="users-toolbar">
                    <div>
                        <div class="users-title-row">
                            <h2 class="users-title">Users</h2>
                            <a href="https://docs.neodove.com/" target="_blank" class="learn-btn" rel="noopener">
                                <i class="fa-solid fa-book-open"></i>
                                Learn More
                            </a>
                        </div>
                        <p class="description">Manage your team with user creation and user deactivation or deletion.</p>
                    </div>

                    <div class="toolbar-actions">
                        <label class="search-box" aria-label="Search">
                            <input type="search" placeholder="Search">
                            <i class="fa-solid fa-filter"></i>
                            <i class="fa-solid fa-magnifying-glass"></i>
                        </label>
                        <button type="button" class="action-btn">
                            <i class="fa-solid fa-rotate"></i>
                            Refresh
                        </button>
                        <button type="button" class="action-btn primary" data-add-user-open>
                            <i class="fa-solid fa-user"></i>
                            Add User
                        </button>
                        <div class="view-toggle" aria-label="View type">
                            <button type="button" class="view-btn" aria-label="Grid view">
                                <i class="fa-regular fa-rectangle-list"></i>
                            </button>
                            <button type="button" class="view-btn active" aria-label="List view">
                                <i class="fa-solid fa-list"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <div class="table-card">
                    <div class="table-scroll">
                        <table>
                            <thead>
                                <tr>
                                    <th>No.</th>
                                    <th>Name <i class="fa-solid fa-arrow-up"></i></th>
                                    <th>Mobile Number</th>
                                    <th>Reporting To</th>
                                    <th>Email</th>
                                    <th>Role</th>
                                    <th>Expiry Date</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody data-users-table-body>
                                @for ($i = 0; $i < 5; $i++)
                                    <tr>
                                        <td><span class="skeleton-loader" style="width: 20px; height: 16px;"></span></td>
                                        <td><span class="skeleton-loader" style="width: 120px; height: 16px;"></span></td>
                                        <td><span class="skeleton-loader" style="width: 90px; height: 16px;"></span></td>
                                        <td><span class="skeleton-loader" style="width: 100px; height: 16px;"></span></td>
                                        <td><span class="skeleton-loader" style="width: 140px; height: 16px;"></span></td>
                                        <td><span class="skeleton-loader" style="width: 70px; height: 16px;"></span></td>
                                        <td><span class="skeleton-loader" style="width: 80px; height: 16px;"></span></td>
                                        <td><span class="skeleton-loader" style="width: 60px; height: 20px; border-radius: 12px;"></span></td>
                                        <td><span class="skeleton-loader" style="width: 24px; height: 24px; border-radius: 50%;"></span></td>
                                    </tr>
                                @endfor
                            </tbody>
                        </table>
                    </div>
                </div>
                </div>

                <div class="users-flow-view reassign-campaign-view" data-users-flow="reassign-campaigns">
                    <div class="reassign-topbar">
                        <div class="reassign-topbar-left">
                            <button type="button" class="flow-back-btn" data-reassign-back aria-label="Back to users">
                                <i class="fa-solid fa-arrow-left"></i>
                            </button>
                        </div>
                        <div class="reassign-tools">
                            <label class="compact-search" aria-label="Search campaigns">
                                <i class="fa-solid fa-magnifying-glass"></i>
                                <input type="search" placeholder="Search" data-reassign-campaign-search>
                            </label>
                            <button type="button" class="reassign-all-btn" data-reassign-summary-open>Reassign All Campaigns</button>
                        </div>
                    </div>
                    <div class="reassign-campaign-table">
                        <div class="table-scroll">
                            <table>
                                <thead>
                                    <tr>
                                        <th>No.</th>
                                        <th>Campaign Name</th>
                                        <th>Pipeline Name</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody data-reassign-campaign-body>
                                    <tr>
                                        <td>1</td>
                                        <td>MP Transacted</td>
                                        <td>Leads</td>
                                        <td><button type="button" class="reassign-row-btn" data-reassign-summary-open data-summary-campaign="MP Transacted">Reassign</button></td>
                                    </tr>
                                    <tr>
                                        <td>2</td>
                                        <td>MP Raw Data</td>
                                        <td>Leads</td>
                                        <td><button type="button" class="reassign-row-btn" data-reassign-summary-open data-summary-campaign="MP Raw Data">Reassign</button></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="users-flow-view reassign-summary-view" data-users-flow="reassign-summary">
                    <div class="summary-head">
                        <div class="summary-title-wrap">
                            <button type="button" class="flow-back-btn" data-summary-back aria-label="Back to campaigns">
                                <i class="fa-solid fa-arrow-left"></i>
                            </button>
                            <span>Lead Summary Report - <span data-summary-title-campaign>MP Transacted</span></span>
                        </div>
                        <button type="button" class="summary-call-log">
                            <i class="fa-solid fa-arrow-right-arrow-left"></i>
                            Call Logs
                        </button>
                    </div>

                    <div class="summary-campaign-row">
                        <article class="summary-campaign-card active" data-summary-campaign-card="MP Transacted">
                            <strong>MP Transacted</strong>
                            <span>Leads</span>
                            <span class="summary-campaign-state">1/2</span>
                        </article>
                        <article class="summary-campaign-card" data-summary-campaign-card="MP Raw Data">
                            <strong>MP Raw Data</strong>
                            <span>Leads</span>
                            <span class="summary-campaign-state">2/2</span>
                        </article>
                    </div>

                    <div class="summary-toolbar">
                        <button type="button" class="summary-tool-btn">
                            <i class="fa-regular fa-pen-to-square"></i>
                            Bulk Actions
                            <i class="fa-solid fa-chevron-down"></i>
                        </button>
                        <div class="summary-tools-right">
                            <button type="button" class="summary-tool-btn">Save Filter</button>
                            <label class="compact-search" aria-label="Search leads">
                                <input type="search" placeholder="Search by number" data-reassign-lead-search>
                                <i class="fa-solid fa-magnifying-glass"></i>
                            </label>
                        </div>
                    </div>

                    <div class="reassign-summary-table">
                        <table>
                            <thead>
                                <tr>
                                    <th><input class="lead-check" type="checkbox" aria-label="Select all leads"></th>
                                    <th>No.</th>
                                    <th>Name</th>
                                    <th>Number</th>
                                    <th>Email</th>
                                    <th>Creation Date</th>
                                    <th>Updated at</th>
                                    <th>Lead Stage</th>
                                    <th>Tag</th>
                                    <th>User Assigned</th>
                                    <th>Follow-Up Time</th>
                                    <th>Lead Status</th>
                                    <th>Last Call Date</th>
                                    <th>Total Disposition Count</th>
                                    <th>Deal Amount</th>
                                    <th>Town/City</th>
                                    <th>State</th>
                                </tr>
                            </thead>
                            <tbody data-reassign-leads-body>
                                @foreach ([
                                    ['Jaiswal Gopal Traders', '7047246623', '16-May-2026 3:42 PM', '16-May-2026 3:42 PM', 'Fresh Leads', '', '30-May-2026 6:08 PM', 'In-Progress', '', '', 'Mandla', 'Madhya Pradesh'],
                                    ['Shingaji seeds agro', '9826141562', '11-May-2026 4:48 PM', '11-May-2026 4:48 PM', 'Fresh Leads', '', '', 'In-Progress', '', '', '', ''],
                                    ['Choudhary gokul', '9407592768', '6-May-2026 6:07 PM', '8-May-2026 7:00 PM', 'Follow Up (Mandatory)', 'Future Requirement', '20-May-2026 7:00 PM', 'In-Progress', '8-May-2026 7:00 PM', '1', '', ''],
                                    ['Vishwakarma ksk', '9407258277', '6-May-2026 12:43 PM', '8-May-2026 7:00 PM', 'Fresh Leads', '', '8-May-2026 8:00 PM', 'In-Progress', '8-May-2026 7:00 PM', '1', '', ''],
                                    ['Balaji traders aari', '9993575565', '5-May-2026 12:58 PM', '8-May-2026 12:09 PM', 'Fresh Leads', '', '8-May-2026 1:09 PM', 'In-Progress', '8-May-2026 12:09 PM', '1', '', ''],
                                    ['Agrawal ksk', '9826663183', '4-May-2026 11:35 AM', '16-May-2026 4:33 PM', 'Follow Up (Mandatory)', 'Future Requirement', '29-May-2026 4:33 PM', 'In-Progress', '16-May-2026 4:33 PM', '2', '', ''],
                                    ['Jain ksk', '9926484977', '4-May-2026 11:34 AM', '8-May-2026 7:00 PM', 'Follow Up (Mandatory)', 'Future Requirement', '28-May-2026 7:00 PM', 'In-Progress', '8-May-2026 7:00 PM', '1', '', ''],
                                    ['Dharmendra ksk', '9993086589', '2-May-2026 12:05 PM', '2-May-2026 12:05 PM', 'Fresh Leads', '', '', 'In-Progress', '', '', '', ''],
                                    ['Maa khad beej bhandar', '9399433019', '2-May-2026 12:03 PM', '2-May-2026 12:03 PM', 'Fresh Leads', '', '', 'In-Progress', '', '', '', ''],
                                    ['Ram traders', '8120967782', '2-May-2026 11:54 AM', '2-May-2026 11:54 AM', 'Fresh Leads', '', '', 'In-Progress', '', '', '', ''],
                                    ['Hanshwahini', '9713569775', '2-May-2026 11:52 AM', '2-May-2026 11:52 AM', 'Fresh Leads', '', '', 'In-Progress', '', '', '', ''],
                                    ['Mohit krishi seva kendra', '9981389859', '2-May-2026 11:18 AM', '2-May-2026 11:18 AM', 'Fresh Leads', '', '', 'In-Progress', '', '', '', ''],
                                    ['MAHADEV TRADERS NAGOD SATNA', '9993556655', '1-May-2026 6:07 PM', '16-May-2026 3:52 PM', 'Fresh Leads', '', '16-May-2026 4:52 PM', 'In-Progress', '16-May-2026 3:52 PM', '1', 'SATNA', 'MADHYA PRADESH'],
                                    ['VIKAS KRASHI SEWA KENDRA', '9977722489', '1-May-2026 6:07 PM', '4-May-2026 12:34 PM', 'Follow Up (Mandatory)', 'Future Requirement', '5-May-2026 12:34 PM', 'In-Progress', '4-May-2026 12:34 PM', '1', '', ''],
                                    ['M/S ASHISH KHAD BEEJ BHANDAR', '9926544431', '1-May-2026 6:07 PM', '11-May-2026 1:38 PM', 'Fresh Leads', '', '11-May-2026 2:38 PM', 'In-Progress', '11-May-2026 1:38 PM', '1', '', ''],
                                ] as $index => $lead)
                                    <tr>
                                        <td><input class="lead-check" type="checkbox" aria-label="Select lead {{ $index + 1 }}"></td>
                                        <td>{{ $index + 1 }}</td>
                                        <td><span class="ellipsis-lead">{{ $lead[0] }}</span></td>
                                        <td>{{ $lead[1] }}</td>
                                        <td></td>
                                        <td>{{ $lead[2] }}</td>
                                        <td>{{ $lead[3] }}</td>
                                        <td><span class="two-line-cell">{{ $lead[4] }}</span></td>
                                        <td><span class="two-line-cell">{{ $lead[5] }}</span></td>
                                        <td>Abhishek Thakur</td>
                                        <td>{{ $lead[6] }}</td>
                                        <td>{{ $lead[7] }}</td>
                                        <td>{{ $lead[8] }}</td>
                                        <td>{{ $lead[9] }}</td>
                                        <td></td>
                                        <td>{{ $lead[10] }}</td>
                                        <td>{{ $lead[11] }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>

            <section class="settings-panel" data-settings-panel="pipelines">
                <div class="pipeline-hero">
                    <div>
                        <div class="users-title-row">
                            <h2 class="users-title">Pipeline</h2>
                            <a href="https://docs.neodove.com/" target="_blank" class="learn-btn" rel="noopener">
                                <i class="fa-solid fa-book-open"></i>
                                Learn More
                            </a>
                        </div>
                        <p class="description">All the leads uploaded go through different stages until it is finally closed. Tags further provide easy identification of leads.</p>
                    </div>
                    <button type="button" class="action-btn primary" data-pipeline-modal-open="create">Create Pipeline</button>
                </div>

                <div class="pipeline-grid">
                    <article class="pipeline-card">
                        <div class="pipeline-card-head">
                            <label class="pipeline-select-wrap">
                                <span>Select Pipeline :</span>
                                <select class="pipeline-select" data-pipeline-select>
                                    <option>Loading pipelines...</option>
                                </select>
                            </label>
                            <button type="button" class="mini-btn" data-pipeline-modal-open="edit">Edit</button>
                            <button type="button" class="mini-btn primary" data-stage-create>Add Stage</button>
                        </div>
                        <div class="stage-flow" data-pipeline-stage-flow>
                            <div class="description">Loading saved stages...</div>
                        </div>
                    </article>

                    <article class="pipeline-card">
                        <div class="pipeline-card-head">
                            <strong>Edit Stage</strong>
                            <div>
                                <button type="button" class="mini-btn danger" data-stage-delete>Delete</button>
                                <button type="submit" form="calling-crm-stage-editor" class="mini-btn primary" data-stage-save>Save</button>
                            </div>
                        </div>
                        <form id="calling-crm-stage-editor" class="stage-editor-body" data-stage-editor-form>
                            <label>
                                <span class="input-label">Stage Name:</span>
                                <input class="editor-input" type="text" value="" data-stage-name-input>
                            </label>
                            <div>
                                <span class="input-label">Tags:</span>
                                <div class="tags-field" data-stage-tags-field>
                                    <input class="tag-input" type="text" placeholder="Add tags...">
                                </div>
                            </div>
                            <section class="stage-settings" data-stage-settings>
                                <button type="button" class="additional-setting" data-stage-settings-toggle aria-expanded="true">
                                    <span>Additional Setting</span>
                                    <i class="fa-solid fa-chevron-up"></i>
                                </button>
                                <div class="stage-settings-body" data-stage-settings-body>
                                    <strong>Transitions:</strong>
                                    <div class="stage-transition-list" data-stage-transition-list></div>
                                </div>
                            </section>
                        </form>
                    </article>
                </div>
            </section>

            <section class="settings-panel" data-settings-panel="profile">
                <form class="profile-form" data-profile-form>
                    <div class="users-title-row">
                        <h2 class="users-title">Profile</h2>
                        <a href="https://docs.neodove.com/" target="_blank" class="learn-btn" rel="noopener">
                            <i class="fa-solid fa-book-open"></i>
                            Learn More
                        </a>
                    </div>
                    <p class="description" style="margin-bottom:16px;">Organize company details, such as name, GST information, and address for invoicing purposes.</p>

                    <div class="profile-card">
                        <h3 class="settings-section-title">Company Details</h3>
                        <div class="profile-fields">
                            <label class="profile-row">
                                <span>Company Name:</span>
                                <input class="profile-control" type="text" value="" data-profile-field="business_name" required>
                            </label>
                            <label class="profile-row">
                                <span>Phone Number:</span>
                                <input class="profile-control" type="tel" value="" data-profile-field="phone" required>
                            </label>
                            <label class="profile-row top">
                                <span>Address:</span>
                                <span class="profile-text-wrap">
                                    <textarea class="profile-textarea" maxlength="250" data-profile-address data-profile-field="address" required></textarea>
                                    <span class="profile-count" data-profile-address-count>0/250</span>
                                </span>
                            </label>
                            <label class="profile-row">
                                <span>State:</span>
                                <select class="profile-select" data-profile-field="state" required>
                                    <option value="" disabled selected>Select State</option>
                                    <option value="Madhya Pradesh">Madhya Pradesh</option>
                                    <option value="Rajasthan">Rajasthan</option>
                                    <option value="Uttar Pradesh">Uttar Pradesh</option>
                                </select>
                            </label>
                            <label class="profile-row">
                                <span>Pincode:</span>
                                <input class="profile-control" type="text" value="" data-profile-field="pincode" required>
                            </label>
                        </div>

                        <h3 class="settings-section-title">Tax Details</h3>
                        <div class="profile-fields">
                            <label class="profile-row">
                                <span>GST No:</span>
                                <input class="profile-control" type="text" value="" data-profile-field="gst_number">
                            </label>
                        </div>

                        <h3 class="settings-section-title">Working hours</h3>
                        <div class="profile-row" style="margin-bottom:14px;">
                            <span>Manage Working Hours</span>
                            <button type="button" class="action-btn" data-working-hours-toggle>Update <i class="fa-solid fa-arrow-right"></i></button>
                        </div>
                        <div class="working-hours-panel" data-working-hours-panel>
                            <label>
                                <span>Working Days</span>
                                <select class="profile-select" data-profile-field="working_days">
                                    <option value="mon_sat">Monday - Saturday</option>
                                    <option value="mon_fri">Monday - Friday</option>
                                    <option value="all_days">All Days</option>
                                </select>
                            </label>
                            <label>
                                <span>Start Time</span>
                                <input class="profile-control" type="time" value="09:30" data-profile-field="work_start_time">
                            </label>
                            <label>
                                <span>End Time</span>
                                <input class="profile-control" type="time" value="18:30" data-profile-field="work_end_time">
                            </label>
                        </div>
                        <div class="profile-actions">
                            <button type="submit" class="modal-btn primary" data-profile-save>Save Profile</button>
                        </div>
                    </div>
                </form>
            </section>

            <section class="settings-panel" data-settings-panel="roles">
                <div class="users-title-row">
                    <h2 class="users-title">Roles and Permission</h2>
                </div>
                <p class="description" style="margin-bottom:16px;">Manage CRM access through the admin role and permission screens.</p>
                <div class="toolbar-actions" style="justify-content:flex-start;">
                    <a class="action-btn primary" href="{{ route('admin_panel.admin.role') }}">Manage Roles</a>
                    <a class="action-btn" href="{{ route('admin_panel.admin.permissions.index') }}">Manage Permissions</a>
                </div>
            </section>

            <section class="settings-panel" data-settings-panel="retry">
                <div class="users-title-row">
                    <h2 class="users-title">Retry Setting</h2>
                    <a href="https://docs.neodove.com/" target="_blank" class="learn-btn" rel="noopener">
                        <i class="fa-solid fa-book-open"></i>
                        Learn More
                    </a>
                    <button type="button" class="action-btn" data-retry-add>Add Reason</button>
                </div>
                <p class="description" style="margin-bottom:16px;">Manage unconnected call reasons and automate follow-ups with customizable intervals and retry counts.</p>

                <div class="retry-card" data-retry-table>
                    <div class="retry-head">
                        <span>Not Connected Reason</span>
                        <span>Retry</span>
                        <span>Logic</span>
                        <span>Action</span>
                    </div>
                    @for ($i = 0; $i < 5; $i++)
                        <div class="retry-row" data-retry-row>
                            <p class="retry-reason" style="width: 50%;"><span class="skeleton-loader" style="height: 16px; width: 80%;"></span></p>
                            <span style="width: 40px; display: inline-flex;"><span class="skeleton-loader" style="height: 20px; border-radius: 10px; width: 40px;"></span></span>
                            <span style="width: 60px; display: inline-flex;"><span class="skeleton-loader" style="height: 20px; width: 50px;"></span></span>
                            <span style="width: 24px; display: inline-flex;"><span class="skeleton-loader" style="height: 24px; border-radius: 50%; width: 24px;"></span></span>
                        </div>
                    @endfor
                    <div class="retry-note">
                        <i class="fa-regular fa-lightbulb"></i>
                        <span><strong>Note:</strong> Turning off the Retry toggle will mark your lead as closed on submission after first failed attempt itself.</span>
                    </div>
                </div>
            </section>

            <section class="settings-panel" data-settings-panel="priority">
                <div class="priority-shell">
                    <div class="users-title-row">
                        <h2 class="users-title">Lead Priority</h2>
                        <a href="https://docs.neodove.com/" target="_blank" class="learn-btn" rel="noopener">
                            <i class="fa-solid fa-book-open"></i>
                            Learn More
                        </a>
                    </div>
                    <p class="description" style="margin-bottom:16px;">Set the order in which leads are presented to users when they start calling.</p>

                    <div class="priority-card">
                        <div class="priority-layout">
                            <div class="priority-labels" aria-hidden="true">
                                <span>Highest Priority</span>
                                <span>High</span>
                                <span>Medium</span>
                                <span>Low</span>
                                <span>Lowest</span>
                            </div>
                            <div class="priority-list" data-priority-list>
                                <div class="priority-item priority-locked" data-priority-locked>Manually Scheduled Leads</div>
                                @for ($i = 0; $i < 4; $i++)
                                    <div class="priority-item" style="border-color: var(--calling-crm-border);">
                                        <span class="priority-handle" aria-hidden="true" style="opacity: 0.3;">
                                            <span></span><span></span><span></span><span></span><span></span><span></span>
                                        </span>
                                        <span class="priority-name" style="width: 70%;"><span class="skeleton-loader" style="height: 16px; width: 100%;"></span></span>
                                    </div>
                                @endfor
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <section class="settings-panel property-panel" data-settings-panel="properties">
                <div class="users-title-row">
                    <h2 class="users-title">Custom Contact Property</h2>
                </div>
                <p class="description">Create and customize lead properties that fit your business needs. Easily search, filter, and manage leads using criteria that matter to you.</p>

                <div class="table-card">
                    <div class="table-scroll">
                        <table class="property-table">
                            <thead>
                                <tr>
                                    <th>No.</th>
                                    <th>Property Name</th>
                                    <th>Data Type</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody data-property-table-body>
                                @for ($i = 0; $i < 5; $i++)
                                    <tr>
                                        <td><span class="skeleton-loader" style="width: 20px; height: 16px;"></span></td>
                                        <td><span class="skeleton-loader" style="width: 140px; height: 16px;"></span></td>
                                        <td><span class="skeleton-loader" style="width: 60px; height: 16px;"></span></td>
                                        <td>
                                            <span class="property-actions">
                                                <span class="skeleton-loader" style="width: 40px; height: 20px; border-radius: 10px; display: inline-block;"></span>
                                                <span class="skeleton-loader" style="width: 24px; height: 24px; border-radius: 50%; display: inline-block; margin-left: 8px;"></span>
                                                <span class="skeleton-loader" style="width: 24px; height: 24px; border-radius: 50%; display: inline-block; margin-left: 8px;"></span>
                                            </span>
                                        </td>
                                    </tr>
                                @endfor
                            </tbody>
                        </table>
                    </div>
                    <div class="property-table-note">
                        <i class="fa-regular fa-lightbulb"></i>
                        <span>You have added <strong data-property-count><span class="skeleton-loader" style="width: 24px; height: 14px; display: inline-block; vertical-align: middle;"></span>/40</strong> custom contact property.</span>
                    </div>
                </div>
                <button type="button" class="action-btn primary property-add" data-property-add>
                    <i class="fa-solid fa-plus"></i>
                    Add New Property
                </button>
            </section>
        </div>

        <div class="user-actions-menu" data-user-actions-menu>
            <button type="button" data-user-action="edit">Edit</button>
            <button type="button" data-user-action="deactivate">Deactivate</button>
            <button type="button" data-user-action="password">Change Password</button>
            <button type="button" data-user-action="campaigns">View Campaigns</button>
            <button type="button" data-user-action="reassign">Reassign Leads</button>
            <button type="button" data-user-action="disable">Disable Lead Assignment</button>
            <button type="button" data-user-action="delete">Delete</button>
        </div>

        <div class="crm-backdrop" data-add-user-modal aria-hidden="true">
            <form class="users-modal" data-add-user-form>
                <div class="users-modal-head">
                    <h2 class="modal-title">Add Users</h2>
                    <button type="button" class="quiet-btn">Add Bulk Users</button>
                </div>
                <div class="users-modal-body" data-user-rows>
                    <div class="user-form-row" data-user-form-row>
                        <input class="field-input" name="name[]" type="text" placeholder="User Name *" required>
                        <input class="field-input" name="number[]" type="tel" placeholder="Contact Number *" required>
                        <label class="password-field">
                            <input class="field-input" name="password[]" type="password" placeholder="Password *" required>
                            <button class="icon-field-btn" type="button" data-password-toggle aria-label="Show password"><i class="fa-solid fa-eye-slash"></i></button>
                        </label>
                        <select class="field-select" name="role[]" required>
                            <option value="">Role *</option>
                            <option value="subadmin">Admin / Team Lead</option>
                            <option value="agent">Executive</option>
                        </select>
                        <input class="field-input" name="email[]" type="email" placeholder="Email">
                        <input class="field-input" name="employee_id[]" type="text" placeholder="Employee Id">
                        <button class="remove-user-row" type="button" data-remove-user-row aria-label="Remove row"><i class="fa-solid fa-xmark"></i></button>
                    </div>
                </div>
                <div class="users-modal-footer">
                    <button type="button" class="modal-btn" data-add-user-row>Add Row</button>
                    <div class="footer-actions">
                        <button type="button" class="modal-btn" data-add-user-close>Cancel</button>
                        <button type="submit" class="modal-btn primary">Submit</button>
                    </div>
                </div>
            </form>
        </div>

        <div class="crm-backdrop" data-campaign-details-modal aria-hidden="true">
            <section class="campaign-details-modal" role="dialog" aria-modal="true" aria-labelledby="campaign-details-title">
                <div class="campaign-details-title" id="campaign-details-title">
                    <span data-campaign-user-name>Abhishek Thakur</span>&nbsp;- Campaign Details
                </div>
                <div class="campaign-details-body">
                    <div class="campaign-details-table">
                        <table>
                            <thead>
                                <tr>
                                    <th>No.</th>
                                    <th>Name <i class="fa-solid fa-arrow-up"></i></th>
                                    <th>Assigned Leads</th>
                                    <th>Un-assigned Leads</th>
                                    <th>Called Leads</th>
                                    <th>Rescheduled Leads</th>
                                    <th>Rescheduled by System</th>
                                    <th>Closed Leads <i class="fa-solid fa-arrow-up"></i></th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>1</td>
                                    <td><button type="button" class="campaign-link" data-campaign-modal-summary="MP Raw Data">MP Raw Data</button></td>
                                    <td>427</td>
                                    <td>0</td>
                                    <td>0</td>
                                    <td>167</td>
                                    <td>659</td>
                                    <td>0</td>
                                </tr>
                                <tr>
                                    <td>2</td>
                                    <td><button type="button" class="campaign-link" data-campaign-modal-summary="MP Transacted">MP Transacted</button></td>
                                    <td>48</td>
                                    <td>0</td>
                                    <td>0</td>
                                    <td>9</td>
                                    <td>27</td>
                                    <td>0</td>
                                </tr>
                            </tbody>
                        </table>
                        <div class="campaign-modal-pager">
                            <span>Items per page:</span>
                            <span class="pager-size">10 <i class="fa-solid fa-caret-down"></i></span>
                            <span>1 - 2 of 2</span>
                            <button type="button" class="pager-chevron" aria-label="Previous page"><i class="fa-solid fa-chevron-left"></i></button>
                            <button type="button" class="pager-chevron" aria-label="Next page"><i class="fa-solid fa-chevron-right"></i></button>
                        </div>
                    </div>
                </div>
                <div class="campaign-details-actions">
                    <button type="button" class="campaign-ok-btn" data-campaign-details-close>OK</button>
                </div>
            </section>
        </div>

        <div class="crm-backdrop" data-pipeline-modal aria-hidden="true">
            <form class="pipeline-modal" data-pipeline-form>
                <div class="pipeline-modal-head">
                    <div class="pipeline-modal-title">
                        <span data-pipeline-modal-title>Add Pipeline</span>
                        <a href="https://docs.neodove.com/" target="_blank" rel="noopener" class="modal-learn"><i class="fa-solid fa-play"></i> Learn More</a>
                    </div>
                    <button type="button" class="modal-close" data-pipeline-modal-close aria-label="Close">&times;</button>
                </div>
                <label class="pipeline-modal-field">
                    <span>Pipeline Name *</span>
                    <input class="pipeline-name-input" type="text" value="New Pipeline" data-pipeline-name-input required>
                </label>
                <div class="color-select-row">
                    <strong>Select Color:</strong>
                    <span class="color-select"><span class="color-dot"></span><i class="fa-solid fa-chevron-down"></i></span>
                </div>
                <div class="pipeline-modal-actions">
                    <button type="submit" class="modal-btn primary" data-pipeline-submit>Create</button>
                </div>
            </form>
        </div>

        <div class="crm-backdrop" data-stage-modal aria-hidden="true">
            <form class="pipeline-modal" data-stage-form>
                <div class="pipeline-modal-head">
                    <div class="pipeline-modal-title">Add Stage</div>
                    <button type="button" class="modal-close" data-stage-modal-close aria-label="Close">&times;</button>
                </div>
                <label class="pipeline-modal-field">
                    <span>Stage Name *</span>
                    <input class="pipeline-name-input" type="text" data-stage-create-name required>
                </label>
                <div class="pipeline-modal-actions">
                    <button type="button" class="modal-btn" data-stage-modal-close>Cancel</button>
                    <button type="submit" class="modal-btn primary">Create</button>
                </div>
            </form>
        </div>

        <div class="crm-backdrop" data-tag-modal aria-hidden="true">
            <form class="pipeline-modal" data-tag-form>
                <div class="pipeline-modal-head">
                    <div class="pipeline-modal-title">Edit Tag</div>
                    <button type="button" class="modal-close" data-tag-modal-close aria-label="Close">&times;</button>
                </div>
                <label class="pipeline-modal-field">
                    <span>Tag Name *</span>
                    <input class="pipeline-name-input" type="text" data-tag-name-input required>
                </label>
                <div class="pipeline-modal-actions">
                    <button type="button" class="modal-btn danger" data-tag-delete>Delete</button>
                    <button type="submit" class="modal-btn primary">Update</button>
                </div>
            </form>
        </div>

        <div class="retry-menu" data-retry-menu>
            <button type="button" data-retry-menu-action="edit">Edit</button>
            <button type="button" data-retry-menu-action="delete">Delete</button>
        </div>

        <div class="crm-backdrop" data-retry-logic-modal aria-hidden="true">
            <form class="logic-modal" data-retry-logic-form>
                <div class="logic-head">
                    <h2>Logic</h2>
                    <a href="https://docs.neodove.com/" target="_blank" rel="noopener" class="modal-learn"><i class="fa-solid fa-play"></i> Learn More</a>
                </div>
                <p class="logic-copy">Select the retry strategy for Not Connected calls</p>
                <div class="logic-types">
                    <label><input type="radio" name="retry_logic_type" value="Fixed" checked> Fixed</label>
                    <label><input type="radio" name="retry_logic_type" value="Variable"> Variable</label>
                </div>
                <div class="logic-rule">
                    <span>Retry a maximum of</span>
                    <input class="logic-number" type="number" min="1" max="99" value="5" data-retry-count required>
                    <span>times, with an interval of</span>
                    <input class="logic-number" type="number" min="1" max="99" value="1" data-retry-interval required>
                    <select class="logic-unit" data-retry-unit>
                        <option>Hours</option>
                        <option>Days</option>
                        <option>Minutes</option>
                    </select>
                </div>
                <div class="logic-note">
                    <i class="fa-regular fa-lightbulb"></i>
                    <span>After last failed retry, lead will automatically be marked as lost by the system</span>
                </div>
                <div class="logic-footer">
                    <label class="logic-apply"><input type="checkbox" data-retry-apply-all> Apply this logic to other reasons as well <i class="fa-solid fa-circle-info" style="color:#c5b4ed;"></i></label>
                    <div class="footer-actions">
                        <button type="button" class="modal-btn" data-retry-logic-close>Cancel</button>
                        <button type="submit" class="modal-btn primary">Update</button>
                    </div>
                </div>
            </form>
        </div>

        <div class="crm-backdrop" data-retry-reason-modal aria-hidden="true">
            <form class="reason-modal" data-retry-reason-form>
                <h2>Edit Reason</h2>
                <label class="reason-field">
                    <span>Name</span>
                    <input class="reason-input" type="text" data-retry-reason-input required>
                </label>
                <div class="reason-actions">
                    <button type="button" class="modal-btn" data-retry-reason-close>Cancel</button>
                    <button type="submit" class="modal-btn primary">Update</button>
                </div>
            </form>
        </div>

        <div class="crm-backdrop" data-property-modal aria-hidden="true">
            <form class="property-modal" data-property-form>
                <div class="property-modal-head">
                    <h2 class="property-modal-title" data-property-modal-title>Add Custom Property</h2>
                    <button type="button" class="modal-close" data-property-close aria-label="Close">&times;</button>
                </div>
                <div class="property-fields">
                    <label class="property-field">
                        <span>Property Name</span>
                        <span class="property-control-wrap">
                            <input class="property-control" type="text" maxlength="60" placeholder="Enter" data-property-name-input required>
                            <span class="property-name-count" data-property-name-count>0/60</span>
                        </span>
                    </label>
                    <label class="property-field">
                        <span>Data Type</span>
                        <select class="property-control" data-property-type-input required>
                            <option value="">Select</option>
                            <option value="text">Text</option>
                            <option value="number">Number</option>
                            <option value="email">Email</option>
                            <option value="date">Date</option>
                        </select>
                    </label>
                </div>
                <div class="property-modal-actions">
                    <button type="submit" class="modal-btn primary">Save</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        (() => {
            const settings = document.querySelector('.calling-crm-settings');
            const tabsTrack = settings?.querySelector('.tabs-track');
            const tabArrows = settings?.querySelectorAll('.tab-arrow');

            if (!tabsTrack || tabArrows?.length !== 2) return;

            const [previousArrow, nextArrow] = tabArrows;
            const tabButtons = settings.querySelectorAll('[data-settings-tab]');
            const tabPanels = settings.querySelectorAll('[data-settings-panel]');
            const addUserModal = settings.querySelector('[data-add-user-modal]');
            const modalBackdrops = settings.querySelectorAll('.crm-backdrop');
            const addUserForm = settings.querySelector('[data-add-user-form]');
            const userRows = settings.querySelector('[data-user-rows]');
            const usersTableBody = settings.querySelector('[data-users-table-body]');
            const userActionsMenu = settings.querySelector('[data-user-actions-menu]');
            const campaignDetailsModal = settings.querySelector('[data-campaign-details-modal]');
            const campaignUserName = settings.querySelector('[data-campaign-user-name]');
            const usersFlowViews = settings.querySelectorAll('[data-users-flow]');
            const reassignCampaignBody = settings.querySelector('[data-reassign-campaign-body]');
            const reassignLeadsBody = settings.querySelector('[data-reassign-leads-body]');
            const summaryTitleCampaign = settings.querySelector('[data-summary-title-campaign]');
            const pipelineModal = settings.querySelector('[data-pipeline-modal]');
            const pipelineForm = settings.querySelector('[data-pipeline-form]');
            const pipelineSelect = settings.querySelector('[data-pipeline-select]');
            const pipelineNameInput = settings.querySelector('[data-pipeline-name-input]');
            const pipelineModalTitle = settings.querySelector('[data-pipeline-modal-title]');
            const pipelineSubmit = settings.querySelector('[data-pipeline-submit]');
            const stageNameInput = settings.querySelector('[data-stage-name-input]');
            const stageTagsField = settings.querySelector('[data-stage-tags-field]');
            const stageSave = settings.querySelector('[data-stage-save]');
            const profileForm = settings.querySelector('[data-profile-form]');
            const profileAddress = settings.querySelector('[data-profile-address]');
            const profileAddressCount = settings.querySelector('[data-profile-address-count]');
            const workingHoursPanel = settings.querySelector('[data-working-hours-panel]');
            const retryTable = settings.querySelector('[data-retry-table]');
            const retryMenu = settings.querySelector('[data-retry-menu]');
            const retryLogicModal = settings.querySelector('[data-retry-logic-modal]');
            const retryLogicForm = settings.querySelector('[data-retry-logic-form]');
            const retryReasonModal = settings.querySelector('[data-retry-reason-modal]');
            const retryReasonForm = settings.querySelector('[data-retry-reason-form]');
            const retryReasonInput = settings.querySelector('[data-retry-reason-input]');
            const retryCount = settings.querySelector('[data-retry-count]');
            const retryInterval = settings.querySelector('[data-retry-interval]');
            const retryUnit = settings.querySelector('[data-retry-unit]');
            const priorityList = settings.querySelector('[data-priority-list]');
            const propertyTableBody = settings.querySelector('[data-property-table-body]');
            const propertyCount = settings.querySelector('[data-property-count]');
            const propertyModal = settings.querySelector('[data-property-modal]');
            const propertyForm = settings.querySelector('[data-property-form]');
            const propertyModalTitle = settings.querySelector('[data-property-modal-title]');
            const propertyNameInput = settings.querySelector('[data-property-name-input]');
            const propertyNameCount = settings.querySelector('[data-property-name-count]');
            const propertyTypeInput = settings.querySelector('[data-property-type-input]');
            let activeUserActionButton = null;
            let editingUserRow = null;
            let pipelineModalMode = 'create';
            let activeRetryRow = null;
            let draggingPriorityItem = null;
            let priorityDragGhost = null;
            let activePropertyRow = null;

            const scrollStep = () => Math.max(220, Math.round(tabsTrack.clientWidth * .72));

            const updateTabArrows = () => {
                const scrollEnd = tabsTrack.scrollWidth - tabsTrack.clientWidth;
                previousArrow.disabled = tabsTrack.scrollLeft <= 1;
                nextArrow.disabled = tabsTrack.scrollLeft >= scrollEnd - 1;
            };

            previousArrow.addEventListener('click', () => {
                tabsTrack.scrollBy({ left: -scrollStep(), behavior: 'smooth' });
            });

            nextArrow.addEventListener('click', () => {
                tabsTrack.scrollBy({ left: scrollStep(), behavior: 'smooth' });
            });

            tabsTrack.addEventListener('scroll', updateTabArrows, { passive: true });
            window.addEventListener('resize', updateTabArrows);
            updateTabArrows();

            const setScrollLock = () => {
                const modalOpen = addUserModal?.classList.contains('open')
                    || campaignDetailsModal?.classList.contains('open')
                    || pipelineModal?.classList.contains('open')
                    || retryLogicModal?.classList.contains('open')
                    || retryReasonModal?.classList.contains('open')
                    || propertyModal?.classList.contains('open');
                document.body.style.overflow = modalOpen ? 'hidden' : '';
            };

            const setUsersFlow = flowName => {
                usersFlowViews.forEach(view => view.classList.toggle('active', view.dataset.usersFlow === flowName));
            };

            const selectSummaryCampaign = campaignName => {
                const currentCampaign = campaignName || 'MP Transacted';
                if (summaryTitleCampaign) summaryTitleCampaign.textContent = currentCampaign;
                settings.querySelectorAll('[data-summary-campaign-card]').forEach(card => {
                    card.classList.toggle('active', card.dataset.summaryCampaignCard === currentCampaign);
                });
            };

            const openReassignSummary = campaignName => {
                selectSummaryCampaign(campaignName);
                setUsersFlow('reassign-summary');
            };

            const renumberUsers = () => {
                usersTableBody?.querySelectorAll('tr').forEach((row, index) => {
                    row.cells[0].textContent = String(index + 1);
                });
            };

            const makeUserActionButton = name => {
                const button = document.createElement('button');
                button.type = 'button';
                button.className = 'dots-btn';
                button.dataset.userActionsToggle = '';
                button.dataset.userName = name;
                button.setAttribute('aria-label', 'User actions');
                button.innerHTML = '<i class="fa-solid fa-ellipsis-vertical"></i>';
                return button;
            };

            const resetUserRow = row => {
                row.querySelectorAll('input').forEach(input => input.value = '');
                row.querySelectorAll('select').forEach(select => select.value = '');
                const password = row.querySelector('input[name="password[]"]');
                const icon = row.querySelector('[data-password-toggle] i');
                if (password) password.type = 'password';
                if (icon) icon.className = 'fa-solid fa-eye-slash';
            };

            const addBlankUserRow = () => {
                const source = userRows?.querySelector('[data-user-form-row]');
                if (!source || !userRows) return;
                const row = source.cloneNode(true);
                resetUserRow(row);
                userRows.append(row);
            };

            const fillUserFormForEdit = row => {
                const formRow = userRows?.querySelector('[data-user-form-row]');
                if (!formRow || !row) return;
                while (userRows.children.length > 1) userRows.lastElementChild.remove();
                resetUserRow(formRow);
                formRow.querySelector('input[name="name[]"]').value = row.cells[1].textContent.trim();
                formRow.querySelector('input[name="number[]"]').value = row.cells[2].textContent.trim();
                formRow.querySelector('select[name="role[]"]').value = row.cells[5].textContent.trim();
                formRow.querySelector('input[name="email[]"]').value = row.cells[4].textContent.trim();
                formRow.querySelector('input[name="password[]"]').required = false;
            };

            const openUserModal = row => {
                editingUserRow = row || null;
                if (row) {
                    fillUserFormForEdit(row);
                } else {
                    userRows?.querySelectorAll('[data-user-form-row]').forEach((formRow, index) => {
                        if (index) formRow.remove();
                        else {
                            resetUserRow(formRow);
                            formRow.querySelector('input[name="password[]"]').required = true;
                        }
                    });
                }
                addUserModal?.classList.add('open');
                addUserModal?.setAttribute('aria-hidden', 'false');
                setScrollLock();
                addUserModal?.querySelector('input[name="name[]"]')?.focus();
            };

            const closeUserModal = () => {
                editingUserRow = null;
                addUserModal?.classList.remove('open');
                addUserModal?.setAttribute('aria-hidden', 'true');
                setScrollLock();
            };

            const openCampaignDetailsModal = row => {
                if (campaignUserName) campaignUserName.textContent = row?.cells[1]?.textContent.trim() || 'User';
                campaignDetailsModal?.classList.add('open');
                campaignDetailsModal?.setAttribute('aria-hidden', 'false');
                setScrollLock();
            };

            const closeCampaignDetailsModal = () => {
                campaignDetailsModal?.classList.remove('open');
                campaignDetailsModal?.setAttribute('aria-hidden', 'true');
                setScrollLock();
            };

            const appendUserRow = formRow => {
                if (!usersTableBody) return;
                const values = {
                    name: formRow.querySelector('input[name="name[]"]').value.trim(),
                    number: formRow.querySelector('input[name="number[]"]').value.trim(),
                    email: formRow.querySelector('input[name="email[]"]').value.trim(),
                    role: formRow.querySelector('select[name="role[]"]').value.trim(),
                };
                const row = usersTableBody.insertRow();
                ['', values.name, values.number, '', values.email, values.role, '10-10-2026'].forEach(value => {
                    row.insertCell().textContent = value;
                });
                const statusCell = row.insertCell();
                const status = document.createElement('span');
                status.className = 'status-pill';
                status.textContent = 'Active';
                statusCell.append(status);
                row.insertCell().append(makeUserActionButton(values.name));
            };

            const updateEditedUser = formRow => {
                if (!editingUserRow) return;
                const name = formRow.querySelector('input[name="name[]"]').value.trim();
                editingUserRow.cells[1].textContent = name;
                editingUserRow.cells[2].textContent = formRow.querySelector('input[name="number[]"]').value.trim();
                editingUserRow.cells[4].textContent = formRow.querySelector('input[name="email[]"]').value.trim();
                editingUserRow.cells[5].textContent = formRow.querySelector('select[name="role[]"]').value.trim();
                const actionButton = editingUserRow.querySelector('[data-user-actions-toggle]');
                if (actionButton) actionButton.dataset.userName = name;
            };

            const closeUserActions = () => {
                userActionsMenu?.classList.remove('open');
                activeUserActionButton = null;
            };

            const openUserActions = button => {
                if (!userActionsMenu) return;
                activeUserActionButton = button;
                const rect = button.getBoundingClientRect();
                const width = 260;
                const height = 383;
                const left = Math.min(Math.max(4, rect.left - width + rect.width), window.innerWidth - width - 8);
                const top = Math.min(Math.max(8, rect.bottom - 10), window.innerHeight - height - 8);
                userActionsMenu.style.left = `${left}px`;
                userActionsMenu.style.top = `${top}px`;
                userActionsMenu.classList.add('open');
            };

            const setPanel = panelName => {
                const panel = settings.querySelector(`[data-settings-panel="${panelName}"]`);
                if (!panel) return;
                tabButtons.forEach(button => button.classList.toggle('active', button.dataset.settingsTab === panelName));
                tabPanels.forEach(tabPanel => tabPanel.classList.toggle('active', tabPanel === panel));
                if (panelName === 'users') setUsersFlow('list');
                closeUserActions();
            };

            tabButtons.forEach(button => {
                button.addEventListener('click', () => setPanel(button.dataset.settingsTab));
            });

            settings.querySelector('[data-add-user-open]')?.addEventListener('click', () => openUserModal());
            settings.querySelector('[data-add-user-row]')?.addEventListener('click', addBlankUserRow);
            settings.querySelector('[data-add-user-close]')?.addEventListener('click', closeUserModal);

            addUserModal?.addEventListener('click', event => {
                if (event.target === addUserModal) closeUserModal();
            });

            campaignDetailsModal?.addEventListener('click', event => {
                if (event.target === campaignDetailsModal) closeCampaignDetailsModal();
            });

            settings.querySelector('[data-campaign-details-close]')?.addEventListener('click', closeCampaignDetailsModal);
            settings.querySelector('[data-reassign-back]')?.addEventListener('click', () => setUsersFlow('list'));
            settings.querySelector('[data-summary-back]')?.addEventListener('click', () => setUsersFlow('reassign-campaigns'));

            settings.querySelectorAll('[data-reassign-summary-open]').forEach(button => {
                button.addEventListener('click', () => openReassignSummary(button.dataset.summaryCampaign));
            });

            settings.querySelectorAll('[data-summary-campaign-card]').forEach(card => {
                card.addEventListener('click', () => selectSummaryCampaign(card.dataset.summaryCampaignCard));
            });

            settings.querySelectorAll('[data-campaign-modal-summary]').forEach(button => {
                button.addEventListener('click', () => {
                    closeCampaignDetailsModal();
                    openReassignSummary(button.dataset.campaignModalSummary);
                });
            });

            const filterTableRows = (body, searchValue) => {
                const query = searchValue.trim().toLowerCase();
                body?.querySelectorAll('tr').forEach(row => {
                    row.hidden = query !== '' && !row.textContent.toLowerCase().includes(query);
                });
            };

            settings.querySelector('[data-reassign-campaign-search]')?.addEventListener('input', event => {
                filterTableRows(reassignCampaignBody, event.target.value);
            });

            settings.querySelector('[data-reassign-lead-search]')?.addEventListener('input', event => {
                filterTableRows(reassignLeadsBody, event.target.value);
            });

            userRows?.addEventListener('click', event => {
                const passwordToggle = event.target.closest('[data-password-toggle]');
                if (passwordToggle) {
                    const passwordInput = passwordToggle.closest('.password-field')?.querySelector('input');
                    const icon = passwordToggle.querySelector('i');
                    if (!passwordInput || !icon) return;
                    const reveal = passwordInput.type === 'password';
                    passwordInput.type = reveal ? 'text' : 'password';
                    icon.className = reveal ? 'fa-solid fa-eye' : 'fa-solid fa-eye-slash';
                    return;
                }

                const removeButton = event.target.closest('[data-remove-user-row]');
                if (removeButton) {
                    const rows = userRows.querySelectorAll('[data-user-form-row]');
                    if (rows.length > 1) removeButton.closest('[data-user-form-row]')?.remove();
                    else resetUserRow(rows[0]);
                }
            });

            addUserForm?.addEventListener('submit', event => {
                event.preventDefault();
                if (!addUserForm.reportValidity()) return;
                const rows = [...userRows.querySelectorAll('[data-user-form-row]')];
                if (editingUserRow) updateEditedUser(rows[0]);
                else rows.forEach(appendUserRow);
                renumberUsers();
                closeUserModal();
            });

            usersTableBody?.addEventListener('click', event => {
                const toggle = event.target.closest('[data-user-actions-toggle]');
                if (!toggle) return;
                event.stopPropagation();
                if (toggle === activeUserActionButton && userActionsMenu?.classList.contains('open')) closeUserActions();
                else openUserActions(toggle);
            });

            userActionsMenu?.addEventListener('click', event => {
                const action = event.target.closest('[data-user-action]')?.dataset.userAction;
                const row = activeUserActionButton?.closest('tr');
                if (!action || !row) return;

                if (action === 'edit' || action === 'password') openUserModal(row);
                if (action === 'campaigns') openCampaignDetailsModal(row);
                if (action === 'reassign') setUsersFlow('reassign-campaigns');
                if (action === 'deactivate' || action === 'disable') {
                    const status = row.querySelector('.status-pill');
                    if (status) {
                        status.textContent = status.textContent.trim() === 'Active' ? 'Inactive' : 'Active';
                    }
                }
                if (action === 'delete') {
                    row.remove();
                    renumberUsers();
                }
                closeUserActions();
            });

            const renderStageTags = tags => {
                if (!stageTagsField) return;
                stageTagsField.querySelectorAll('.tag-chip').forEach(chip => chip.remove());
                const tagInput = stageTagsField.querySelector('.tag-input');
                tags.forEach(tag => {
                    const chip = document.createElement('button');
                    chip.type = 'button';
                    chip.className = 'tag-chip';
                    chip.textContent = tag;
                    stageTagsField.insertBefore(chip, tagInput);
                });
            };

            // Mock stage event listeners removed as they are now handled by api-bindings.blade.php


            const openPipelineModal = mode => {
                pipelineModalMode = mode;
                const isEdit = mode === 'edit';
                if (pipelineModalTitle) pipelineModalTitle.textContent = isEdit ? 'Edit Pipeline' : 'Add Pipeline';
                if (pipelineSubmit) pipelineSubmit.textContent = isEdit ? 'Update' : 'Create';
                if (pipelineNameInput) pipelineNameInput.value = isEdit ? pipelineSelect?.selectedOptions[0]?.textContent.trim() || '' : 'New Pipeline';
                pipelineModal?.classList.add('open');
                pipelineModal?.setAttribute('aria-hidden', 'false');
                setScrollLock();
                pipelineNameInput?.focus();
            };

            settings.querySelectorAll('[data-pipeline-modal-open]').forEach(button => {
                button.addEventListener('click', () => openPipelineModal(button.dataset.pipelineModalOpen));
            });

            const closePipelineModal = () => {
                pipelineModal?.classList.remove('open');
                pipelineModal?.setAttribute('aria-hidden', 'true');
                setScrollLock();
            };

            settings.querySelector('[data-pipeline-modal-close]')?.addEventListener('click', closePipelineModal);
            pipelineModal?.addEventListener('click', event => {
                if (event.target === pipelineModal) closePipelineModal();
            });

            const updateAddressCount = () => {
                if (!profileAddress || !profileAddressCount) return;
                profileAddressCount.textContent = `${profileAddress.value.length}/250`;
            };

            profileAddress?.addEventListener('input', updateAddressCount);
            updateAddressCount();

            settings.querySelector('[data-working-hours-toggle]')?.addEventListener('click', () => {
                workingHoursPanel?.classList.toggle('open');
            });

            profileForm?.addEventListener('submit', event => {
                event.preventDefault();
                if (!profileForm.reportValidity()) return;
                const button = settings.querySelector('[data-profile-save]');
                if (!button) return;
                const label = button.textContent;
                button.textContent = 'Saved';
                window.setTimeout(() => {
                    button.textContent = label;
                }, 1200);
            });

            const updatePropertyCount = () => {
                const rows = propertyTableBody?.querySelectorAll('[data-property-row]').length || 0;
                if (propertyCount) propertyCount.textContent = `${rows}/40`;
            };

            const renumberProperties = () => {
                propertyTableBody?.querySelectorAll('[data-property-row]').forEach((row, index) => {
                    row.cells[0].textContent = String(index + 1);
                });
                updatePropertyCount();
            };

            const updatePropertyNameCount = () => {
                if (!propertyNameInput || !propertyNameCount) return;
                propertyNameCount.textContent = `${propertyNameInput.value.length}/60`;
            };

            const openPropertyModal = row => {
                activePropertyRow = row || null;
                const isEdit = Boolean(row);
                if (propertyModalTitle) propertyModalTitle.textContent = isEdit ? 'Edit Custom Column' : 'Add Custom Property';
                if (propertyNameInput) {
                    propertyNameInput.value = row?.querySelector('[data-property-name]')?.textContent.trim() || '';
                    propertyNameInput.placeholder = isEdit ? '' : 'Enter';
                }
                if (propertyTypeInput) {
                    propertyTypeInput.disabled = isEdit;
                    propertyTypeInput.value = row?.querySelector('[data-property-type]')?.textContent.trim() || '';
                }
                updatePropertyNameCount();
                propertyModal?.classList.add('open');
                propertyModal?.setAttribute('aria-hidden', 'false');
                setScrollLock();
                propertyNameInput?.focus();
            };

            const closePropertyModal = () => {
                activePropertyRow = null;
                propertyModal?.classList.remove('open');
                propertyModal?.setAttribute('aria-hidden', 'true');
                if (propertyTypeInput) propertyTypeInput.disabled = false;
                setScrollLock();
            };

            const buildPropertyActions = name => {
                const actions = document.createElement('span');
                actions.className = 'property-actions';
                actions.innerHTML = `
                    <label class="retry-switch" aria-label="Toggle property">
                        <input type="checkbox" checked>
                        <span></span>
                    </label>
                    <button type="button" class="property-icon-btn" data-property-edit aria-label="Edit property">
                        <i class="fa-solid fa-pencil"></i>
                    </button>
                    <button type="button" class="property-icon-btn" data-property-delete aria-label="Delete property">
                        <i class="fa-solid fa-trash"></i>
                    </button>
                `;
                actions.querySelector('[data-property-edit]')?.setAttribute('aria-label', `Edit ${name}`);
                actions.querySelector('[data-property-delete]')?.setAttribute('aria-label', `Delete ${name}`);
                actions.querySelector('.retry-switch')?.setAttribute('aria-label', `Toggle ${name}`);
                return actions;
            };

            const appendPropertyRow = (name, type) => {
                if (!propertyTableBody) return;
                const row = propertyTableBody.insertRow();
                row.dataset.propertyRow = '';
                row.insertCell();
                const nameCell = row.insertCell();
                nameCell.dataset.propertyName = '';
                nameCell.textContent = name;
                const typeCell = row.insertCell();
                typeCell.dataset.propertyType = '';
                typeCell.textContent = type;
                row.insertCell().append(buildPropertyActions(name));
                renumberProperties();
            };

            settings.querySelector('[data-property-add]')?.addEventListener('click', () => openPropertyModal());
            settings.querySelector('[data-property-close]')?.addEventListener('click', closePropertyModal);
            propertyNameInput?.addEventListener('input', updatePropertyNameCount);

            propertyModal?.addEventListener('click', event => {
                if (event.target === propertyModal) closePropertyModal();
            });

            propertyTableBody?.addEventListener('click', event => {
                const row = event.target.closest('[data-property-row]');
                if (!row) return;
                if (event.target.closest('[data-property-edit]')) {
                    openPropertyModal(row);
                }
                if (event.target.closest('[data-property-delete]')) {
                    row.remove();
                    renumberProperties();
                }
            });

            propertyForm?.addEventListener('submit', event => {
                event.preventDefault();
                if (!propertyForm.reportValidity()) return;
                const name = propertyNameInput?.value.trim();
                const type = propertyTypeInput?.value;
                if (!name || !type) return;
                if (activePropertyRow) {
                    const nameCell = activePropertyRow.querySelector('[data-property-name]');
                    if (nameCell) nameCell.textContent = name;
                    activePropertyRow.querySelector('[data-property-edit]')?.setAttribute('aria-label', `Edit ${name}`);
                    activePropertyRow.querySelector('[data-property-delete]')?.setAttribute('aria-label', `Delete ${name}`);
                    activePropertyRow.querySelector('.retry-switch')?.setAttribute('aria-label', `Toggle ${name}`);
                } else {
                    appendPropertyRow(name, type);
                }
                closePropertyModal();
            });

            updatePropertyCount();

            const openRetryLogicModal = row => {
                activeRetryRow = row || activeRetryRow;
                retryLogicModal?.classList.add('open');
                retryLogicModal?.setAttribute('aria-hidden', 'false');
                setScrollLock();
                retryCount?.focus();
            };

            const closeRetryLogicModal = () => {
                retryLogicModal?.classList.remove('open');
                retryLogicModal?.setAttribute('aria-hidden', 'true');
                setScrollLock();
            };

            const openRetryReasonModal = row => {
                activeRetryRow = row || activeRetryRow;
                if (retryReasonInput) {
                    retryReasonInput.value = activeRetryRow?.querySelector('.retry-reason')?.textContent.trim() || '';
                }
                retryReasonModal?.classList.add('open');
                retryReasonModal?.setAttribute('aria-hidden', 'false');
                setScrollLock();
                retryReasonInput?.focus();
            };

            const closeRetryReasonModal = () => {
                retryReasonModal?.classList.remove('open');
                retryReasonModal?.setAttribute('aria-hidden', 'true');
                setScrollLock();
            };

            const closeRetryMenu = () => {
                retryMenu?.classList.remove('open');
            };

            const openRetryMenu = (button, row) => {
                if (!retryMenu) return;
                activeRetryRow = row;
                const rect = button.getBoundingClientRect();
                const width = 104;
                const height = 90;
                retryMenu.style.left = `${Math.min(Math.max(8, rect.right - width + 12), window.innerWidth - width - 8)}px`;
                retryMenu.style.top = `${Math.min(Math.max(8, rect.bottom - 6), window.innerHeight - height - 8)}px`;
                retryMenu.classList.add('open');
            };

            retryTable?.addEventListener('click', event => {
                const setup = event.target.closest('[data-retry-setup]');
                if (setup) {
                    openRetryLogicModal(setup.closest('[data-retry-row]'));
                    return;
                }

                const menuToggle = event.target.closest('[data-retry-action-toggle]');
                if (menuToggle) {
                    event.stopPropagation();
                    const row = menuToggle.closest('[data-retry-row]');
                    if (retryMenu?.classList.contains('open') && activeRetryRow === row) closeRetryMenu();
                    else openRetryMenu(menuToggle, row);
                }
            });

            retryMenu?.addEventListener('click', event => {
                const action = event.target.closest('[data-retry-menu-action]')?.dataset.retryMenuAction;
                if (!action || !activeRetryRow) return;
                if (action === 'edit') openRetryReasonModal(activeRetryRow);
                if (action === 'delete') activeRetryRow.remove();
                closeRetryMenu();
            });

            retryLogicModal?.addEventListener('click', event => {
                if (event.target === retryLogicModal || event.target.closest('[data-retry-logic-close]')) {
                    closeRetryLogicModal();
                }
            });

            retryLogicForm?.addEventListener('submit', event => {
                event.preventDefault();
                if (!retryLogicForm.reportValidity()) return;
                const rows = settings.querySelector('[data-retry-apply-all]')?.checked
                    ? settings.querySelectorAll('[data-retry-row]')
                    : [activeRetryRow].filter(Boolean);
                rows.forEach(row => {
                    row.dataset.retryLogic = `${retryCount?.value || 5} times / ${retryInterval?.value || 1} ${retryUnit?.value || 'Hours'}`;
                    const toggle = row.querySelector('[data-retry-toggle]');
                    if (toggle) toggle.checked = true;
                });
                closeRetryLogicModal();
            });

            retryReasonModal?.addEventListener('click', event => {
                if (event.target === retryReasonModal || event.target.closest('[data-retry-reason-close]')) {
                    closeRetryReasonModal();
                }
            });

            retryReasonForm?.addEventListener('submit', event => {
                event.preventDefault();
                if (!activeRetryRow || !retryReasonForm.reportValidity()) return;
                const name = retryReasonInput?.value.trim();
                const reasonLabel = activeRetryRow.querySelector('.retry-reason');
                if (!name || !reasonLabel) return;
                reasonLabel.textContent = name;
                activeRetryRow.dataset.retryReason = name;
                closeRetryReasonModal();
            });

            const getPriorityDropTarget = (list, y) => {
                const candidates = [...list.querySelectorAll('[data-priority-item]:not(.dragging)')];
                return candidates.reduce((closest, item) => {
                    const rect = item.getBoundingClientRect();
                    const offset = y - rect.top - rect.height / 2;
                    if (offset < 0 && offset > closest.offset) return { offset, item };
                    return closest;
                }, { offset: Number.NEGATIVE_INFINITY, item: null }).item;
            };

            const clearPriorityDropState = () => {
                priorityList?.classList.remove('drop-at-end');
                priorityList?.querySelectorAll('[data-priority-item]').forEach(item => item.classList.remove('drag-over'));
            };

            const removePriorityDragGhost = () => {
                priorityDragGhost?.remove();
                priorityDragGhost = null;
            };

            priorityList?.addEventListener('dragstart', event => {
                const item = event.target.closest('[data-priority-item]');
                if (!item) return;
                draggingPriorityItem = item;
                item.classList.add('dragging');
                event.dataTransfer.effectAllowed = 'move';
                event.dataTransfer.setData('text/plain', item.textContent.trim());
                const rect = item.getBoundingClientRect();
                priorityDragGhost = item.cloneNode(true);
                priorityDragGhost.classList.remove('dragging', 'drag-over');
                priorityDragGhost.classList.add('priority-drag-ghost');
                priorityDragGhost.style.width = `${rect.width}px`;
                settings.append(priorityDragGhost);
                event.dataTransfer.setDragImage(priorityDragGhost, 26, Math.round(rect.height / 2));
            });

            priorityList?.addEventListener('dragover', event => {
                if (!draggingPriorityItem) return;
                event.preventDefault();
                const target = getPriorityDropTarget(priorityList, event.clientY);
                clearPriorityDropState();
                if (target) {
                    target.classList.add('drag-over');
                } else {
                    priorityList.classList.add('drop-at-end');
                }
            });

            priorityList?.addEventListener('drop', event => {
                if (!draggingPriorityItem) return;
                event.preventDefault();
                const target = getPriorityDropTarget(priorityList, event.clientY);
                if (target) priorityList.insertBefore(draggingPriorityItem, target);
                else priorityList.append(draggingPriorityItem);
                clearPriorityDropState();
            });

            priorityList?.addEventListener('dragend', () => {
                draggingPriorityItem?.classList.remove('dragging');
                clearPriorityDropState();
                removePriorityDragGhost();
                draggingPriorityItem = null;
            });

            document.addEventListener('click', event => {
                if (!event.target.closest('[data-user-actions-toggle]') && !event.target.closest('[data-user-actions-menu]')) {
                    closeUserActions();
                }
                if (!event.target.closest('[data-retry-action-toggle]') && !event.target.closest('[data-retry-menu]')) {
                    closeRetryMenu();
                }
            });

            document.addEventListener('keydown', event => {
                if (event.key !== 'Escape') return;
                closeUserActions();
                closeUserModal();
                closeCampaignDetailsModal();
                closePipelineModal();
                closeRetryMenu();
                closeRetryLogicModal();
                closeRetryReasonModal();
                closePropertyModal();
            });

            const modalPortal = document.createElement('div');
            modalPortal.className = 'calling-crm-settings crm-modal-portal';
            document.body.append(modalPortal);
            modalBackdrops.forEach(backdrop => modalPortal.append(backdrop));
        })();
    </script>
@endpush



@push('scripts')
<script src="{{ asset('js/crm/crm-core.js') }}"></script>
<script type="module" src="{{ asset('js/crm/agents.js') }}"></script>
@endpush

<?php

use App\Http\Controllers\Api\AgentCustomerController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\MessageController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\VendorAuthController;
use App\Http\Controllers\Api\VendorProductController;
use App\Http\Controllers\Api\VendorController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\CallingCrm\CampaignController as CallingCrmCampaignController;
use App\Http\Controllers\Api\CallingCrm\CallController as CallingCrmCallController;
use App\Http\Controllers\Api\CallingCrm\CommunicationsController as CallingCrmCommunicationsController;
use App\Http\Controllers\Api\CallingCrm\DispositionController as CallingCrmDispositionController;
use App\Http\Controllers\Api\CallingCrm\FollowUpController as CallingCrmFollowUpController;
use App\Http\Controllers\Api\CallingCrm\ImportController as CallingCrmImportController;
use App\Http\Controllers\Api\CallingCrm\LeadController as CallingCrmLeadController;
use App\Http\Controllers\Api\CallingCrm\PipelineController as CallingCrmPipelineController;
use App\Http\Controllers\Api\CallingCrm\ReportController as CallingCrmReportController;
use App\Http\Controllers\Api\CallingCrm\SavedFiltersController as CallingCrmSavedFiltersController;
use App\Http\Controllers\Api\CallingCrm\SessionsController as CallingCrmSessionsController;
use App\Http\Controllers\Api\CallingCrm\SettingsController as CallingCrmSettingsController;
use App\Http\Controllers\Api\CallingCrm\TeamsController as CallingCrmTeamsController;
use App\Http\Controllers\Api\VendorCategoryController;
use Illuminate\Support\Facades\Route;

// PUBLIC ROUTE — LOGIN
Route::middleware('throttle:6,1')->group(function () {
    Route::post('login/send-otp', [AuthController::class, 'sendLoginOtp']);
    Route::post('login/verify', [AuthController::class, 'login']);
    Route::post('agent/login', [AuthController::class, 'agentLogin']);
});

// Route::post('/vendor/register', [VendorAuthController::class, 'register']);
Route::middleware('throttle:6,1')->group(function () {
    Route::post('/vendor/send-otp', [VendorAuthController::class, 'sendOtp']);
    Route::post('/vendor/register', [VendorAuthController::class, 'register']);
    Route::post('/vendor/resend-otp', [VendorAuthController::class, 'resendOtp']);
});

// PROTECTED ROUTES — REQUIRE TOKEN
Route::middleware(['auth:sanctum', 'api.active', 'throttle:120,1'])->group(function () {
    // Customers
    Route::get('/agent/customers', [AgentCustomerController::class, 'getAssignedCustomers'])->middleware('api.role:agent');
    Route::get('/customer/agent', [AgentCustomerController::class, 'getCustomerAgent'])->middleware('api.role:customer');

    // Products
    Route::get('/products', [ProductController::class, 'index']);
    Route::get('/products/{id}', [ProductController::class, 'show']);

    // Messages
    Route::get('/messages', [MessageController::class, 'getCurrentConversation']);
    Route::get('/messages/{user_id}', [MessageController::class, 'getMessages']);
    Route::post('/messages/seen/{user_id}', [MessageController::class, 'markAsSeen']);
    Route::get('/message/latest/{user_id}', [MessageController::class, 'getLatestMessage']);
    Route::post('/messages/send', [MessageController::class, 'sendMessage'])->middleware('throttle:30,1');
    Route::post('/messages/send-product', [MessageController::class, 'sendProduct'])->middleware('throttle:30,1');

    // VENDOR PRODUCTS
    Route::get('/categories', [CategoryController::class, 'index']);
    Route::get('/categories/parents', [CategoryController::class, 'parentCategories']);
    Route::get('/categories/{id}/subcategories', [CategoryController::class, 'subCategories']);

    Route::middleware('api.role:vendor')->group(function () {
        // VENDOR PROFILE & PERSONAL FORM
        Route::get('/vendor/profile', [VendorController::class, 'getProfileData']);
        Route::get('/vendor/personal-form', [VendorController::class, 'getPersonalFormData']);
        Route::put('/vendor/profile', [VendorController::class, 'updateProfileData'])->middleware('throttle:30,1');

        // VENDOR PRODUCTS
        Route::post('/vendor/metrics', [VendorProductController::class, 'metrics']);
        Route::post('/vendor/products', [VendorProductController::class, 'store'])->middleware('throttle:20,1');
        Route::get('/vendor/products', [VendorProductController::class, 'index']);
        Route::get('/vendor/products/{id}', [VendorProductController::class, 'show']);
        Route::put('/vendor/products/{id}', [VendorProductController::class, 'update'])->middleware('throttle:30,1');
        Route::delete('/vendor/products/{id}', [VendorProductController::class, 'destroy'])->middleware('throttle:20,1');
        Route::get('/vendor/categories', [VendorCategoryController::class, 'index']);
    });

        Route::prefix('calling-crm')->middleware('api.callingcrm')->group(function () {
        // Settings
        Route::get('/settings/bootstrap', [CallingCrmSettingsController::class, 'bootstrap']);
        Route::get('/settings/profile', [CallingCrmSettingsController::class, 'profile']);
        Route::put('/settings/profile', [CallingCrmSettingsController::class, 'updateProfile']);
        Route::get('/settings/users', [CallingCrmSettingsController::class, 'users']);
        Route::post('/settings/users', [CallingCrmSettingsController::class, 'storeUsers']);
        Route::put('/settings/users/{user}', [CallingCrmSettingsController::class, 'updateUser']);
        Route::patch('/settings/users/{user}/status', [CallingCrmSettingsController::class, 'updateUserStatus']);
        Route::patch('/settings/users/{user}/password', [CallingCrmSettingsController::class, 'updateUserPassword']);
        Route::delete('/settings/users/{user}', [CallingCrmSettingsController::class, 'destroyUser']);
        Route::get('/settings/users/{user}/campaigns', [CallingCrmSettingsController::class, 'userCampaigns']);
        Route::get('/settings/users/{user}/reassign-campaigns', [CallingCrmSettingsController::class, 'userReassignCampaigns']);
        Route::get('/settings/retry-reasons', [CallingCrmSettingsController::class, 'retryReasons']);
        Route::post('/settings/retry-reasons', [CallingCrmSettingsController::class, 'storeRetryReason']);
        Route::put('/settings/retry-reasons/{retryReason}', [CallingCrmSettingsController::class, 'updateRetryReason']);
        Route::delete('/settings/retry-reasons/{retryReason}', [CallingCrmSettingsController::class, 'destroyRetryReason']);
        Route::put('/settings/retry-reasons/{retryReason}/rule', [CallingCrmSettingsController::class, 'updateRetryRule']);
        Route::get('/settings/lead-priority', [CallingCrmSettingsController::class, 'leadPriorityRules']);
        Route::put('/settings/lead-priority', [CallingCrmSettingsController::class, 'updateLeadPriority']);

        // Contact Properties
        Route::get('/contact-properties', [CallingCrmSettingsController::class, 'contactProperties']);
        Route::post('/contact-properties', [CallingCrmSettingsController::class, 'storeContactProperty']);
        Route::put('/contact-properties/{property}', [CallingCrmSettingsController::class, 'updateContactProperty']);
        Route::patch('/contact-properties/{property}/toggle', [CallingCrmSettingsController::class, 'toggleContactProperty']);
        Route::delete('/contact-properties/{property}', [CallingCrmSettingsController::class, 'destroyContactProperty']);

        // Pipelines, Stages, Tags
        Route::apiResource('pipelines', CallingCrmPipelineController::class);
        Route::post('/pipelines/{pipeline}/stages', [CallingCrmPipelineController::class, 'storeStage']);
        Route::post('/pipelines/{pipeline}/stages/reorder', [CallingCrmPipelineController::class, 'reorderStages']);
        Route::put('/stages/{stage}', [CallingCrmPipelineController::class, 'updateStage']);
        Route::delete('/stages/{stage}', [CallingCrmPipelineController::class, 'destroyStage']);
        Route::put('/stages/{stage}/transitions', [CallingCrmPipelineController::class, 'updateStageTransitions']);
        Route::post('/stages/{stage}/tags', [CallingCrmPipelineController::class, 'storeTag']);
        Route::put('/stage-tags/{tag}', [CallingCrmPipelineController::class, 'updateTag']);
        Route::delete('/stage-tags/{tag}', [CallingCrmPipelineController::class, 'destroyTag']);

        // Teams
        Route::get('/teams', [CallingCrmTeamsController::class, 'index']);
        Route::post('/teams', [CallingCrmTeamsController::class, 'store']);
        Route::put('/teams/{team}', [CallingCrmTeamsController::class, 'update']);
        Route::post('/teams/{team}/members', [CallingCrmTeamsController::class, 'addMembers']);
        Route::delete('/teams/{team}/members/{user}', [CallingCrmTeamsController::class, 'removeMember']);

        // Campaigns
        Route::apiResource('campaigns', CallingCrmCampaignController::class);
        Route::patch('/campaigns/{campaign}/status', [CallingCrmCampaignController::class, 'updateStatus']);
        Route::patch('/campaigns/{campaign}/priority', [CallingCrmCampaignController::class, 'updatePriority']);
        Route::patch('/campaigns/{campaign}/pin', [CallingCrmCampaignController::class, 'pin']);
        Route::get('/campaigns/{campaign}/summary', [CallingCrmCampaignController::class, 'summary']);
        Route::get('/campaigns/{campaign}/lead-funnel', [CallingCrmCampaignController::class, 'leadFunnel']);
        Route::get('/campaigns/{campaign}/tags-summary', [CallingCrmCampaignController::class, 'tagsSummary']);
        Route::post('/campaigns/{campaign}/agents', [CallingCrmCampaignController::class, 'addAgents']);
        Route::delete('/campaigns/{campaign}/agents/{user}', [CallingCrmCampaignController::class, 'removeAgent']);
        Route::get('/campaigns/{campaign}/assignment-rules', [CallingCrmCampaignController::class, 'assignmentRules']);
        Route::post('/campaigns/{campaign}/assignment-rules', [CallingCrmCampaignController::class, 'storeAssignmentRule']);
        Route::put('/campaigns/{campaign}/assignment-rules/{rule}', [CallingCrmCampaignController::class, 'updateAssignmentRule']);
        Route::delete('/campaigns/{campaign}/assignment-rules/{rule}', [CallingCrmCampaignController::class, 'destroyAssignmentRule']);

        // Leads
        Route::get('/leads', [CallingCrmLeadController::class, 'index']);
        Route::post('/leads', [CallingCrmLeadController::class, 'store']);
        Route::get('/leads/{lead}', [CallingCrmLeadController::class, 'show']);
        Route::put('/leads/{lead}', [CallingCrmLeadController::class, 'update']);
        Route::delete('/leads/{lead}', [CallingCrmLeadController::class, 'destroy']);
        Route::get('/leads/{lead}/timeline', [CallingCrmLeadController::class, 'timeline']);
        Route::get('/leads/{lead}/history', [CallingCrmLeadController::class, 'history']);
        Route::post('/leads/{lead}/notes', [CallingCrmLeadController::class, 'storeNote']);
        Route::post('/leads/{lead}/phone-numbers', [CallingCrmLeadController::class, 'storePhoneNumber']);
        Route::post('/leads/reassign', [CallingCrmLeadController::class, 'reassign']);
        Route::post('/leads/claim-next', [CallingCrmLeadController::class, 'claimNext']);
        Route::post('/leads/bulk/update', [CallingCrmLeadController::class, 'bulkUpdate']);
        Route::post('/leads/bulk/delete', [CallingCrmLeadController::class, 'bulkDelete']);
        Route::post('/leads/bulk/move', [CallingCrmLeadController::class, 'bulkMove']);
        Route::post('/leads/bulk/copy', [CallingCrmLeadController::class, 'bulkCopy']);
        Route::post('/leads/bulk/close', [CallingCrmLeadController::class, 'bulkClose']);

        // Calls
        Route::get('/calls', [CallingCrmCallController::class, 'index']);
        Route::post('/calls/start', [CallingCrmCallController::class, 'start']);
        Route::post('/calls/webhook', [CallingCrmCallController::class, 'webhook']);
        Route::get('/calls/{call}', [CallingCrmCallController::class, 'show']);
        Route::post('/calls/{call}/recording', [CallingCrmCallController::class, 'uploadRecording']);
        Route::patch('/calls/{call}', [CallingCrmCallController::class, 'update']);
        Route::get('/campaigns/{campaign}/call-logs', [CallingCrmCallController::class, 'campaignCallLogs']);
        Route::get('/users/{user}/call-logs', [CallingCrmCallController::class, 'userCallLogs']);

        // Dispositions
        Route::apiResource('dispositions', CallingCrmDispositionController::class)->except(['show']);
        Route::post('/leads/{lead}/dispose', [CallingCrmDispositionController::class, 'disposeLead']);

        // Follow-ups
        Route::get('/follow-ups', [CallingCrmFollowUpController::class, 'index']);
        Route::post('/follow-ups', [CallingCrmFollowUpController::class, 'store']);
        Route::put('/follow-ups/{followUp}', [CallingCrmFollowUpController::class, 'update']);
        Route::patch('/follow-ups/{followUp}', [CallingCrmFollowUpController::class, 'update']);
        Route::delete('/follow-ups/{followUp}', [CallingCrmFollowUpController::class, 'destroy']);
        Route::patch('/follow-ups/{followUp}/complete', [CallingCrmFollowUpController::class, 'complete']);

        // Imports
        Route::get('/imports', [CallingCrmImportController::class, 'index']);
        Route::post('/campaigns/{campaign}/imports', [CallingCrmImportController::class, 'store']);
        Route::get('/imports/{import}', [CallingCrmImportController::class, 'show']);
        Route::get('/imports/{import}/rows', [CallingCrmImportController::class, 'rows']);
        Route::delete('/imports/{import}', [CallingCrmImportController::class, 'destroy']);
        Route::get('/imports/sample', [CallingCrmImportController::class, 'sample']);

        // Dashboard
        Route::get('/dashboard/overview', [CallingCrmReportController::class, 'dashboardOverview']);
        Route::get('/dashboard/agent-activity', [CallingCrmReportController::class, 'agentActivity']);
        Route::get('/dashboard/leads-by-stage', [CallingCrmReportController::class, 'leadsByStage']);

        // Reports
        Route::get('/reports/catalog', [CallingCrmReportController::class, 'catalog']);
        Route::get('/reports/user-call', [CallingCrmReportController::class, 'userCallReport']);
        Route::get('/reports/lead-disposition', [CallingCrmReportController::class, 'leadDispositionReport']);
        Route::get('/reports/follow-ups', [CallingCrmReportController::class, 'followUpReport']);
        Route::get('/reports/campaign', [CallingCrmReportController::class, 'campaignReport']);
        Route::get('/reports/login', [CallingCrmReportController::class, 'loginReport']);
        Route::get('/reports/hourly', [CallingCrmReportController::class, 'hourlyReport']);
        Route::get('/reports/day', [CallingCrmReportController::class, 'dayReport']);
        Route::post('/reports/exports', [CallingCrmReportController::class, 'queueExport']);
        Route::get('/reports/exports/{export}', [CallingCrmReportController::class, 'showExport']);

        // Trends
        Route::get('/trends/widgets', [CallingCrmReportController::class, 'trendWidgets']);
        Route::get('/trends/calls-vs-connected', [CallingCrmReportController::class, 'callsVsConnected']);
        Route::get('/trends/call-duration', [CallingCrmReportController::class, 'callDurationTrend']);
        Route::get('/trends/conversion-ratio', [CallingCrmReportController::class, 'conversionRatioTrend']);
        Route::get('/trends/leads-added', [CallingCrmReportController::class, 'leadsAddedTrend']);
        Route::get('/trends/lead-sources', [CallingCrmReportController::class, 'leadSources']);
        Route::get('/trends/lost-leads', [CallingCrmReportController::class, 'lostLeadsTrend']);

        // Sessions
        Route::post('/sessions/login-track', [CallingCrmSessionsController::class, 'loginTrack']);
        Route::post('/sessions/logout-track', [CallingCrmSessionsController::class, 'logoutTrack']);
        Route::post('/sessions/breaks/start', [CallingCrmSessionsController::class, 'startBreak']);
        Route::post('/sessions/breaks/{break}/end', [CallingCrmSessionsController::class, 'endBreak']);
        Route::get('/sessions/current', [CallingCrmSessionsController::class, 'current']);

        // Saved Filters
        Route::get('/saved-filters', [CallingCrmSavedFiltersController::class, 'index']);
        Route::post('/saved-filters', [CallingCrmSavedFiltersController::class, 'store']);
        Route::put('/saved-filters/{filter}', [CallingCrmSavedFiltersController::class, 'update']);
        Route::delete('/saved-filters/{filter}', [CallingCrmSavedFiltersController::class, 'destroy']);

        // Communications
        Route::post('/communications/sms', [CallingCrmCommunicationsController::class, 'sendSms']);
        Route::post('/communications/email', [CallingCrmCommunicationsController::class, 'sendEmail']);
        Route::post('/communications/whatsapp', [CallingCrmCommunicationsController::class, 'sendWhatsApp']);
        Route::get('/communications', [CallingCrmCommunicationsController::class, 'index']);
    });
    

    // Logout
    Route::post('/logout', [AuthController::class, 'logout']);
});

<?php

use App\Models\Admin;

it('renders calling crm pages with shared css and javascript assets', function (string $routeName) {
    $admin = Admin::query()->create([
        'name' => 'CRM Admin',
        'email' => 'crm-admin@example.test',
        'password' => 'password',
    ]);

    $this->actingAs($admin, 'admin')
        ->get(route($routeName))
        ->assertOk()
        ->assertSee('css/crm/calling-crm.css', false)
        ->assertSee('js/crm/calling-crm.js', false)
        ->assertSee('js/crm/crm-core.js', false)
        ->assertSee('callingCrmConfig', false);
})->with([
    'dashboard' => 'admin_panel.admin.callingcrm.dashboard',
    'contact' => 'admin_panel.admin.callingcrm.contact',
    'contact properties' => 'admin_panel.admin.callingcrm.contact.properties',
    'pipeline' => 'admin_panel.admin.callingcrm.pipeline',
    'report' => 'admin_panel.admin.callingcrm.report',
    'user report' => 'admin_panel.admin.callingcrm.report.user',
    'login report' => 'admin_panel.admin.callingcrm.report.login',
    'trends' => 'admin_panel.admin.callingcrm.trends',
    'settings' => 'admin_panel.admin.callingcrm.settings',
]);

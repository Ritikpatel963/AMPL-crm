<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class CallingCrmPermissionSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $permissions = [
            'crm.dashboard.view',
            'crm.users.manage',
            'crm.roles.manage',
            'crm.pipelines.manage',
            'crm.campaigns.view',
            'crm.campaigns.manage',
            'crm.leads.view',
            'crm.leads.create',
            'crm.leads.update',
            'crm.leads.delete',
            'crm.leads.assign',
            'crm.leads.bulk_actions',
            'crm.calls.start',
            'crm.calls.view',
            'crm.dispositions.manage',
            'crm.followups.manage',
            'crm.imports.manage',
            'crm.reports.view',
            'crm.reports.export',
            'crm.settings.manage',
        ];

        foreach (['web', 'admin'] as $guardName) {
            foreach ($permissions as $permission) {
                Permission::firstOrCreate([
                    'name' => $permission,
                    'guard_name' => $guardName,
                ]);
            }

            $superAdmin = Role::firstOrCreate(['name' => 'Super Admin', 'guard_name' => $guardName]);
            $crmAdmin = Role::firstOrCreate(['name' => 'CRM Admin', 'guard_name' => $guardName]);
            $teamLead = Role::firstOrCreate(['name' => 'CRM Team Lead', 'guard_name' => $guardName]);
            $executive = Role::firstOrCreate(['name' => 'CRM Executive', 'guard_name' => $guardName]);
            $auditor = Role::firstOrCreate(['name' => 'CRM Auditor', 'guard_name' => $guardName]);

            $superAdmin->syncPermissions($permissions);
            $crmAdmin->syncPermissions($permissions);

            $teamLead->syncPermissions([
                'crm.dashboard.view',
                'crm.campaigns.view',
                'crm.leads.view',
                'crm.leads.create',
                'crm.leads.update',
                'crm.leads.assign',
                'crm.leads.bulk_actions',
                'crm.calls.start',
                'crm.calls.view',
                'crm.followups.manage',
                'crm.imports.manage',
                'crm.reports.view',
                'crm.reports.export',
            ]);

            $executive->syncPermissions([
                'crm.dashboard.view',
                'crm.campaigns.view',
                'crm.leads.view',
                'crm.leads.create',
                'crm.leads.update',
                'crm.calls.start',
                'crm.calls.view',
                'crm.followups.manage',
            ]);

            $auditor->syncPermissions([
                'crm.dashboard.view',
                'crm.campaigns.view',
                'crm.leads.view',
                'crm.calls.view',
                'crm.reports.view',
                'crm.reports.export',
            ]);
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}

<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class CallingCrmRoleSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            'callingcrm.access',
            'callingcrm.dashboard.view',
            'callingcrm.contacts.view',
            'callingcrm.contacts.create',
            'callingcrm.contacts.import',
            'callingcrm.pipeline.view',
            'callingcrm.pipeline.manage',
            'callingcrm.reports.view',
            'callingcrm.reports.export',
            'callingcrm.trends.view',
        ];

        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission, 'admin');
        }

        $admin = Role::findOrCreate('callingcrm_admin', 'admin');
        $manager = Role::findOrCreate('callingcrm_manager', 'admin');
        $agent = Role::findOrCreate('callingcrm_agent', 'admin');

        $admin->syncPermissions($permissions);

        $manager->syncPermissions([
            'callingcrm.access',
            'callingcrm.dashboard.view',
            'callingcrm.contacts.view',
            'callingcrm.contacts.create',
            'callingcrm.contacts.import',
            'callingcrm.pipeline.view',
            'callingcrm.pipeline.manage',
            'callingcrm.reports.view',
            'callingcrm.reports.export',
            'callingcrm.trends.view',
        ]);

        $agent->syncPermissions([
            'callingcrm.access',
            'callingcrm.dashboard.view',
            'callingcrm.contacts.view',
            'callingcrm.pipeline.view',
            'callingcrm.reports.view',
            'callingcrm.trends.view',
        ]);
    }
}

<?php

it('keeps admin and user login pages on separate routes', function () {
    $this->get('/admin')
        ->assertOk()
        ->assertSee('Admin Login')
        ->assertSee(route('admin_panel.admin.send.otp'), false);

    $this->get('/login')
        ->assertOk()
        ->assertSee('Forgot your password?')
        ->assertDontSee('Admin Login');
});

it('redirects guests from admin pages to the admin login route', function () {
    $this->get(route('admin_panel.admin.callingcrm.dashboard'))
        ->assertRedirect(route('admin_panel.admin.login'));
});

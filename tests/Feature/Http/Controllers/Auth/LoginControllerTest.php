<?php

use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

it('renders the login page', function () {
    $response = $this->get(route('login'));

    $response->assertInertia(fn (Assert $page) => $page->component('Auth/Login'));
});

it('redirects guests from the control panel to the login page', function () {
    $response = $this->get(route('admin.dashboard'));

    $response->assertRedirect(route('login'));
});

it('authenticates a user with valid credentials', function () {
    $user = User::factory()->create();

    $response = $this->post(route('login.store'), [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $response->assertRedirect(route('admin.dashboard'));
    $this->assertAuthenticatedAs($user);
});

it('rejects an invalid password with an Arabic message', function () {
    $user = User::factory()->create();

    $response = $this->post(route('login.store'), [
        'email' => $user->email,
        'password' => 'wrong-password',
    ]);

    $response->assertSessionHasErrors(['email' => 'بيانات الدخول هذه غير متطابقة مع سجلاتنا.']);
    $this->assertGuest();
});

it('locks out the email after five failed attempts', function () {
    $user = User::factory()->create();

    foreach (range(1, 5) as $attempt) {
        $this->post(route('login.store'), ['email' => $user->email, 'password' => 'wrong-password']);
    }

    $response = $this->post(route('login.store'), [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $response->assertSessionHasErrors('email');
    expect(session('errors')->first('email'))->toStartWith('محاولات دخول كثيرة');
    $this->assertGuest();
});

it('redirects an authenticated user away from the login page', function () {
    $response = $this->actingAs(User::factory()->create())->get(route('login'));

    $response->assertRedirect(route('admin.dashboard'));
});

it('logs the user out', function () {
    $response = $this->actingAs(User::factory()->create())->post(route('logout'));

    $response->assertRedirect(route('home'));
    $this->assertGuest();
});

<?php

use App\Enums\Permission;
use App\Models\ContactMessage;
use Inertia\Testing\AssertableInertia as Assert;

it('lists messages newest first', function () {
    $older = ContactMessage::factory()->create(['created_at' => now()->subDay()]);
    $newer = ContactMessage::factory()->create();

    $response = $this->actingAs(admin())->get(route('admin.messages.index'));

    $response->assertInertia(fn (Assert $page) => $page
        ->component('Admin/Messages/Index')
        ->where('messages.data.0.id', $newer->id)
        ->where('messages.data.1.id', $older->id)
        ->where('messages.data.0.read', false));
});

it('marks a message as read when it is opened', function () {
    $message = ContactMessage::factory()->create();

    $response = $this->actingAs(admin())->get(route('admin.messages.show', $message));

    $response->assertInertia(fn (Assert $page) => $page->where('message.email', $message->email));
    expect($message->fresh()->read_at)->not->toBeNull();
});

it('shares the number of unread messages with the control panel', function () {
    ContactMessage::factory()->count(2)->create();
    ContactMessage::factory()->read()->create();

    $response = $this->actingAs(admin())->get(route('admin.dashboard'));

    $response->assertInertia(fn (Assert $page) => $page->where('unreadMessages', 2));
});

it('deletes a message', function () {
    $message = ContactMessage::factory()->create();

    $response = $this->actingAs(admin())->delete(route('admin.messages.destroy', $message));

    $response->assertRedirect(route('admin.messages.index'));
    $this->assertModelMissing($message);
});

it('forbids users who cannot manage messages', function () {
    $message = ContactMessage::factory()->create();

    $response = $this->actingAs(userWithPermissions(Permission::ProductsView))->get(route('admin.messages.show', $message));

    $response->assertForbidden();
    expect($message->fresh()->read_at)->toBeNull();
});

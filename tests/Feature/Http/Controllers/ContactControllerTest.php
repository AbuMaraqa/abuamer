<?php

use App\Mail\ContactMessageReceived;
use App\Models\Category;
use App\Models\ContactMessage;
use App\Models\Product;
use App\Settings\ContactSettings;
use Illuminate\Support\Facades\Mail;
use Inertia\Testing\AssertableInertia as Assert;

/**
 * @param  array<string, mixed>  $overrides
 * @return array<string, mixed>
 */
function contactPayload(array $overrides = []): array
{
    return [
        'name' => 'سارة أحمد',
        'email' => 'sara@example.com',
        'phone' => '+966 50 123 4567',
        'subject' => 'عرض سعر',
        'message' => 'أرغب في عرض سعر لبلاط بورسلان لفيلا.',
        ...$overrides,
    ];
}

it('renders the contact page', function () {
    $response = $this->get(route('contact'));

    $response->assertInertia(fn (Assert $page) => $page->component('Contact')->where('subject', null));
});

it('fills in the subject for an inquiry about a product', function () {
    $product = Product::factory()->named('Calacatta Gold', 'كالاكاتا ذهبي')->create(['sku' => 'NSQ-000012']);

    $response = $this->get(route('contact', ['product' => $product->id]));

    $response->assertInertia(fn (Assert $page) => $page->where('subject', 'استفسار عن كالاكاتا ذهبي (NSQ-000012)'));
});

it('leaves the subject empty for products visitors cannot see', function (Product $product) {
    $response = $this->get(route('contact', ['product' => $product->id]));

    $response->assertInertia(fn (Assert $page) => $page->where('subject', null));
})->with([
    'hidden product' => fn () => Product::factory()->inactive()->create(),
    'product in a hidden category' => fn () => Product::factory()->for(Category::factory()->inactive())->create(),
]);

it('stores the message and emails the configured recipient', function () {
    Mail::fake();
    app(ContactSettings::class)->fill(['form_recipient' => 'sales@nasaq.test'])->save();

    $response = $this->from(route('contact'))->post(route('contact.store'), contactPayload());

    $response->assertRedirect(route('contact'))->assertInertiaFlash('toast.type', 'success');
    $message = ContactMessage::sole();
    expect($message->email)->toBe('sara@example.com')
        ->and($message->locale)->toBe('ar')
        ->and($message->read_at)->toBeNull();
    Mail::assertQueued(ContactMessageReceived::class, fn (ContactMessageReceived $mail) => $mail->hasTo('sales@nasaq.test') && $mail->contactMessage->is($message));
});

it('stores the message without emailing when no recipient is configured', function () {
    Mail::fake();

    $this->post(route('contact.store'), contactPayload());

    expect(ContactMessage::count())->toBe(1);
    Mail::assertNothingQueued();
});

it('silently discards messages that fill the honeypot', function () {
    Mail::fake();
    app(ContactSettings::class)->fill(['form_recipient' => 'sales@nasaq.test'])->save();

    $response = $this->post(route('contact.store'), contactPayload(['website' => 'https://spam.example']));

    $response->assertInertiaFlash('toast.type', 'success');
    expect(ContactMessage::count())->toBe(0);
    Mail::assertNothingQueued();
});

it('requires a name, a valid email and a message', function () {
    $response = $this->post(route('contact.store'), ['email' => 'not-an-email', 'message' => 'short']);

    $response->assertSessionHasErrors(['name', 'email', 'message']);
    expect(ContactMessage::count())->toBe(0);
});

it('limits how often one visitor can send messages', function () {
    foreach (range(1, 5) as $attempt) {
        $this->post(route('contact.store'), contactPayload());
    }

    $response = $this->post(route('contact.store'), contactPayload());

    $response->assertTooManyRequests();
    expect(ContactMessage::count())->toBe(5);
});

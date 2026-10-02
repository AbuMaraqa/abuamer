<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreContactMessageRequest;
use App\Mail\ContactMessageReceived;
use App\Models\ContactMessage;
use App\Models\Product;
use App\Services\Catalog\CategoryTreeService;
use App\Settings\ContactSettings;
use App\Support\Localized;
use App\Support\Seo\SeoMeta;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class ContactController extends Controller
{
    public function __construct(private readonly CategoryTreeService $categoryTree) {}

    /**
     * Show the contact page. Visitors coming from a product page ("?product=12") find the
     * subject already filled in with that product.
     */
    public function show(Request $request, ContactSettings $contact): Response
    {
        return Inertia::render('Contact', [
            'seo' => SeoMeta::make()
                ->title(__('Contact'))
                ->description(__('Our team will help you choose the right tiles, sizes and finishes for your space.')),
            'mapEmbedUrl' => $contact->map_embed_url ?: null,
            'workingHours' => Localized::value($contact->working_hours) ?: null,
            'subject' => $this->productInquirySubject($request->integer('product')),
        ]);
    }

    /**
     * Store a contact form message and email it to the configured recipient.
     */
    public function store(StoreContactMessageRequest $request, ContactSettings $contact): RedirectResponse
    {
        // Bots that fill the honeypot get the same answer, but nothing is stored or sent.
        if (! $request->isLikelySpam()) {
            $message = ContactMessage::create([
                ...$request->safe()->only(['name', 'email', 'phone', 'subject', 'message']),
                'locale' => app()->getLocale(),
                'ip_address' => $request->ip(),
            ]);

            if (filled($contact->form_recipient)) {
                Mail::to($contact->form_recipient)->queue(new ContactMessageReceived($message));
            }
        }

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Thank you! Your message has been sent. We will get back to you soon.')]);

        return back();
    }

    /**
     * The subject for an inquiry about a product the visitor can see, e.g. "Inquiry about Calacatta Gold (NSQ-000012)".
     */
    private function productInquirySubject(int $productId): ?string
    {
        $product = $productId > 0 ? Product::query()->active()->find($productId) : null;

        if ($product === null || ! $this->categoryTree->tree()->isVisible($product->category_id)) {
            return null;
        }

        $subject = __('Inquiry about :name', ['name' => $product->name]).($product->sku ? " ({$product->sku})" : '');

        return Str::limit($subject, 200, '');
    }
}

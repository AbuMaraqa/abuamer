<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreContactMessageRequest;
use App\Mail\ContactMessageReceived;
use App\Models\ContactMessage;
use App\Settings\ContactSettings;
use App\Support\Localized;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Mail;
use Inertia\Inertia;
use Inertia\Response;

class ContactController extends Controller
{
    /**
     * Show the contact page.
     */
    public function show(ContactSettings $contact): Response
    {
        return Inertia::render('Contact', [
            'mapEmbedUrl' => $contact->map_embed_url ?: null,
            'workingHours' => Localized::value($contact->working_hours) ?: null,
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
}

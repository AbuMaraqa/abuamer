<?php

namespace App\Http\Controllers\Admin;

use App\Enums\Permission;
use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class ContactMessageController extends Controller
{
    public function index(): Response
    {
        Gate::authorize(Permission::MessagesManage->value);

        return Inertia::render('Admin/Messages/Index', [
            'messages' => ContactMessage::query()
                ->latest()
                ->orderByDesc('id')
                ->paginate(config('catalog.admin_per_page'))
                ->through(fn (ContactMessage $message): array => [
                    'id' => $message->id,
                    'name' => $message->name,
                    'email' => $message->email,
                    'subject' => $message->subject,
                    'excerpt' => str($message->message)->squish()->limit(120)->value(),
                    'read' => $message->read_at !== null,
                    'created_at' => $message->created_at->toIso8601String(),
                ]),
        ]);
    }

    /**
     * Show a message and mark it as read.
     */
    public function show(ContactMessage $message): Response
    {
        Gate::authorize(Permission::MessagesManage->value);

        $message->markAsRead();

        return Inertia::render('Admin/Messages/Show', [
            'message' => [
                ...$message->only(['id', 'name', 'email', 'phone', 'subject', 'message', 'locale', 'ip_address']),
                'created_at' => $message->created_at->toIso8601String(),
            ],
        ]);
    }

    public function destroy(ContactMessage $message): RedirectResponse
    {
        Gate::authorize(Permission::MessagesManage->value);

        $message->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Message deleted.')]);

        return to_route('admin.messages.index');
    }
}

<x-mail::message>
# New message from the website

<x-mail::table>
| | |
|:--|:--|
| **Name** | {{ $contactMessage->name }} |
| **Email** | {{ $contactMessage->email }} |
@if ($contactMessage->phone)
| **Phone** | {{ $contactMessage->phone }} |
@endif
@if ($contactMessage->subject)
| **Subject** | {{ $contactMessage->subject }} |
@endif
| **Language** | {{ strtoupper($contactMessage->locale) }} |
</x-mail::table>

<x-mail::panel>
{{ $contactMessage->message }}
</x-mail::panel>

<x-mail::button :url="route('admin.messages.show', $contactMessage)">
Open in the control panel
</x-mail::button>

Reply to this email to answer {{ $contactMessage->name }} directly.
</x-mail::message>

<!-- resources/views/emails/reset-password.blade.php -->

<x-mail::message>
# Hello, {{ $name }}

You are receiving this email because we received a password reset request for your Newborn Application account.
Please click the following link to reset your password:

<x-mail::button :url="$url">
Reset Password
</x-mail::button>

Regards,<br>
{{ config('mail.from.name') }}
</x-mail::message>
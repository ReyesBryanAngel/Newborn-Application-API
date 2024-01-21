<!-- resources/views/emails/forgot-password.blade.php -->

<html>
<head>
    <title>Forgot Password Email</title>
</head>
<body>
    <p>Hello, {{ $name }}</p>
    <p>We have received a request to reset your password. Please click the link below to reset it:</p>
    <a href="{{ $url }}">Reset Password</a>
    <p>If you did not request a password reset, please ignore this email.</p>
    <p>Thank you,</p>
    <p>{{ config('mail.from.name') }}</p>
</body>
</html>

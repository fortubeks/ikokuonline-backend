<!DOCTYPE html>
<html>
<head>
    <title>Email Verification</title>
</head>
<body>
    <p>Hello {{ $user->first_name }},</p>

    <p>Your email verification code is: <strong>{{ $user->email_verification_code }}</strong></p>

    <p>This code will expire in 15 minutes.</p>

    <p>Thank you!</p>
</body>
</html>

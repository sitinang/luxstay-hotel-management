<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Your LUXSTAY Account</title>
</head>
<body style="font-family: Georgia, 'Times New Roman', serif; background-color: #f5f5f0; margin: 0; padding: 0;">
    <div style="max-width: 600px; margin: 0 auto; background: #ffffff;">
        {{-- HEADER --}}
        <div style="background: #0A1128; padding: 35px 20px; text-align: center; border-bottom: 4px solid #D4AF37;">
            <h1 style="margin: 0; font-family: Georgia, 'Times New Roman', serif; font-size: 28px; font-weight: normal; color: #ffffff; letter-spacing: 4px; text-transform: uppercase;">
                LUXSTAY <span style="color: #D4AF37; font-weight: bold;">HOTEL</span>
            </h1>
        </div>

        {{-- BODY --}}
        <div style="padding: 40px; border: 1px solid #e8e0d5; border-top: none;">
            <div style="font-size: 24px; font-weight: bold; color: #0f172a; margin-bottom: 16px;">Welcome, {{ $name }}!</div>
            <p style="font-size: 15px; color: #555; line-height: 1.7; margin-bottom: 16px;">
                Thank you for choosing <strong>LUXSTAY Hotel</strong>. To complete your registration, please use the 6-digit verification code below:
            </p>

            <div style="background: #fdfaf3; border: 1.5px solid #d4af37; border-radius: 12px; padding: 28px 20px; text-align: center; margin: 28px 0;">
                <div style="font-family: 'Courier New', monospace; font-size: 42px; font-weight: bold; letter-spacing: 12px; color: #0f172a;">{{ $otp }}</div>
                <div style="color: #b8860b; font-size: 13px; margin-top: 10px;">This code will expire in <strong>15 minutes</strong>.</div>
            </div>

            <p style="font-size: 15px; color: #555; line-height: 1.7;">For your security, please do not share this code with anyone.</p>
            <p style="font-size: 13px; color: #999; margin-top: 24px;">If you did not create an account at LUXSTAY Hotel, no further action is required.</p>
        </div>

        {{-- FOOTER --}}
        <div style="background: #f5f5f0; padding: 24px 40px; text-align: center; font-size: 12px; color: #aaa; border-top: 1px solid #e8e0d5;">
            Warm regards,<br>
            <strong style="color: #0f172a;">The LUXSTAY Team</strong><br><br>
            &copy; {{ date('Y') }} LUXSTAY. All rights reserved.
        </div>
    </div>
</body>
</html>

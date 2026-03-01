<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Verify Your Account — {{ config('app.name') }}</title>
    <!--[if mso]>
    <noscript>
        <xml>
            <o:OfficeDocumentSettings>
                <o:PixelsPerInch>96</o:PixelsPerInch>
            </o:OfficeDocumentSettings>
        </xml>
    </noscript>
    <![endif]-->
</head>

<body style="margin:0;padding:0;background-color:#f4f6f9;font-family:'Segoe UI',Helvetica,Arial,sans-serif;">

    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0"
        style="background-color:#f4f6f9;">
        <tr>
            <td align="center" style="padding:40px 16px;">

                <!-- Email Card -->
                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0"
                    style="max-width:600px;">

                    <!-- Header -->
                    <tr>
                        <td align="center"
                            style="background-color:#1a1a2e;border-radius:8px 8px 0 0;padding:36px 48px;">
                            <h1 style="margin:0;font-size:28px;font-weight:700;color:#ffffff;letter-spacing:-0.5px;">
                                {{ config('app.name') }}
                            </h1>
                            <p
                                style="margin:6px 0 0;font-size:13px;color:#a0aec0;letter-spacing:1.5px;text-transform:uppercase;">
                                Smart Relocation Solutions
                            </p>
                        </td>
                    </tr>

                    <!-- Body -->
                    <tr>
                        <td style="background-color:#ffffff;padding:48px;">

                            <!-- Shield Icon -->
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0">
                                <tr>
                                    <td align="center" style="padding-bottom:32px;">
                                        <div
                                            style="display:inline-block;background-color:#eef2ff;border-radius:50%;width:72px;height:72px;line-height:72px;text-align:center;font-size:32px;">
                                            🔐
                                        </div>
                                    </td>
                                </tr>
                            </table>

                            <!-- Heading -->
                            <h2 style="margin:0 0 12px;font-size:24px;font-weight:700;color:#1a1a2e;text-align:center;">
                                Verify Your Email Address
                            </h2>
                            <p style="margin:0 0 32px;font-size:15px;color:#64748b;text-align:center;line-height:1.6;">
                                One last step before you get started.
                            </p>

                            <!-- Divider -->
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0">
                                <tr>
                                    <td style="border-top:1px solid #e2e8f0;padding-bottom:32px;"></td>
                                </tr>
                            </table>

                            <!-- Body text -->
                            <p style="margin:0 0 16px;font-size:15px;color:#374151;line-height:1.7;">
                                Dear <strong>{{ $name }} {{ $surname }}</strong>,
                            </p>
                            <p style="margin:0 0 16px;font-size:15px;color:#374151;line-height:1.7;">
                                Thank you for registering with <strong>{{ config('app.name') }}</strong>. To complete
                                your registration and activate your account, please verify your email address by
                                clicking the button below.
                            </p>
                            <p style="margin:0 0 32px;font-size:15px;color:#374151;line-height:1.7;">
                                This verification link will expire in <strong>60 minutes</strong>. If you did not create
                                an account, no further action is required.
                            </p>

                            <!-- CTA Button -->
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0">
                                <tr>
                                    <td align="center" style="padding-bottom:32px;">
                                        <a href="{{ $verificationUrl }}"
                                            style="display:inline-block;background-color:#4f46e5;color:#ffffff;font-size:15px;font-weight:600;text-decoration:none;padding:14px 36px;border-radius:6px;letter-spacing:0.3px;">
                                            Verify Email Address
                                        </a>
                                    </td>
                                </tr>
                            </table>

                            <!-- Fallback URL -->
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0"
                                style="margin-bottom:32px;">
                                <tr>
                                    <td
                                        style="background-color:#f8fafc;border:1px solid #e2e8f0;border-radius:6px;padding:16px 20px;">
                                        <p
                                            style="margin:0 0 6px;font-size:12px;font-weight:600;color:#64748b;text-transform:uppercase;letter-spacing:0.8px;">
                                            If the button doesn't work, copy this link:
                                        </p>
                                        <p
                                            style="margin:0;font-size:13px;color:#4f46e5;word-break:break-all;line-height:1.6;">
                                            {{ $verificationUrl }}
                                        </p>
                                    </td>
                                </tr>
                            </table>

                            <!-- Security Notice -->
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0"
                                style="margin-bottom:32px;">
                                <tr>
                                    <td
                                        style="background-color:#fffbeb;border-left:4px solid #f59e0b;border-radius:4px;padding:14px 20px;">
                                        <p style="margin:0;font-size:13px;color:#92400e;line-height:1.6;">
                                            <strong>Security notice:</strong> {{ config('app.name') }} will never ask
                                            for your password via email. If you did not request this verification,
                                            please disregard this message.
                                        </p>
                                    </td>
                                </tr>
                            </table>

                            <!-- Divider -->
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0">
                                <tr>
                                    <td style="border-top:1px solid #e2e8f0;padding-bottom:24px;"></td>
                                </tr>
                            </table>

                            <!-- Closing -->
                            <p style="margin:0 0 4px;font-size:14px;color:#374151;line-height:1.6;">
                                Regards,
                            </p>
                            <p style="margin:0;font-size:14px;font-weight:700;color:#1a1a2e;">
                                The {{ config('app.name') }} Security Team
                            </p>

                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td
                            style="background-color:#f8fafc;border-top:1px solid #e2e8f0;border-radius:0 0 8px 8px;padding:24px 48px;text-align:center;">
                            <p style="margin:0 0 8px;font-size:12px;color:#94a3b8;line-height:1.6;">
                                This is an automated security email sent to confirm your identity. Please do not reply
                                to this message.
                            </p>
                            <p style="margin:0;font-size:12px;color:#94a3b8;">
                                &copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.
                            </p>
                        </td>
                    </tr>

                </table>

            </td>
        </tr>
    </table>

</body>

</html>
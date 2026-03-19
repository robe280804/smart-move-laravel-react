<!DOCTYPE html>
<html lang="en" xmlns="http://www.w3.org/1999/xhtml" xmlns:v="urn:schemas-microsoft-com:vml"
    xmlns:o="urn:schemas-microsoft-com:office:office">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="x-apple-disable-message-reformatting">
    <title>Verify Your Email — {{ config('app.name') }}</title>
    <!--[if mso]>
    <noscript><xml><o:OfficeDocumentSettings><o:PixelsPerInch>96</o:PixelsPerInch></o:OfficeDocumentSettings></xml></noscript>
    <![endif]-->
    <style>
        /* Reset */
        * { box-sizing: border-box; }
        body, table, td, a { -webkit-text-size-adjust: 100%; -ms-text-size-adjust: 100%; }
        table, td { mso-table-lspace: 0pt; mso-table-rspace: 0pt; }
        img { -ms-interpolation-mode: bicubic; border: 0; outline: none; text-decoration: none; }

        /* Mobile */
        @media only screen and (max-width: 620px) {
            .wrapper { padding: 16px 8px !important; }
            .card { border-radius: 12px !important; }
            .header { padding: 32px 24px !important; border-radius: 12px 12px 0 0 !important; }
            .body { padding: 32px 24px !important; }
            .footer { padding: 20px 24px !important; border-radius: 0 0 12px 12px !important; }
            .heading { font-size: 20px !important; }
            .cta-btn { padding: 14px 28px !important; font-size: 15px !important; display: block !important; text-align: center !important; }
            .fallback-url { font-size: 12px !important; word-break: break-all !important; }
        }
    </style>
</head>

<body style="margin:0;padding:0;background-color:#f1f5f9;font-family:'Segoe UI',Helvetica,Arial,sans-serif;-webkit-font-smoothing:antialiased;">

    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background-color:#f1f5f9;">
        <tr>
            <td align="center" class="wrapper" style="padding:48px 16px;">

                <table role="presentation" class="card" width="100%" cellspacing="0" cellpadding="0" border="0"
                    style="max-width:600px;border-radius:16px;overflow:hidden;box-shadow:0 4px 24px rgba(0,0,0,0.10);">

                    <!-- ── Header ── -->
                    <tr>
                        <td class="header" align="center"
                            style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #312e81 100%); padding:44px 48px;">

                            <!-- Logo mark -->
                            <table role="presentation" cellspacing="0" cellpadding="0" border="0">
                                <tr>
                                    <td align="center" style="padding-bottom:16px;">
                                        <div style="display:inline-block;background:linear-gradient(135deg,#3b82f6,#6366f1);border-radius:14px;width:52px;height:52px;line-height:52px;text-align:center;font-size:26px;">
                                            🏋️
                                        </div>
                                    </td>
                                </tr>
                            </table>

                            <h1 style="margin:0 0 4px;font-size:22px;font-weight:800;color:#ffffff;letter-spacing:-0.3px;">
                                {{ config('app.name') }}
                            </h1>
                            <p style="margin:0;font-size:12px;color:#94a3b8;letter-spacing:2px;text-transform:uppercase;font-weight:500;">
                                AI-Powered Fitness Planner
                            </p>
                        </td>
                    </tr>

                    <!-- ── Body ── -->
                    <tr>
                        <td class="body" style="background-color:#ffffff;padding:48px;">

                            <!-- Greeting -->
                            <p style="margin:0 0 6px;font-size:13px;font-weight:600;color:#6366f1;text-transform:uppercase;letter-spacing:1px;">
                                One last step
                            </p>
                            <h2 class="heading" style="margin:0 0 16px;font-size:24px;font-weight:700;color:#0f172a;line-height:1.2;">
                                Verify your email address
                            </h2>
                            <p style="margin:0 0 28px;font-size:15px;color:#475569;line-height:1.7;">
                                Hi <strong style="color:#0f172a;">{{ $name }} {{ $surname }}</strong>, welcome to {{ config('app.name') }}!
                                We're excited to have you on board. Before we get started, we just need to confirm your email address.
                            </p>

                            <!-- Divider -->
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0">
                                <tr><td style="border-top:1px solid #e2e8f0;padding-bottom:28px;"></td></tr>
                            </table>

                            <!-- What you get section -->
                            <p style="margin:0 0 16px;font-size:14px;font-weight:600;color:#0f172a;">
                                Once verified you'll be able to:
                            </p>

                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="margin-bottom:32px;">
                                <tr>
                                    <td style="padding:0 0 10px;">
                                        <table role="presentation" cellspacing="0" cellpadding="0" border="0">
                                            <tr>
                                                <td style="padding-right:10px;vertical-align:top;font-size:16px;">⚡</td>
                                                <td style="font-size:14px;color:#475569;line-height:1.6;">
                                                    <strong style="color:#0f172a;">Generate AI workout plans</strong> tailored to your goals and fitness level
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding:0 0 10px;">
                                        <table role="presentation" cellspacing="0" cellpadding="0" border="0">
                                            <tr>
                                                <td style="padding-right:10px;vertical-align:top;font-size:16px;">📊</td>
                                                <td style="font-size:14px;color:#475569;line-height:1.6;">
                                                    <strong style="color:#0f172a;">Track your progress</strong> and manage all your workout sessions
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <table role="presentation" cellspacing="0" cellpadding="0" border="0">
                                            <tr>
                                                <td style="padding-right:10px;vertical-align:top;font-size:16px;">🚀</td>
                                                <td style="font-size:14px;color:#475569;line-height:1.6;">
                                                    <strong style="color:#0f172a;">Unlock premium features</strong> and upgrade your plan anytime
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>

                            <!-- CTA Button -->
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0">
                                <tr>
                                    <td align="center" style="padding-bottom:12px;">
                                        <a href="{{ $verificationUrl }}" class="cta-btn"
                                            style="display:inline-block;background:linear-gradient(135deg,#2563eb,#4f46e5);color:#ffffff;font-size:16px;font-weight:700;text-decoration:none;padding:16px 40px;border-radius:10px;letter-spacing:0.2px;box-shadow:0 4px 14px rgba(79,70,229,0.35);">
                                            ✓ &nbsp; Verify Email Address
                                        </a>
                                    </td>
                                </tr>
                                <tr>
                                    <td align="center" style="padding-bottom:32px;">
                                        <p style="margin:0;font-size:12px;color:#94a3b8;">
                                            This link expires in <strong>60 minutes</strong>
                                        </p>
                                    </td>
                                </tr>
                            </table>

                            <!-- Fallback URL -->
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0"
                                style="margin-bottom:28px;">
                                <tr>
                                    <td style="background-color:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;padding:16px 20px;">
                                        <p style="margin:0 0 6px;font-size:11px;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:1px;">
                                            Button not working? Copy this link:
                                        </p>
                                        <p class="fallback-url" style="margin:0;font-size:13px;color:#4f46e5;word-break:break-all;line-height:1.6;">
                                            {{ $verificationUrl }}
                                        </p>
                                    </td>
                                </tr>
                            </table>

                            <!-- Security notice -->
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0"
                                style="margin-bottom:28px;">
                                <tr>
                                    <td style="background-color:#fefce8;border-left:3px solid #eab308;border-radius:0 6px 6px 0;padding:12px 16px;">
                                        <p style="margin:0;font-size:13px;color:#713f12;line-height:1.6;">
                                            🔒 <strong>Security notice:</strong> {{ config('app.name') }} will never ask for your password via email.
                                            If you didn't create this account, you can safely ignore this message.
                                        </p>
                                    </td>
                                </tr>
                            </table>

                            <!-- Divider -->
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0">
                                <tr><td style="border-top:1px solid #e2e8f0;padding-bottom:24px;"></td></tr>
                            </table>

                            <!-- Sign-off -->
                            <p style="margin:0 0 2px;font-size:14px;color:#64748b;">Cheers,</p>
                            <p style="margin:0;font-size:14px;font-weight:700;color:#0f172a;">
                                The {{ config('app.name') }} Team
                            </p>

                        </td>
                    </tr>

                    <!-- ── Footer ── -->
                    <tr>
                        <td class="footer"
                            style="background-color:#f8fafc;border-top:1px solid #e2e8f0;border-radius:0 0 16px 16px;padding:24px 48px;text-align:center;">
                            <p style="margin:0 0 6px;font-size:12px;color:#94a3b8;line-height:1.6;">
                                This is an automated message — please do not reply to this email.
                            </p>
                            <p style="margin:0;font-size:12px;color:#cbd5e1;">
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

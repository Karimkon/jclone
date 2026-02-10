<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name') }}</title>
</head>
<body style="margin: 0; padding: 0; background-color: #f3f4f6; font-family: Arial, Helvetica, sans-serif;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background-color: #f3f4f6;">
        <tr>
            <td align="center" style="padding: 40px 20px;">
                <table role="presentation" width="600" cellspacing="0" cellpadding="0" style="max-width: 600px; width: 100%;">
                    <!-- Header -->
                    <tr>
                        <td style="background: linear-gradient(135deg, #4f46e5, #7c3aed); padding: 30px 40px; border-radius: 12px 12px 0 0; text-align: center;">
                            <h1 style="color: #ffffff; margin: 0; font-size: 24px; font-weight: bold;">BebaMart</h1>
                        </td>
                    </tr>

                    <!-- Content -->
                    <tr>
                        <td style="background-color: #ffffff; padding: 40px; line-height: 1.6; color: #374151; font-size: 15px;">
                            {!! $content !!}
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td style="background-color: #f9fafb; padding: 24px 40px; border-top: 1px solid #e5e7eb; border-radius: 0 0 12px 12px; text-align: center;">
                            <p style="color: #9ca3af; font-size: 12px; margin: 0 0 8px;">
                                &copy; {{ date('Y') }} BebaMart. All rights reserved.
                            </p>
                            @if(!empty($unsubscribeUrl))
                                <p style="margin: 0;">
                                    <a href="{{ $unsubscribeUrl }}" style="color: #6366f1; font-size: 12px; text-decoration: underline;">Unsubscribe from our newsletter</a>
                                </p>
                            @endif
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>

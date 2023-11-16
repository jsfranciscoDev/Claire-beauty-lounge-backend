<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $mailData['title'] }}</title>
    <!-- Add any additional head elements or styles here -->
</head>
<body style="font-family: 'Arial', sans-serif; background-color: #f4f4f4; margin: 0; padding: 0;">

    <table role="presentation" cellspacing="0" cellpadding="0" width="100%" style="margin: 0; padding: 20px;">
        <tr>
            <td align="center">
                <table role="presentation" cellspacing="0" cellpadding="0" width="600" style="background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);">
                    <tr>
                        <td style="padding: 20px;">
                            <h2 style="color: #bd8c8c;">{{ $mailData['title'] }}</h2>
                            <p style="color: #666666;">{{ $mailData['body'] }}</p>

                            <!-- Additional content or customization can be added here -->

                            <p style="color: #888888; margin-top: 20px;">Thank you,</p>
                            <p style="color: #888888;">Claire Admin</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

</body>
</html>

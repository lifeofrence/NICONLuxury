<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
        }

        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }

        .message-content {
            white-space: pre-wrap;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="message-content">
            {!! nl2br(e($messageContent)) !!}
        </div>
        <hr style="margin: 30px 0; border: none; border-top: 1px solid #eee;">
        <p style="color: #666; font-size: 14px;">
            Best regards,<br>
            NICON Luxury Hotel Team
        </p>
    </div>
</body>

</html>
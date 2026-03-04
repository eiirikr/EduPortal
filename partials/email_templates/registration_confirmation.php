<?php ?>
<!DOCTYPE html>
<html>
<head>
    <style>
        body {
            font-family: Arial, sans-serif;
            color: #333;
        }
        .container {
            padding: 20px;
            background: #f7f7f7;
            border-radius: 10px;
            max-width: 600px;
            margin: auto;
        }
        .highlight {
            color: #4e54c8;
            font-weight: bold;
        }
        a {
            color: #4e54c8;
            text-decoration: none;
        }
    </style>
</head>
<body>
<div class="container">
    <p>Dear <strong><?= htmlspecialchars($first_name) . ' ' . htmlspecialchars($last_name) ?></strong>,</p>

    <p>Your registration has been successfully processed. Please use the credentials below:</p>

    <ul>
        <li>Username: <span class="highlight"><?= htmlspecialchars($email) ?></span></li>
        <li>Temporary Password: <span class="highlight"><?= htmlspecialchars($default_password) ?></span></li>
        <li>Access Portal: <a href="https://student.intercommerce.com.ph">https://student.intercommerce.com.ph</a></li>
    </ul>

    <p>Thank you,<br><strong>Intercommerce Network Services</strong></p>
</div>
</body>
</html>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Payment Receipt</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            padding: 40px;
            background-color: #f4f4f4;
            color: #333;
        }
        .receipt-container {
            background: #fff;
            padding: 30px 40px;
            border-radius: 10px;
            max-width: 600px;
            margin: 0 auto;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }
        .logo {
            width: 120px;
            margin-bottom: 20px;
        }
        h2 {
            text-align: center;
            margin-bottom: 20px;
            color: #0B0F1C;
        }
        .info {
            margin: 10px 0;
            font-size: 15px;
        }
        .info strong {
            display: inline-block;
            width: 140px;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 14px;
            color: #555;
        }
        .line {
            border-top: 1px solid #ddd;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="receipt-container">
        <div style="text-align: center;">
            <img src="{{ public_path('flat_connect_blue.png') }}" class="logo" alt="FlatConnect Logo">
        </div>

        <h2>Payment Receipt</h2>

        <div class="line"></div>

        <p class="info"><strong>Client Name:</strong> {{ $payment->client->first_name }} {{ $payment->client->last_name }}</p>
        <p class="info"><strong>Reference No:</strong> {{ $payment->reference }}</p>
        <p class="info"><strong>Amount Paid:</strong> PHP {{ number_format($payment->amount, 2) }}</p>
        <p class="info"><strong>Date Paid:</strong> {{ $payment->paid_at ? $payment->paid_at->format('F d, Y') : 'N/A' }}</p>
        <p class="info"><strong>Client IP:</strong> {{ $clientIp }}</p>

        <div class="footer">
            Thank you for choosing FlatConnect.<br>
            This receipt is computer generated and does not require a signature.
        </div>
    </div>
</body>
</html>

<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $subject ?? 'ایمیل' }}</title>
    <style>
        /* Reset & Base Styles */
        body, p, div {
            margin: 0;
            padding: 0;
        }
        body {
            font-family: 'Tahoma', sans-serif;
            background-color: #f7f7f7;
            color: #333;
            line-height: 1.6;
        }
        .container {
            width: 100%;
            max-width: 600px;
            margin: 20px auto;
            background-color: #ffffff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .header {
            text-align: center;
            padding-bottom: 20px;
            border-bottom: 1px solid #ddd;
        }
        .header img {
            max-width: 150px;
            height: auto;
        }
        .header h1 {
            font-size: 24px;
            margin-top: 10px;
        }
        .content {
            padding: 20px 0;
            font-size: 16px;
            line-height: 1.8;
        }
        .button-container {
            text-align: center;
            margin-top: 20px;
        }
        .button {
            display: inline-block;
            background-color: #007BFF;
            color: #ffffff !important;
            text-decoration: none;
            padding: 10px 20px;
            border-radius: 4px;
            font-weight: bold;
        }
        .footer {
            text-align: center;
            font-size: 12px;
            color: #888;
            border-top: 1px solid #ddd;
            padding-top: 20px;
            margin-top: 20px;
        }
        .footer a {
            color: #007BFF;
            text-decoration: none;
        }
        @media only screen and (max-width: 600px) {
            .container {
                padding: 15px;
            }
            .content {
                padding: 15px 0;
                font-size: 15px;
            }
        }
    </style>
</head>
<body>
<div class="container">
    <!-- Header Section -->
    <div class="header">
        <!-- You may insert your logo here -->
        {{-- <img src="{{ asset('path/to/logo.png') }}" alt="Logo"> --}}
        <h1>{{ $subject ?? 'ایمیل' }}</h1>
    </div>

    <!-- Content Section -->
    <div class="content">
        {!! $body !!}
    </div>

    <!-- Footer Section -->
    <div class="footer">
        <p>© {{ date('Y') }} شرکت شما. تمامی حقوق محفوظ است.</p>
    </div>
</div>
</body>
</html>

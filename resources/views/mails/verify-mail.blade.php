<!DOCTYPE html>
<html lang="fa">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>حساب کاربری خود را تأیید کنید</title>
    <style>
        body {
            font-family: Tahoma, Arial, sans-serif;
            background-color: #f5f5f5;
            margin: 0;
            padding: 0;
            text-align: center;
        }

        .container {
            width: 600px;
            margin: 40px auto;
            background-color: #fff;
            padding: 30px;
            border: 1px solid #ddd;
        }

        .logo {
            text-align: center !important;
        }

        .content {
            margin-top: 20px;
            line-height: 1.5;
        }

        .code {
            font-size: 32px;
            font-weight: bold;
            text-align: center;
            padding: 10px 20px;
            background-color: #FED7AA;
            border-radius: 5px;
        }

        .footer {
            text-align: center !important;
            margin-top: 30px;
            color: #5c5b5b;
        }

        p {
            text-align: right;
            font-size: 18px;
            direction: rtl;
        }
        .logo-image{
            height: 200px !important;
            width: 200px !important;
        }
    </style>
</head>
<body>
<div class="container">
    <div class="logo">
       اپلیکیشن میزونی!
    </div>
    <div class="content">
        <p>سلام :)</p>
        <p> برای تأیید حساب کاربری خود، لطفا ازً کد زیر استفاده کنید</p>
        <p class="code">{{$code}}</p>
        <p>این کد به مدت 15 دقیقه معتبر است. اگر در این مدت آن را وارد نکردید لطفاً درخواست مجدد کنید</p>
    </div>
    <div class="footer">
        <p>اگر این کد تأیید را درخواست نکرده اید، لطفا این ایمیل را نادیده بگیرید.</p>
        <p>با احترام</p>
        <p><a href="https://mizooni.app/">mizooni.app</a></p>
    </div>
</div>
</body>
</html>

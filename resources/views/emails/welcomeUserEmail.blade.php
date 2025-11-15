<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>مرحباً بك في مدونتنا</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700&display=swap');

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Tajawal', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            max-width: 500px;
            width: 100%;
            text-align: center;
        }

        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 40px 30px;
            color: white;
        }

        .welcome-icon {
            font-size: 4rem;
            margin-bottom: 15px;
            display: block;
        }

        h1 {
            font-size: 2.2rem;
            font-weight: 700;
            margin-bottom: 10px;
            line-height: 1.3;
        }

        .subtitle {
            font-size: 1.1rem;
            opacity: 0.9;
            font-weight: 300;
        }

        .content {
            padding: 40px 30px;
        }

        .user-name {
            color: #667eea;
            font-weight: 700;
            font-size: 1.4rem;
            margin: 15px 0;
            display: inline-block;
            background: linear-gradient(135deg, #667eea, #764ba2);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .message {
            color: #666;
            font-size: 1.1rem;
            line-height: 1.6;
            margin-bottom: 30px;
        }

        .features {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
            margin: 30px 0;
        }

        .feature {
            background: #f8f9ff;
            padding: 15px;
            border-radius: 12px;
            border: 2px solid #e8ecff;
            transition: transform 0.3s ease;
        }

        .feature:hover {
            transform: translateY(-5px);
        }

        .feature-icon {
            font-size: 2rem;
            margin-bottom: 10px;
            display: block;
        }

        .feature-text {
            font-size: 0.9rem;
            color: #555;
            font-weight: 500;
        }

        .cta-button {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 15px 40px;
            border: none;
            border-radius: 50px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s ease;
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
        }

        .cta-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 15px 30px rgba(102, 126, 234, 0.4);
        }

        .footer {
            background: #f8f9ff;
            padding: 20px;
            border-top: 1px solid #e8ecff;
            color: #888;
            font-size: 0.9rem;
        }

        @media (max-width: 480px) {
            .features {
                grid-template-columns: 1fr;
            }

            h1 {
                font-size: 1.8rem;
            }

            .header {
                padding: 30px 20px;
            }

            .content {
                padding: 30px 20px;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <span class="welcome-icon">🎉</span>
            <h1>مرحباً بك في عالمنا</h1>
            <p class="subtitle">نحن سعداء بانضمامك إلينا</p>
        </div>

        <div class="content">
            <p class="message">يسعدنا ترحيبك في مجتمعنا المتميز</p>

            <div class="user-name">
                {{ $user->name }} 👋
            </div>

            <p class="message">
                أنت الآن جزء من مجتمعنا الرائع. استعد لاكتشاف عالم من المحتوى المميز والتواصل مع كتاب مبدعين.
            </p>

            <div class="features">
                <div class="feature">
                    <span class="feature-icon">✍️</span>
                    <div class="feature-text">اكتب مقالاتك</div>
                </div>
                <div class="feature">
                    <span class="feature-icon">💬</span>
                    <div class="feature-text">تفاعل مع الآخرين</div>
                </div>
                <div class="feature">
                    <span class="feature-icon">🔔</span>
                    <div class="feature-text">كن على اطلاع</div>
                </div>
            </div>

            <a href="{{ url('/') }}" class="cta-button">
                ابدأ رحلتك الآن
            </a>
        </div>

        <div class="footer">
            <p>مع تحيات فريق {{ config('app.name') }} ❤️</p>
            <p>© {{ date('Y') }} جميع الحقوق محفوظة</p>
        </div>
    </div>
</body>

</html>
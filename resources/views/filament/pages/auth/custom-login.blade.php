<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />

    {{-- CSS Files --}}
    <link rel="stylesheet" href="{{ asset('assets/css/bootstrap.rtl.min.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/fonts/flaticon.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/css/owl.carousel.min.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/css/owl.theme.default.min.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/css/animate.min.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/css/slick.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/css/slick-theme.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/css/magnific-popup.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/css/dark.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/css/responsive.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/css/rtl.css') }}" />

    <style>
        .form-group input,
        .form-group select,
        .form-group textarea {
            border: 1px solid #dee2e6;
            border-radius: 5px;
            padding: 10px;
            width: 100%;
            background-color: #fff;
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: 2px solid #ec1f1f;
        }

        .custom-navbar {
            background-color: #ec1f1f;
        }

        #errorMessages {
            color: #dc3545;
            margin-bottom: 15px;
        }

        #successMessage {
            color: #28a745;
            margin-bottom: 15px;
        }

        label {
            justify-content: center;
            display: flex;
        }

        input {
            text-align: center;
        }

        .contact-section .contact-form {
            margin-top: 40px;
            padding: 40px !important;
        }

        .footer-area .copyright-area {
            border-top: 1px solid #F3A524 !important;
        }
    </style>

    <title>تسجيل الدخول</title>
    <link rel="icon" type="image/png" href="{{ asset('assets/img/favicon.png') }}" />
</head>

<body>

    <section class="contact-section pt-100 pb-100">
        <div class="container">
            <img src="https://wecan.click/assets/img/logo.png" alt=""
                style="display: flex; justify-content: center; margin-bottom: 20px; margin-left: auto; margin-right: auto;" />

            <div class="section-title" style="margin-bottom: 0px;">
                <h2 style="margin-bottom: 0px;">تسجيل الدخول</h2>
            </div>

            <div class="row justify-content-center">
                <div class="col-lg-6">
                    <div class="contact-form">
                        {{-- Laravel validation messages --}}
                        @if ($errors->any())
                            <div id="errorMessages">
                                @foreach ($errors->all() as $error)
                                    {{ $error }}<br>
                                @endforeach
                            </div>
                        @endif

                        @if (session('status'))
                            <div id="successMessage">
                                {{ session('status') }}
                            </div>
                        @endif

                        <form method="POST" action="{{ route('filament.admin.auth.login') }}">
                            @csrf
                            <div class="form-group">
                                <label for="email">البريد الالكتروني</label>
                                <input type="email" name="email" id="email"
                                    value="{{ old('email') }}"
                                    placeholder="البريد الالكتروني"
                                    required autofocus>
                            </div>

                            <div class="form-group">
                                <label for="password">كلمة المرور</label>
                                <input type="password" name="password" id="password"
                                    placeholder="كلمة المرور" required>
                            </div>

                            <button type="submit" class="default-btn w-100"
                                style="height: 50px; background-color: #ec1f1f; text-align: center; color: white; border-radius: 5px;">
                                تسجيل الدخول
                            </button>
                        </form>

                        <div style="text-align: center; margin-top: 20px">
                            <a href="{{ url(env('WEBSITE_URL') . '/doctor-register.php') }}" class="register-btn">
                                إذا لم تكن مسجلاً، انقر هنا للتسجيل
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <footer class="footer-area" style="background-color: #ec1f1f;">
        <div class="copyright-area">
            <div class="container">
                <div class="row">
                    <div class="col-lg-12">
                        <p>© جميع الحقوق محفوظة لتطبيق نستطيع</p>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    {{-- JS Files --}}
    <script src="{{ asset('assets/js/bootstrap.bundle.min.js') }}"></script>
</body>

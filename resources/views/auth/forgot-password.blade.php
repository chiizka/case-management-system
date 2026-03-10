<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="DOLE Case Management System">
    <meta name="author" content="">

    <title>DOLE CMS - Forgot Password</title>

    <!-- Fonts & Icons -->
    <link href="{{ asset('vendor/fontawesome-free/css/all.min.css') }}" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- SB Admin 2 CSS -->
    <link href="{{ asset('css/sb-admin-2.min.css') }}" rel="stylesheet">

    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        html, body { height: 100%; }

        body {
            font-family: 'Nunito', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .login-container {
            width: 100%;
            max-width: 520px;
            margin: 0 auto;
        }

        .card {
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            border: none;
            background: #ffffff;
        }

        .card-body {
            padding: 60px 50px;
        }

        .password-icon {
            font-size: 3.2rem;
            color: #667eea;
            margin-bottom: 1.2rem;
        }

        .text-center h1 {
            color: #2d3748;
            font-weight: 700;
            font-size: 28px;
            margin-bottom: 12px;
        }

        .text-gray-600 {
            color: #718096;
            font-size: 15.5px;
            margin-bottom: 35px;
        }

        .form-control-user {
            height: 58px;
            border-radius: 12px;
            padding: 0 22px;
            font-size: 15.5px;
            border: 2px solid #e2e8f0;
            transition: all 0.3s ease;
            width: 100%;
            color: #495057;
            background-color: #fff;
        }

        .form-control-user:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.12);
            outline: none;
        }

        .form-control-user.is-invalid {
            border-color: #fc8181;
        }

        .btn-user {
            display: block;
            width: 100%;
            height: 58px;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 600;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            margin-top: 10px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn-user:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.4);
            background: linear-gradient(135deg, #764ba2 0%, #667eea 100%);
            color: white;
            text-decoration: none;
        }

        .btn-user:disabled {
            opacity: 0.7;
            cursor: not-allowed;
            transform: none;
        }

        .alert {
            border-radius: 12px;
            padding: 16px 20px;
            margin-bottom: 20px;
            border: none;
        }

        .alert-success {
            background-color: #c6f6d5;
            color: #276749;
        }

        .alert-danger {
            background-color: #fed7d7;
            color: #c53030;
        }

        .alert-dismissible .close {
            position: absolute;
            top: 0;
            right: 0;
            padding: 0.75rem 1.25rem;
            color: inherit;
            background: transparent;
            border: 0;
            font-size: 1.5rem;
            font-weight: 700;
            line-height: 1;
            opacity: 0.5;
            cursor: pointer;
        }

        .alert-dismissible .close:hover {
            opacity: 0.75;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .text-danger {
            color: #c53030;
            font-size: 13px;
            margin-top: 6px;
            display: block;
        }

        hr {
            margin: 30px 0;
            border: 0;
            border-top: 1px solid #e2e8f0;
        }

        a {
            color: #667eea;
            text-decoration: none;
            transition: color 0.2s;
        }

        a:hover {
            color: #764ba2;
            text-decoration: none;
        }

        .small {
            font-size: 14px;
            font-weight: 600;
        }

        @media (max-width: 576px) {
            .card-body { padding: 45px 30px; }
        }
    </style>
</head>

<body>

    <div class="login-container">
        <div class="card">
            <div class="card-body">

                <div class="text-center">
                    <i class="fas fa-envelope-open-text password-icon"></i>
                    <h1>Forgot Your Password?</h1>
                    <p class="text-gray-600">
                        No worries! Enter your registered email address and we'll send you 
                        a link to reset your password.
                    </p>
                </div>

                {{-- Success message after sending the link --}}
                @if (session('status'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle mr-2"></i>
                        {{ session('status') }}
                        <button type="button" class="close" data-dismiss="alert">&times;</button>
                    </div>
                @endif

                {{-- General error --}}
                @if ($errors->any() && !$errors->has('email'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-triangle mr-2"></i>
                        {{ $errors->first() }}
                        <button type="button" class="close" data-dismiss="alert">&times;</button>
                    </div>
                @endif

                <form method="POST" action="{{ route('password.email') }}" id="forgot-form">
                    @csrf

                    <div class="form-group">
                        <input 
                            type="email" 
                            name="email" 
                            class="form-control-user @error('email') is-invalid @enderror"
                            placeholder="Enter your email address"
                            value="{{ old('email') }}" 
                            required 
                            autofocus>

                        @error('email')
                            <span class="text-danger">
                                <i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}
                            </span>
                        @enderror
                    </div>

                    <button type="submit" class="btn-user" id="submit-btn">
                        <i class="fas fa-paper-plane mr-2"></i> SEND RESET LINK
                    </button>
                </form>

                <hr>

                <div class="text-center">
                    <a href="{{ route('login') }}" class="small">
                        <i class="fas fa-arrow-left mr-1"></i> Back to Login
                    </a>
                </div>

            </div>
        </div>

        <div class="text-center mt-4">
            <small style="color: rgba(255,255,255,0.6);">
                Department of Labor and Employment • Republic of the Philippines
            </small>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="{{ asset('vendor/jquery/jquery.min.js') }}"></script>
    <script src="{{ asset('vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>

    <script>
        // Show loading state on submit
        $('#forgot-form').on('submit', function () {
            const btn = $('#submit-btn');
            btn.prop('disabled', true).html(`
                <span class="spinner-border spinner-border-sm mr-2" role="status"></span> SENDING...
            `);
        });

        // Auto-hide success alert after 8 seconds
        setTimeout(function () {
            $('.alert-success').fadeOut('slow');
        }, 8000);
    </script>

</body>
</html>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>Set Your Password</title>

    <!-- Custom fonts for this template-->
    <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">

    <!-- Custom styles for this template-->
    <link href="css/sb-admin-2.min.css" rel="stylesheet">

    <!-- Enhanced modern styles -->
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html, body {
            margin: 0;
            padding: 0;
            height: 100%;
        }

        body {
            font-family: 'Nunito', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .login-container {
            width: 100%;
            max-width: 600px;
        }

        .card {
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            backdrop-filter: blur(10px);
            border: none;
        }

        .card-body {
            padding: 60px 50px;
            background: #ffffff;
        }

        .password-icon {
            font-size: 3rem;
            color: #667eea;
            margin-bottom: 1rem;
        }

        .text-center h1 {
            color: #2d3748;
            font-weight: 700;
            margin-bottom: 12px;
            font-size: 28px;
        }

        .text-gray-600 {
            color: #718096;
            font-size: 15px;
            margin-bottom: 30px;
        }

        .form-group {
            margin-bottom: 20px;
            position: relative;
        }

        .form-control-user {
            border-radius: 12px;
            padding: 16px 20px;
            font-size: 15px;
            border: 2px solid #e2e8f0;
            transition: all 0.3s ease;
            width: 100%;
        }

        .form-control-user:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
            outline: none;
        }

        .form-control-user::placeholder {
            color: #a0aec0;
        }

        .form-control-user[readonly] {
            background-color: #f7fafc;
            cursor: not-allowed;
        }

        .is-invalid {
            border-color: #fc8181;
        }

        .btn-user {
            border-radius: 12px;
            padding: 16px 20px;
            font-size: 16px;
            font-weight: 600;
            transition: all 0.3s ease;
            border: none;
            width: 100%;
            margin-top: 10px;
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            position: relative;
            overflow: hidden;
        }

        .btn-primary::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
            transition: left 0.5s;
        }

        .btn-primary:hover::before {
            left: 100%;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.4);
            background: linear-gradient(135deg, #764ba2 0%, #667eea 100%);
        }

        .btn-primary:active {
            transform: translateY(0);
        }

        .alert {
            border-radius: 12px;
            padding: 15px 20px;
            margin-bottom: 20px;
            border: none;
        }

        .alert-danger {
            background-color: #fed7d7;
            color: #c53030;
        }

        hr {
            margin: 30px 0;
            border-top: 1px solid #e2e8f0;
        }

        .small {
            font-size: 14px;
            color: #667eea;
            text-decoration: none;
            transition: all 0.3s ease;
            font-weight: 600;
        }

        .small:hover {
            color: #764ba2;
            text-decoration: none;
        }

        .password-strength {
            height: 4px;
            background: #e2e8f0;
            border-radius: 2px;
            margin-top: 8px;
            overflow: hidden;
            display: none;
        }

        .password-strength-bar {
            height: 100%;
            width: 0;
            transition: all 0.3s ease;
            border-radius: 2px;
        }

        .form-text {
            font-size: 13px;
            color: #718096;
            margin-top: 8px;
            display: block;
            text-align: center;
        }

        @media (max-width: 576px) {
            .card-body {
                padding: 40px 30px;
            }

            .text-center h1 {
                font-size: 24px;
            }
        }
    </style>
</head>

<body class="bg-gradient-primary">

    <div class="container login-container">
        <div class="row justify-content-center w-100">
            <div class="col-xl-6 col-lg-8 col-md-9">
                <div class="card o-hidden border-0 shadow-lg">
                    <div class="card-body p-0">
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="card-body">
                                    <div class="text-center">
                                        <i class="fas fa-key password-icon"></i>
                                        <h1 class="h4 text-gray-900 mb-2">Set Your Password</h1>
                                        <p class="mb-4 text-gray-600">
                                            Please create a secure password for your account.
                                        </p>
                                    </div>

                                    @if($errors->any())
                                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                            <i class="fas fa-exclamation-triangle me-2"></i>
                                            @foreach($errors->all() as $error)
                                                {{ $error }}<br>
                                            @endforeach
                                            <button type="button" class="close" data-dismiss="alert">
                                                <span>&times;</span>
                                            </button>
                                        </div>
                                    @endif

                                    <form class="user" method="POST" action="{{ route('password.update') }}">
                                        @csrf
                                        <input type="hidden" name="token" value="{{ $token }}">
                                        <input type="hidden" name="email" value="{{ $email }}">

                                        <div class="form-group">
                                            <input type="email" 
                                                   name="email" 
                                                   class="form-control form-control-user"
                                                   value="{{ $email }}" 
                                                   readonly 
                                                   placeholder="Email Address">
                                        </div>

                                        <div class="form-group">
                                            <input type="password" 
                                                   name="password" 
                                                   class="form-control form-control-user @error('password') is-invalid @enderror"
                                                   placeholder="New Password" 
                                                   required 
                                                   id="password-input">
                                            <small class="form-text text-muted">
                                                Password must be at least 8 characters
                                            </small>
                                            <div class="password-strength" id="password-strength">
                                                <div class="password-strength-bar" id="strength-bar"></div>
                                            </div>
                                        </div>

                                        <div class="form-group">
                                            <input type="password" 
                                                   name="password_confirmation" 
                                                   class="form-control form-control-user"
                                                   placeholder="Confirm New Password" 
                                                   required 
                                                   id="confirm-password-input">
                                        </div>

                                        <button type="submit" class="btn btn-primary btn-user btn-block">
                                            <i class="fas fa-check me-2"></i>
                                            Set Password
                                        </button>
                                    </form>

                                    <hr>

                                    <div class="text-center">
                                        <a class="small text-gray-600" href="{{ route('login') }}">
                                            <i class="fas fa-arrow-left me-1"></i>
                                            Back to Login
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap core JavaScript-->
    <script src="vendor/jquery/jquery.min.js"></script>
    <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>

    <!-- Core plugin JavaScript-->
    <script src="vendor/jquery-easing/jquery.easing.min.js"></script>

    <!-- Custom scripts for all pages-->
    <script src="js/sb-admin-2.min.js"></script>

    <script>
        $(document).ready(function() {
            // Password strength indicator
            $('#password-input').on('input', function() {
                const password = $(this).val();
                const strengthBar = $('#strength-bar');
                const strengthContainer = $('#password-strength');

                if (password.length > 0) {
                    strengthContainer.show();

                    let strength = 0;
                    if (password.length >= 8) strength += 25;
                    if (password.match(/[a-z]/) && password.match(/[A-Z]/)) strength += 25;
                    if (password.match(/[0-9]/)) strength += 25;
                    if (password.match(/[^a-zA-Z0-9]/)) strength += 25;

                    strengthBar.css('width', strength + '%');

                    if (strength <= 25) {
                        strengthBar.css('background-color', '#fc8181');
                    } else if (strength <= 50) {
                        strengthBar.css('background-color', '#f6ad55');
                    } else if (strength <= 75) {
                        strengthBar.css('background-color', '#68d391');
                    } else {
                        strengthBar.css('background-color', '#48bb78');
                    }
                } else {
                    strengthContainer.hide();
                }
            });

            // Auto-dismiss alerts after 5 seconds
            setTimeout(function() {
                $('.alert').fadeOut();
            }, 5000);
        });
    </script>

</body>

</html>
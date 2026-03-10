<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="DOLE Case Management System">
    <meta name="author" content="">

    <title>DOLE Case Management System - Login</title>

    <!-- Fonts & Icons -->
    <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">

    <!-- SB Admin 2 CSS -->
    <link href="css/sb-admin-2.min.css" rel="stylesheet">

    <style>
        body.bg-gradient-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .login-card {
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 25px 60px rgba(0, 0, 0, 0.3);
            max-width: 920px;
        }

        .login-left {
            background: linear-gradient(135deg, #1e40af, #3b82f6);
            color: white;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 4rem 2rem;
        }

        .logo-circle {
            width: 110px;
            height: 110px;
            background: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 10px 30px rgba(0,0,0,0.25);
            margin-bottom: 1.5rem;
        }

        .logo-circle img {
            width: 75px;
            height: 75px;
        }

        .form-control-user {
            height: 55px;
            border-radius: 50px;
            padding: 0 1.5rem;
            font-size: 1rem;
            border: 2px solid #e2e8f0;
        }

        .form-control-user:focus {
            border-color: #4e73df;
            box-shadow: 0 0 0 0.2rem rgba(78,115,223,0.25);
        }

        .btn-login {
            height: 55px;
            border-radius: 50px;
            font-size: 1.1rem;
            font-weight: 600;
            background: linear-gradient(90deg, #4e73df, #2653d4);
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(78,115,223,0.4);
        }
    </style>
</head>

<body class="bg-gradient-primary">

    <div class="container">

        <div class="row justify-content-center">

            <div class="col-xl-10 col-lg-12 col-md-10">

                <div class="login-card card o-hidden border-0 shadow-lg">

                    <div class="row g-0">

                        <!-- Left Branding Side -->
                        <div class="col-lg-5 login-left d-none d-lg-flex">
                            <div class="text-center">
                                <div class="logo-circle mx-auto">
                                    <img src="{{ asset('img/dole_logo.png') }}" 
                                         alt="DOLE Logo" 
                                         style="width: 78px; height: 78px;">
                                </div>
                                <h1 class="h3 text-white font-weight-bold mb-1">DOLE Case Management</h1>
                                <p class="text-white-50">Labor Standards Enforcement System</p>
                            </div>
                        </div>

                        <!-- Login Form Side -->
                        <div class="col-lg-7 bg-white p-5">

                            <div class="text-center mb-4">
                                <i class="fas fa-lock fa-3x text-primary mb-3"></i>
                                <h4 class="text-gray-900">Welcome Back!</h4>
                                <p class="text-muted">Sign in to continue</p>
                            </div>

                            <form class="user" method="POST" action="{{ route('login.post') }}">
                                @csrf

                                @if($errors->any())
                                    <div class="alert alert-danger py-2">
                                        <small>{{ $errors->first() }}</small>
                                    </div>
                                @endif

                                <div class="form-group">
                                    <input type="email" name="email" class="form-control form-control-user" 
                                           placeholder="Email Address" required autofocus>
                                </div>

                                <div class="form-group">
                                    <input type="password" name="password" class="form-control form-control-user" 
                                           placeholder="Password" required>
                                </div>

                                <button type="submit" class="btn btn-primary btn-user btn-block btn-login">
                                    <i class="fas fa-sign-in-alt mr-2"></i> SIGN IN
                                </button>

                                <hr>

                                <div class="text-center">
                                    <a class="small text-muted" href="{{ route('password.request') }}">Forgot Password?</a>
                                </div>
                            </form>

                        </div>

                    </div>
                </div>

                <div class="text-center mt-4">
                    <small class="text-white-50">Department of Labor and Employment • Republic of the Philippines</small>
                </div>

            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="vendor/jquery/jquery.min.js"></script>
    <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="vendor/jquery-easing/jquery.easing.min.js"></script>
    <script src="js/sb-admin-2.min.js"></script>

    <script>
        // Show loading on submit
        $('.user').on('submit', function() {
            const btn = $(this).find('button[type="submit"]');
            btn.prop('disabled', true).html(`
                <span class="spinner-border spinner-border-sm mr-2"></span> SIGNING IN...
            `);
        });
    </script>

</body>
</html>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>2FA Verification - Case Management System</title>

    <!-- Custom fonts for this template-->
    <link href="{{ asset('vendor/fontawesome-free/css/all.min.css') }}" rel="stylesheet" type="text/css">
    <link
        href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i"
        rel="stylesheet">

    <!-- Custom styles for this template-->
    <link href="{{ asset('css/sb-admin-2.min.css') }}" rel="stylesheet">

    <!-- Additional styles -->
    <style>
        .bg-gradient-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        
        .login-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .form-control-user {
            font-size: 1.2rem;
            border-radius: 10rem;
            padding: 1.5rem 1rem;
            text-align: center;
            letter-spacing: 0.5rem;
            font-weight: bold;
        }
        
        .btn-user {
            font-size: 0.8rem;
            border-radius: 10rem;
            padding: 0.75rem 1rem;
        }
        
        .btn-primary {
            background-color: #4e73df;
            border-color: #4e73df;
        }
        
        .btn-primary:hover {
            background-color: #2e59d9;
            border-color: #2653d4;
        }
        
        .text-gray-900 {
            color: #3a3b45 !important;
        }
        
        .small {
            font-size: 0.875rem;
        }
        
        .verification-icon {
            font-size: 3rem;
            color: #4e73df;
            margin-bottom: 1rem;
        }
        
        .countdown {
            font-size: 0.875rem;
            color: #858796;
        }
        
        .resend-section {
            margin-top: 1rem;
            padding-top: 1rem;
            border-top: 1px solid #e3e6f0;
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
                                <div class="p-5">
                                    <div class="text-center">
                                        <i class="fas fa-shield-alt verification-icon"></i>
                                        <h1 class="h4 text-gray-900 mb-2">Verify Your Identity</h1>
                                        <p class="mb-4 text-gray-600">
                                            We've sent a 6-digit verification code to your email address. 
                                            Please enter it below to complete your login.
                                        </p>
                                    </div>

                                    <!-- Success Message -->
                                    @if(session('success'))
                                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                                            <i class="fas fa-check-circle me-2"></i>
                                            {{ session('success') }}
                                            <button type="button" class="close" data-dismiss="alert">
                                                <span>&times;</span>
                                            </button>
                                        </div>
                                    @endif

                                    <!-- Error Messages -->
                                    @if($errors->any())
                                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                            <i class="fas fa-exclamation-triangle me-2"></i>
                                            {{ $errors->first() }}
                                            <button type="button" class="close" data-dismiss="alert">
                                                <span>&times;</span>
                                            </button>
                                        </div>
                                    @endif

                                    <form class="user" method="POST" action="{{ route('2fa.verify.post') }}">
                                        @csrf

                                        <div class="form-group">
                                            <input type="text" 
                                                   name="otp_code" 
                                                   class="form-control form-control-user" 
                                                   id="otpCode"
                                                   placeholder="000000"
                                                   maxlength="6"
                                                   pattern="[0-9]{6}"
                                                   autocomplete="one-time-code"
                                                   required
                                                   autofocus>
                                            <small class="form-text text-muted text-center mt-2">
                                                Enter the 6-digit code from your email
                                            </small>
                                        </div>

                                        <button type="submit" class="btn btn-primary btn-user btn-block">
                                            <i class="fas fa-check me-2"></i>
                                            Verify Code
                                        </button>
                                    </form>

                                    <div class="resend-section text-center">
                                        <p class="small text-gray-600">Didn't receive the code?</p>
                                        <form method="POST" action="{{ route('2fa.resend') }}" style="display: inline;">
                                            @csrf
                                            <button type="submit" class="btn btn-link btn-sm p-0">
                                                <i class="fas fa-redo me-1"></i>
                                                Resend Code
                                            </button>
                                        </form>
                                    </div>

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
    <script src="{{ asset('vendor/jquery/jquery.min.js') }}"></script>
    <script src="{{ asset('vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>

    <!-- Core plugin JavaScript-->
    <script src="{{ asset('vendor/jquery-easing/jquery.easing.min.js') }}"></script>

    <!-- Custom scripts for all pages-->
    <script src="{{ asset('js/sb-admin-2.min.js') }}"></script>

    <script>
        $(document).ready(function() {
            // Auto-focus and format OTP input
            $('#otpCode').on('input', function() {
                this.value = this.value.replace(/[^0-9]/g, '');
                if (this.value.length === 6) {
                    $(this).closest('form').submit();
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
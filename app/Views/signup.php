<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SignUp - Smart Home System</title>

    <!-- Bootstrap CSS -->
    <link href="<?= base_url('assets/css/bootstrap.min.css') ?>" rel="stylesheet">

    <!-- Bootstrap Icons CSS -->
    <link href="<?= base_url('assets/bootstrap-icons/bootstrap-icons.css') ?>" rel="stylesheet">


    <style>
        body {
            background-color: #f8f9fa;
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .login-card {
            max-width: 400px;
            width: 100%;
            border-radius: 10px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        }

        .login-card .card-body {
            padding: 2rem;
        }

        .login-card h5 {
            font-weight: bold;
            text-align: center;
        }

        .form-control:focus {
            box-shadow: none;
        }
    </style>
</head>

<body>

    <div class="container">
        <div class="card login-card mx-auto">
            <div class="card-body">

                <!-- HEADER -->
                <h5 class="text-success mb-4">Smart Home System Login</h5>

                <!-- LOGIN FORM -->
                
                <?php if (session()->getFlashdata('error')): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <strong><?= esc(session()->getFlashdata('error')) ?></strong>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    <?php endif; ?>
                    
                    <form action="<?= base_url('/auth/signup') ?>" method="post">
                    <!-- EMAIL -->
                    <div class="mb-3">
                        <label for="username" class="form-label">Username</label>
                        <input type="text" name="username" id="username" class="form-control" placeholder="Create your Username"
                            required>
                    </div>

                    <!-- PASSWORD -->
                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" name="password" id="password" class="form-control"
                            placeholder="Create your password" required>
                    
                        </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Confirm Password</label>
                        <input type="password" name="confirm_password" id="password" class="form-control"
                            placeholder="Confirm your password" required>
                    </div>

                    <!-- REMEMBER ME -->
                    <!-- <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" value="" id="rememberMe">
                        <label class="form-check-label" for="rememberMe">
                            Remember Me
                        </label>
                    </div> -->

                    <!-- SUBMIT BUTTON -->
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">Sign Up</button>
                    </div>

                </form>

                <!-- FOOTER -->
                <p class="text-center text-muted mt-3 mb-0">
                    &copy; <?= date('Y') ?> Smart Home System
                </p>

            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="<?= base_url('assets/js/bootstrap.bundle.min.js') ?>"></script>
</body>

</html>
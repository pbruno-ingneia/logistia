<!doctype html>
<html lang="it" data-layout="vertical" data-topbar="light" data-sidebar="dark" data-sidebar-size="lg" data-sidebar-image="none" data-preloader="disable">

<head>

    <meta charset="utf-8" />
    <title>Logistia.it</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta content="Logistia.it" name="description" />
    <meta content="Themesbrand" name="author" />
    <!-- App favicon -->
    <link rel="shortcut icon" href="/base_icon_white_background">

    <script src="/default/assets/js/layout.js"></script>
    <link href="/default/assets/css/bootstrap.min.css" rel="stylesheet" type="text/css" />
    <link href="/default/assets/css/icons.min.css" rel="stylesheet" type="text/css" />
    <link href="/default/assets/css/app.min.css" rel="stylesheet" type="text/css" />
    <link href="/default/assets/css/custom.min.css" rel="stylesheet" type="text/css" />

    <style>
        .auth-bg-cover {
            background: linear-gradient(-90deg, #0ab39c 10%, #405189);

        }
    </style>
</head>

<body>

<!-- auth-page wrapper -->
<div class="auth-page-wrapper auth-bg-cover py-5 d-flex justify-content-center align-items-center min-vh-100">

    <!-- auth-page content -->
    <div class="auth-page-content overflow-hidden pt-lg-5">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <div class=" overflow-hidden">
                        <div class="row g-0">

                            <div class="col-lg-4"></div>

                            <div class="col-lg-4" style="background: white;">
                                <div class="p-lg-5 p-4" style="padding:20px!important">
                                    <div>
                                        <a class="d-block">
                                            <img src="/base_logo_white_background.png" style="margin: 0 auto 0 auto;display: block;width: 100%;">
                                        </a>
                                    </div>

                                    <div class="mt-4">
                                        <form method="post">

                                            <div class="mb-3">
                                                <label for="username" class="form-label">Email </label>
                                                <input type="email" class="form-control" id="email" name="email" placeholder="Email" required>
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label" for="password-input">Password</label>
                                                <input type="password" class="form-control pe-5 password-input" name="password" placeholder="password" id="password-input" required>
                                            </div>

                                            <div class="mt-4">
                                                <input style="background: linear-gradient(-90deg, #0ab39c 10%, #405189);
border:none;" class="btn btn-success w-100" type="submit" name="login" value="Login">
                                            </div>

                                        </form>
                                    </div>

                                </div>
                            </div>

                            <div class="col-lg-4"></div>
                            <!-- end col -->
                        </div>
                        <!-- end row -->
                    </div>
                    <!-- end card -->
                </div>
                <!-- end col -->

            </div>
            <!-- end row -->
        </div>
        <!-- end container -->
    </div>
    <!-- end auth page content -->

    <!-- footer
    <footer class="footer">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <div class="text-center">
                        <p class="mb-0">&copy;
                            <script>document.write(new Date().getFullYear())</script> Velzon. Crafted with <i class="mdi mdi-heart text-danger"></i> by Themesbrand
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </footer>
     end Footer -->
</div>
<!-- end auth-page-wrapper -->

<!-- JAVASCRIPT -->
<script src="/default/assets/libs/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="/default/assets/libs/simplebar/simplebar.min.js"></script>
<script src="/default/assets/libs/node-waves/waves.min.js"></script>
<script src="/default/assets/libs/feather-icons/feather.min.js"></script>
<script src="/default/assets/js/pages/plugins/lord-icon-2.1.0.js"></script>
<script src="/default/assets/js/plugins.js"></script>

<!-- password-addon init -->
<script src="/default/assets/js/pages/password-addon.init.js"></script>
</body>

</html>
<?php
if (session_status() === PHP_SESSION_NONE) session_start();
if (!defined('SITE_NAME')) define('SITE_NAME', 'Ma Boutique');
$pageTitle = $pageTitle ?? 'Boutique';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= SITE_NAME ?> - <?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?></title>
    <link rel="stylesheet" href="/projet/assets/css/neumorphic.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="/projet/assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg neumorphic mb-12">
        <div class="container">
            <a class="navbar-brand" href="/projet/index.php">
                <video src="/projet/assets/vids/SHOP(5).mp4" class="logo" alt="Logo" loop playsinline autoplay height="50px" width="50px" style="border:2px solid blue; padding:4px;"></video>
            </a>
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav"
                aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <i class="bi bi-list" style="font-size: 2rem;"></i>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ml-auto">
                    <li class="nav-item active"><a class="nav-link" href="/projet/index.php">Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="#">About</a></li>
                    <li class="nav-item"><a class="nav-link" href="#">Services</a></li>
                    <?php if (!isset($_SESSION['user'])): ?>
                        <li class="nav-item"><a class="nav-link" href="#" data-toggle="modal" data-target="#loginModal">Login</a></li>
                        <li class="nav-item"><a class="nav-link" href="#" data-toggle="modal" data-target="#registerModal">Register</a></li>
                    <?php endif; ?>
                    <li class="nav-item"><a class="nav-link" href="#">Contact</a></li>
                </ul>
                <div class="social-icons ml-5 d-none d-lg-flex">
                    <a href="#" class="fab fa-facebook-f" aria-label="Facebook"></a>
                    <a href="#" class="fab fa-twitter" aria-label="Twitter"></a>
                    <a href="#" class="fab fa-linkedin-in" aria-label="LinkedIn"></a>
                    <a href="#" class="fab fa-instagram" aria-label="Instagram"></a>
                </div>
            </div>
            <div class="d-flex">
                <a href="/projet/cart/view.php" class="btn btn-outline-primary me-2">
                    <i class="bi bi-cart"></i>
                    <span class="cart-count"><?= $_SESSION['cart_count'] ?? 0 ?></span>
                </a>
                <?php if (isset($_SESSION['user'])): ?>
                    <?php if ($_SESSION['user']['role'] === 'admin'): ?>
                        <a href="/projet/admin/dashboard.php" class="btn btn-primary">
                            <i class="bi bi-person"></i> <span class="d-none d-md-inline">Admin Dashboard</span>
                        </a>
                    <?php else: ?>
                        <a href="/projet/user/profile.php" class="btn btn-primary">
                            <i class="bi bi-person"></i> <span class="d-none d-md-inline">Mon compte</span>
                        </a>
                    <?php endif; ?>
                <?php else: ?>
                    <a href="#" class="btn btn-primary" data-toggle="modal" data-target="#loginModal">
                        <i class="bi bi-box-arrow-in-right"></i> <span class="d-none d-md-inline">Connexion</span>
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <!-- Login Modal -->
    <div class="modal fade" id="loginModal" tabindex="-1" role="dialog" aria-labelledby="loginModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content neumorphism">
                <form id="loginForm" action="/projet/user/login.php" method="POST">
                    <div class="modal-header">
                        <h5 class="modal-title" id="loginModalLabel">Login</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="loginEmail">Email address</label>
                            <input type="email" name="email" class="form-control" id="loginEmail" placeholder="Your Email" required>
                            <div class="invalid-feedback">Please enter a valid email address.</div>
                        </div>
                        <div class="form-group">
                            <label for="loginPassword">Password</label>
                            <input type="password" name="password" class="form-control" id="loginPassword" placeholder="Your Password" required minlength="8">
                            <div class="invalid-feedback">Please enter a valid password (minimum 8 characters).</div>
                        </div>
                        <?php if (isset($_SESSION['error'])): ?>
                            <div class="alert alert-danger"><?= $_SESSION['error'] ?></div>
                            <?php unset($_SESSION['error']); ?>
                        <?php endif; ?>
                        <?php if (isset($_SESSION['success'])): ?>
                            <div class="alert alert-success"><?= $_SESSION['success'] ?></div>
                            <?php unset($_SESSION['success']); ?>
                        <?php endif; ?>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <p class="text-center mt-2">Don't have an account? <a href="#" data-toggle="modal" data-target="#registerModal" data-dismiss="modal">Register here</a></p>
                        <button type="submit" class="neumorphism-btn">Login</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Register Modal -->
    <div class="modal fade" id="registerModal" tabindex="-1" role="dialog" aria-labelledby="registerModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content neumorphism">
                <form id="registerForm" action="/projet/user/register.php" method="POST">
                    <div class="modal-header">
                        <h5 class="modal-title" id="registerModalLabel">Register</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="registerName">Name</label>
                            <input type="text" name="name" class="form-control" id="registerName" placeholder="Your Name" required>
                            <div class="invalid-feedback">Please enter your name.</div>
                        </div>
                        <div class="form-group">
                            <label for="registerEmail">Email address</label>
                            <input type="email" name="email" class="form-control" id="registerEmail" placeholder="Your Email" required>
                            <div class="invalid-feedback">Please enter a valid email address.</div>
                        </div>
                        <div class="form-group">
                            <label for="registerPassword">Password</label>
                            <input type="password" name="password" class="form-control" id="registerPassword" placeholder="Your Password" required minlength="8">
                            <div class="invalid-feedback">Please enter a valid password (minimum 8 characters).</div>
                        </div>
                        <div class="form-group">
                            <label for="registerPasswordConfirm">Confirm Password</label>
                            <input type="password" name="password_confirm" class="form-control" id="registerPasswordConfirm" placeholder="Confirm Password" required minlength="8">
                            <div class="invalid-feedback">Please confirm your password.</div>
                        </div>
                        <?php if (isset($_SESSION['error'])): ?>
                            <div class="alert alert-danger"><?= $_SESSION['error'] ?></div>
                            <?php unset($_SESSION['error']); ?>
                        <?php endif; ?>
                        <?php if (isset($_SESSION['success'])): ?>
                            <div class="alert alert-success"><?= $_SESSION['success'] ?></div>
                            <?php unset($_SESSION['success']); ?>
                        <?php endif; ?>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <p class="text-center mt-2">Already have an account? <a href="#" data-toggle="modal" data-target="#loginModal" data-dismiss="modal">Login here</a></p>
                        <button type="submit" class="neumorphism-btn">Register</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.1/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
</body>
</html>
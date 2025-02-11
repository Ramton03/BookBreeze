<?php require_once 'config.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BookBreeze</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/style.css">
    <style>
        :root {
            --navbar-height: 70px;
            --primary-color: #007bff;
            --secondary-color: #343a40;
        }

        .navbar {
            height: var(--navbar-height);
            background: linear-gradient(135deg, var(--secondary-color), var(--primary-color));
            box-shadow: 0 2px 15px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease-in-out;
            padding: 0.5rem 1rem;
        }

        .navbar.scrolled {
            height: calc(var(--navbar-height) - 10px);
            background: linear-gradient(135deg, rgba(52, 58, 64, 0.98), rgba(0, 123, 255, 0.98));
            backdrop-filter: blur(10px);
        }

        .navbar-brand {
            position: relative;
            padding: 0;
            transition: transform 0.3s ease;
        }

        .navbar-brand:hover {
            transform: scale(1.05);
        }

        .navbar-brand img {
            height: calc(var(--navbar-height) - 20px);
            object-fit: contain;
            transition: height 0.3s ease;
        }

        .scrolled .navbar-brand img {
            height: calc(var(--navbar-height) - 30px);
        }

        .nav-link {
            position: relative;
            color: #fff !important;
            margin: 0 0.5rem;
            padding: 0.5rem 1rem !important;
            transition: all 0.3s ease;
        }

        .nav-link::after {
            content: '';
            position: absolute;
            width: 0;
            height: 2px;
            bottom: 0;
            left: 50%;
            background-color: #fff;
            transition: all 0.3s ease;
            transform: translateX(-50%);
        }

        .nav-link:hover::after {
            width: 100%;
        }

        .nav-link:hover {
            transform: translateY(-2px);
            color: #f8f9fa !important;
        }

        .navbar-toggler {
            border: none;
            padding: 0.5rem;
            transition: transform 0.3s ease;
        }

        .navbar-toggler:focus {
            box-shadow: none;
        }

        .navbar-toggler:hover {
            transform: scale(1.1);
        }

        .navbar-toggler-icon {
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 30 30'%3e%3cpath stroke='rgba(255, 255, 255, 1)' stroke-linecap='round' stroke-miterlimit='10' stroke-width='2' d='M4 7h22M4 15h22M4 23h22'/%3e%3c/svg%3e");
        }

        /* Mobile menu animations */
        .navbar-collapse {
            transition: all 0.3s ease-in-out;
        }

        @media (max-width: 991.98px) {
            .navbar-collapse {
                background: linear-gradient(135deg, var(--secondary-color), var(--primary-color));
                padding: 1rem;
                border-radius: 0 0 1rem 1rem;
                box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            }

            .nav-link {
                padding: 0.75rem 1rem !important;
                text-align: center;
                transform: translateX(-20px);
                opacity: 0;
                animation: slideIn 0.3s forwards;
            }

            .navbar-nav .nav-item:nth-child(1) .nav-link { animation-delay: 0.1s; }
            .navbar-nav .nav-item:nth-child(2) .nav-link { animation-delay: 0.2s; }
            .navbar-nav .nav-item:nth-child(3) .nav-link { animation-delay: 0.3s; }
            .navbar-nav .nav-item:nth-child(4) .nav-link { animation-delay: 0.4s; }
            .navbar-nav .nav-item:nth-child(5) .nav-link { animation-delay: 0.5s; }
        }

        @keyframes slideIn {
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark fixed-top">
        <div class="container-fluid">
            <a class="navbar-brand" href="<?php echo SITE_URL; ?>">
                <img src="<?php echo SITE_URL; ?>/assets/other/logo.jpg" alt="BookBreeze">
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" href="<?php echo SITE_URL; ?>">Home</a></li>
                    <?php if(isset($_SESSION['user_id'])): ?>
                    <li class="nav-item"><a class="nav-link" href="<?php echo SITE_URL; ?>/books.php">Books</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?php echo SITE_URL; ?>/cart/view.php">Cart</a></li>
                    <?php if(isAdmin()): ?>
                    <li class="nav-item"><a class="nav-link" href="<?php echo SITE_URL; ?>/admin/dashboard.php">Admin</a></li>
                    <?php endif; ?>
                    <li class="nav-item"><a class="nav-link" href="<?php echo SITE_URL; ?>/auth/logout.php">Logout</a></li>
                    <?php else: ?>
                    <li class="nav-item"><a class="nav-link" href="<?php echo SITE_URL; ?>/auth/login.php">Login</a></li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>
    <div style="height: var(--navbar-height)"></div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Shrink navbar on scroll
        window.addEventListener('scroll', function() {
            const navbar = document.querySelector('.navbar');
            if (window.scrollY > 50) {
                navbar.classList.add('scrolled');
            } else {
                navbar.classList.remove('scrolled');
            }
        });

        // Smooth state transitions for mobile menu
        const navbarToggler = document.querySelector('.navbar-toggler');
        const navbarCollapse = document.querySelector('.navbar-collapse');

        navbarToggler.addEventListener('click', function() {
            if (!navbarCollapse.classList.contains('show')) {
                document.querySelectorAll('.nav-link').forEach(link => {
                    link.style.transform = 'translateX(-20px)';
                    link.style.opacity = '0';
                });
                setTimeout(() => {
                    document.querySelectorAll('.nav-link').forEach(link => {
                        link.style.transform = '';
                        link.style.opacity = '';
                    });
                }, 50);
            }
        });
    </script>
</body>
</html>

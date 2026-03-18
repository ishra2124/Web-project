<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?php echo isset($pageTitle) ? $pageTitle : 'SkillBridge - Professional Freelance Marketplace'; ?></title>
    <link rel="stylesheet" href="styles.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
</head>

<body>
    <!-- Navbar -->
    <nav class="navbar">
        <div class="navbar-content">
            <a href="index.php" class="navbar-logo">
                <img src="logo.png" alt="SkillBridge Logo" />
                <span style="font-family: calibri; font-size: 25px; font-weight: bold">
                    <span style="color: greenyellow">Skill</span><span style="color: blue">Bridge</span>
                </span>
            </a>
            <div class="navbar-links">
                <a href="index.php" class="navbar-link"><i class="fas fa-home"></i> Home</a>
                <a href="browse-projects.php" class="navbar-link"><i class="fas fa-briefcase"></i> Browse Projects</a>
                <a href="testimonials.php" class="navbar-link"><i class="fas fa-info-circle"></i> Testimonials</a>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="<?php echo $_SESSION['role']; ?>-dashboard.php" class="navbar-link"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
                    <a href="logout.php" class="navbar-link"><i class="fas fa-sign-out-alt"></i> Logout</a>
                <?php else: ?>
                    <a href="login.php" class="navbar-link"><i class="fas fa-sign-in-alt"></i> Login</a>
                    <a href="signup.php" class="btn btn-primary btn-sm"><i class="fas fa-user-plus"></i> Sign up</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>
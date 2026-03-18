<?php
session_start();
require_once 'includes/db.php';

$freelancer_id = isset($_GET['id']) ? (int)$_GET['id'] : (isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0);

if (!$freelancer_id) {
    echo "No freelancer selected.";
    exit();
}

// Fetch user data
$stmt_user = $conn->prepare("SELECT full_name, email, phone, profile_image FROM users WHERE user_id = ? AND role = 'freelancer'");
$stmt_user->bind_param("i", $freelancer_id);
$stmt_user->execute();
$user = $stmt_user->get_result()->fetch_assoc();

if (!$user) {
    echo "Freelancer not found.";
    exit();
}

// Fetch profile data
$stmt_prof = $conn->prepare("SELECT * FROM freelancer_profiles WHERE freelancer_id = ?");
$stmt_prof->bind_param("i", $freelancer_id);
$stmt_prof->execute();
$profile = $stmt_prof->get_result()->fetch_assoc();

// Fetch certifications
$stmt_certs = $conn->prepare("SELECT * FROM certifications WHERE freelancer_id = ?");
$stmt_certs->bind_param("i", $freelancer_id);
$stmt_certs->execute();
$certs = $stmt_certs->get_result();

// Fetch portfolios
$stmt_ports = $conn->prepare("SELECT * FROM portfolios WHERE freelancer_id = ? AND status = 'approved'");
$stmt_ports->bind_param("i", $freelancer_id);
$stmt_ports->execute();
$portfolios = $stmt_ports->get_result();

$pageTitle = $user['full_name'] . ' - Portfolio';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageTitle); ?></title>
    <link rel="stylesheet" href="styles.css">
    <style>
        .portfolio-header {
            background: linear-gradient(135deg, var(--blue-600), var(--blue-800));
            color: white;
            padding: 4rem 0;
            text-align: center;
        }

        .portfolio-name {
            font-size: 3rem;
            font-weight: 800;
            margin-bottom: 0.5rem;
        }

        .portfolio-title {
            font-size: 1.5rem;
            opacity: 0.9;
            font-weight: 400;
        }

        .portfolio-section {
            padding: 3rem 0;
        }

        .section-heading {
            font-size: 1.8rem;
            font-weight: 700;
            margin-bottom: 2rem;
            position: relative;
            padding-bottom: 0.5rem;
        }

        .section-heading::after {
            content: '';
            position: absolute;
            left: 0;
            bottom: 0;
            width: 50px;
            height: 4px;
            background: var(--blue-600);
        }

        .skill-tag {
            background: var(--blue-50);
            color: var(--blue-700);
            padding: 5px 15px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 0.9rem;
        }

        .cert-card {
            border-left: 4px solid var(--blue-600);
            padding: 15px;
            background: #f9f9f9;
            border-radius: 0 8px 8px 0;
        }

        @media print {
            .no-print {
                display: none;
            }

            body {
                background: white;
            }

            .card {
                box-shadow: none;
                border: 1px solid #eee;
            }
        }
    </style>
</head>

<body class="bg-gray-50">

    <div class="no-print" style="position: sticky; top: 0; background: white; z-index: 100; border-bottom: 1px solid #eee; padding: 10px 0;">
        <div class="container" style="display: flex; justify-content: space-between; align-items: center;">
            <a href="index.php" style="text-decoration: none; font-weight: 700; color: var(--blue-600);">SkillBridge</a>
            <button onclick="window.print()" class="btn btn-primary btn-sm">Download as PDF / Print</button>
        </div>
    </div>

    <header class="portfolio-header">
        <div class="container">
            <h1 class="portfolio-name"><?php echo htmlspecialchars($user['full_name']); ?></h1>
            <p class="portfolio-title"><?php echo htmlspecialchars($profile['title'] ?? 'Freelancer'); ?></p>
            <div style="margin-top: 1.5rem; display: flex; justify-content: center; gap: 20px;">
                <span><i class="fas fa-envelope"></i> <?php echo htmlspecialchars($user['email']); ?></span>
                <?php if ($user['phone']): ?><span><i class="fas fa-phone"></i> <?php echo htmlspecialchars($user['phone']); ?></span><?php endif; ?>
                <?php if ($profile['github_link']): ?><span><i class="fab fa-github"></i> <a href="<?php echo htmlspecialchars($profile['github_link']); ?>" style="color: white;">GitHub</a></span><?php endif; ?>
            </div>
        </div>
    </header>

    <main class="container">
        <div class="portfolio-section">
            <div class="grid grid-3">
                <div class="col-span-2">
                    <h2 class="section-heading">About Me</h2>
                    <p style="font-size: 1.1rem; line-height: 1.8; color: var(--gray-700);">
                        <?php echo nl2br(htmlspecialchars($profile['bio'] ?? 'No bio provided.')); ?>
                    </p>

                    <h2 class="section-heading" style="margin-top: 3rem;">Skills</h2>
                    <div style="display: flex; flex-wrap: wrap; gap: 10px;">
                        <?php
                        $skills = explode(',', $profile['skills'] ?? '');
                        foreach ($skills as $skill): if (trim($skill)): ?>
                                <span class="skill-tag"><?php echo htmlspecialchars(trim($skill)); ?></span>
                        <?php endif;
                        endforeach; ?>
                    </div>
                </div>

                <div>
                    <div class="card" style="padding: 25px;">
                        <h3 style="margin-bottom: 1rem; border-bottom: 1px solid #eee; padding-bottom: 0.5rem;">Details</h3>
                        <p><strong>Experience:</strong> <?php echo ucfirst($profile['experience_level'] ?? 'Not specified'); ?></p>
                        <p><strong>Hourly Rate:</strong> $<?php echo number_format($profile['hourly_rate'] ?? 0, 2); ?>/hr</p>
                    </div>

                    <h3 class="section-heading" style="margin-top: 2rem;">Certifications</h3>
                    <div style="display: flex; flex-direction: column; gap: 15px;">
                        <?php if ($certs->num_rows > 0): ?>
                            <?php while ($c = $certs->fetch_assoc()): ?>
                                <div class="cert-card">
                                    <h4 style="margin: 0;"><?php echo htmlspecialchars($c['title']); ?></h4>
                                    <p style="margin: 5px 0 0; font-size: 0.85rem; color: #666;"><?php echo htmlspecialchars($c['issuer']); ?> | <?php echo $c['year']; ?></p>
                                </div>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <p style="color: #999;">No certifications listed.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="portfolio-section" style="border-top: 1px solid #eee;">
            <h2 class="section-heading">Portfolio Projects</h2>
            <div class="grid grid-3">
                <?php if ($portfolios->num_rows > 0): ?>
                    <?php while ($p = $portfolios->fetch_assoc()): ?>
                        <div class="card" style="overflow: hidden;">
                            <div style="padding: 20px;">
                                <h3 style="margin-bottom: 0.5rem;"><?php echo htmlspecialchars($p['title']); ?></h3>
                                <p style="font-size: 0.9rem; color: var(--gray-600); margin-bottom: 1rem;"><?php echo htmlspecialchars($p['description']); ?></p>
                                <?php if ($p['project_link']): ?>
                                    <a href="<?php echo htmlspecialchars($p['project_link']); ?>" target="_blank" class="btn btn-secondary btn-xs">View Project</a>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="col-span-3">
                        <p style="text-align: center; color: #999; padding: 2rem;">No approved portfolio projects to show yet.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <footer style="text-align: center; padding: 4rem 0; color: #999; border-top: 1px solid #eee;">
        <p>&copy; <?php echo date('Y'); ?> SkillBridge - Generated Professional Portfolio</p>
    </footer>

</body>

</html>
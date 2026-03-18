<?php
session_start();
require_once 'includes/db.php';

if (!isset($_GET['id'])) {
    header("Location: browse-projects.php");
    exit();
}

$job_id = $_GET['id'];

// Fetch job details
$stmt = $conn->prepare("SELECT j.*, u.full_name as client_name 
                        FROM jobs j 
                        JOIN users u ON j.client_id = u.user_id 
                        WHERE j.job_id = ?");
$stmt->bind_param("i", $job_id);
$stmt->execute();
$job = $stmt->get_result()->fetch_assoc();

if (!$job) {
    header("Location: browse-projects.php");
    exit();
}

$pageTitle = htmlspecialchars($job['title']) . ' - SkillBridge';
include 'includes/header.php';
?>

<style>
    /* Page Layout overrides if needed */
    body {
        background: linear-gradient(135deg, #f5f9ff, #eef7f2);
    }

    .details-container {
        max-width: 1100px;
        margin: 50px auto;
        padding: 30px;
    }

    .details-card {
        background: #ffffff;
        border-radius: 12px;
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.08);
        padding: 40px;
    }

    .project-title {
        font-size: 32px;
        font-weight: 700;
        margin-bottom: 10px;
        color: #1f2937;
    }

    .project-meta {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 20px;
        margin: 30px 0;
    }

    .meta-box {
        background: #f8fafc;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        padding: 15px 18px;
    }

    .meta-label {
        font-size: 13px;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: #6b7280;
        margin-bottom: 5px;
    }

    .meta-value {
        font-size: 16px;
        font-weight: 600;
        color: #111827;
    }

    .section {
        margin-top: 35px;
    }

    .section h2 {
        font-size: 20px;
        margin-bottom: 12px;
        color: #111827;
        border-left: 4px solid #22c55e;
        padding-left: 10px;
    }

    .section p {
        line-height: 1.7;
        color: #374151;
    }

    .tags {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
    }

    .tag {
        background: #ecfdf5;
        color: #16a34a;
        border: 1px solid #bbf7d0;
        padding: 6px 14px;
        border-radius: 999px;
        font-size: 14px;
        font-weight: 500;
    }

    .offer-box {
        margin-top: 40px;
        background: var(--blue-600);
        color: #ffffff;
        padding: 25px 30px;
        border-radius: 10px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .offer-text {
        font-size: 18px;
        font-weight: 500;
    }

    .offer-amount {
        font-size: 28px;
        font-weight: 800;
    }
</style>

<?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'freelancer'): ?>
    <nav class="admin-navbar">
        <div class="navbar-content">
            <div class="navbar-links">
                <a href="freelancer-dashboard.php" class="navbar-link">Dashboard</a>
                <a href="submit-proposal.php" class="navbar-link">Proposals</a>
                <a href="client-chat.php" class="navbar-link">Chat</a>
                <a href="work-progress.php" class="navbar-link">Contracts</a>
                <a href="wallet.php" class="navbar-link">Earnings</a>
            </div>
        </div>
    </nav>
<?php endif; ?>

<main class="details-container">
    <div class="details-card">

        <div class="project-title"><?php echo htmlspecialchars($job['title']); ?></div>

        <div class="project-meta">
            <div class="meta-box">
                <div class="meta-label">Client</div>
                <div class="meta-value"><?php echo htmlspecialchars($job['client_name']); ?></div>
            </div>
            <div class="meta-box">
                <div class="meta-label">Status</div>
                <div class="meta-value"><?php echo ucfirst($job['status']); ?></div>
            </div>
            <div class="meta-box">
                <div class="meta-label">Deadline</div>
                <div class="meta-value"><?php echo date('F d, Y', strtotime($job['deadline'])); ?></div>
            </div>
        </div>

        <div class="section">
            <h2>Description</h2>
            <div style="white-space: pre-wrap; line-height: 1.7; color: #374151;">
                <?php echo htmlspecialchars($job['description']); ?>
            </div>
        </div>

        <div class="section">
            <h2>Budget</h2>
            <div class="offer-box">
                <div class="offer-text">Total Project Budget</div>
                <div class="offer-amount">$<?php echo number_format($job['budget'], 2); ?></div>
            </div>
        </div>

        <?php if ($job['status'] === 'open' && isset($_SESSION['role']) && $_SESSION['role'] === 'freelancer'): ?>
            <div style="margin-top: 25px;">
                <a href="submit-proposal.php?job_id=<?php echo $job['job_id']; ?>" class="btn btn-primary btn-lg">Apply for This Project</a>
            </div>
        <?php endif; ?>

    </div>
</main>

<?php include 'includes/footer.php'; ?>
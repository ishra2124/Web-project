<?php
session_start();
require_once 'includes/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'client') {
  header("Location: login.php");
  exit();
}

$user_id = $_SESSION['user_id'];

// Fetch user info
$stmt = $conn->prepare("SELECT full_name FROM users WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user_data = $stmt->get_result()->fetch_assoc();

if (!$user_data) {
  header("Location: logout.php");
  exit();
}

// Fetch project stats
$stmt_active = $conn->prepare("SELECT COUNT(*) as count FROM projects WHERE client_id = ? AND status = 'active'");
$stmt_active->bind_param("i", $user_id);
$stmt_active->execute();
$active_count = $stmt_active->get_result()->fetch_assoc()['count'];

$stmt_completed = $conn->prepare("SELECT COUNT(*) as count FROM projects WHERE client_id = ? AND status = 'completed'");
$stmt_completed->bind_param("i", $user_id);
$stmt_completed->execute();
$completed_count = $stmt_completed->get_result()->fetch_assoc()['count'];

$stmt_total = $conn->prepare("SELECT COUNT(*) as count FROM jobs WHERE client_id = ?");
$stmt_total->bind_param("i", $user_id);
$stmt_total->execute();
$total_jobs = $stmt_total->get_result()->fetch_assoc()['count'];

$stmt_pending = $conn->prepare("SELECT COUNT(*) as count FROM jobs WHERE client_id = ? AND status = 'open'");
$stmt_pending->bind_param("i", $user_id);
$stmt_pending->execute();
$pending_jobs = $stmt_pending->get_result()->fetch_assoc()['count'];

$pageTitle = 'Client Dashboard - SkillBridge';
include 'includes/header.php';
?>

<nav class="admin-navbar">
  <div class="navbar-content">
    <div class="navbar-links">
      <a href="client-dashboard.php" class="navbar-link active">Dashboard</a>
      <a href="request-quote.php" class="navbar-link">Quotes</a>
      <a href="client-chat.php" class="navbar-link">Chat</a>
      <a href="proposal-overview.php" class="navbar-link">Proposals</a>
      <a href="work-progress.php" class="navbar-link">Work Approval</a>
      <a href="client-feedback.php" class="navbar-link">Ratings & Reviews</a>
    </div>
  </div>
</nav>

<!-- Welcome Banner -->
<div class="section section-white"
  style="padding: 4rem 0; background: linear-gradient(135deg, rgba(156, 199, 70, 0.1) 0%, rgba(49, 157, 214, 0.1) 100%);">
  <div class="container">
    <div
      style="text-align: center; padding: 3rem 2rem; background-color: var(--white); border-radius: 0.5rem; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
      <h1 class="section-title" style="margin-bottom: 1rem;">Welcome, <?php echo htmlspecialchars($user_data['full_name']); ?></h1>
      <p style="color: var(--gray-600); margin-bottom: 2rem;">Manage your projects and hire the best talent on SkillBridge.</p>
      <a href="post-project.php" class="btn btn-primary btn-lg">Post a New Job</a>
    </div>
  </div>
</div>

<!-- Project Statistics -->
<div class="section section-gray">
  <div class="container">
    <div class="card">
      <h2 class="section-title" style="font-size: 1.5rem; margin-bottom: 1.5rem;">Project Statistics</h2>
      <div class="stats-grid"
        style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px;">
        <div class="stat-card"
          style="background: linear-gradient(135deg, var(--blue-600) 0%, var(--blue-700) 100%); border-radius: 12px; padding: 20px; color: white;">
          <p style="opacity: 0.9; margin-bottom: 0.5rem; font-size: 0.95rem;">Active Projects</p>
          <p style="font-size: 2.2rem; font-weight: 700; margin-bottom: 0.75rem;"><?php echo $active_count; ?></p>
          <a href="work-progress.php" class="btn btn-primary btn-sm"
            style="background: rgba(255,255,255,0.2);">View All</a>
        </div>
        <div class="stat-card"
          style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); border-radius: 12px; padding: 20px; color: white;">
          <p style="opacity: 0.9; margin-bottom: 0.5rem; font-size: 0.95rem;">Completed Projects</p>
          <p style="font-size: 2.2rem; font-weight: 700; margin-bottom: 0.75rem;"><?php echo $completed_count; ?></p>
          <span style="opacity: 0.8;">-</span>
        </div>
        <div class="stat-card"
          style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); border-radius: 12px; padding: 20px; color: white;">
          <p style="opacity: 0.9; margin-bottom: 0.5rem; font-size: 0.95rem;">Total Jobs Posted</p>
          <p style="font-size: 2.2rem; font-weight: 700; margin-bottom: 0.75rem;"><?php echo $total_jobs; ?></p>
          <span style="opacity: 0.8;">-</span>
        </div>
        <div class="stat-card"
          style="background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%); border-radius: 12px; padding: 20px; color: white;">
          <p style="opacity: 0.9; margin-bottom: 0.5rem; font-size: 0.95rem;">Pending Jobs</p>
          <p style="font-size: 2.2rem; font-weight: 700; margin-bottom: 0.75rem;"><?php echo $pending_jobs; ?></p>
          <a href="proposal-overview.php" class="btn btn-primary btn-sm"
            style="background: rgba(255,255,255,0.2);">Review</a>
        </div>
      </div>
    </div>
  </div>
</div>

<?php include 'includes/footer.php'; ?>
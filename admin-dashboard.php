<?php
session_start();
require_once 'includes/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
  header("Location: login.php");
  exit();
}

$user_id = $_SESSION['user_id'];

// Fetch admin info
$stmt = $conn->prepare("SELECT full_name FROM users WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$admin_data = $stmt->get_result()->fetch_assoc();

if (!$admin_data) {
  header("Location: logout.php");
  exit();
}

// Fetch platform stats
$total_users = $conn->query("SELECT COUNT(*) as count FROM users")->fetch_assoc()['count'];
$total_jobs = $conn->query("SELECT COUNT(*) as count FROM jobs")->fetch_assoc()['count'];
$total_projects = $conn->query("SELECT COUNT(*) as count FROM projects")->fetch_assoc()['count'];
$pending_verifications = $conn->query("SELECT COUNT(*) as count FROM verification_documents WHERE status = 'pending'")->fetch_assoc()['count'];

$pageTitle = 'Admin Dashboard - SkillBridge';
include 'includes/header.php';
?>

<nav class="admin-navbar">
  <div class="navbar-content">
    <div class="navbar-links">
      <a href="admin-dashboard.php" class="navbar-link active">Dashboard</a>
      <a href="admin-projects.php" class="navbar-link">Projects</a>
      <a href="admin-verification.php" class="navbar-link">Verifications <?php if ($pending_verifications > 0): ?><span class="badge urgent" style="padding: 2px 6px;"><?php echo $pending_verifications; ?></span><?php endif; ?></a>
      <a href="admin-freelancer-approvals.php" class="navbar-link">Freelancers</a>
      <a href="admin-client-approvals.php" class="navbar-link">Clients</a>
      <a href="admin-panel.php" class="navbar-link">Settings</a>
    </div>
  </div>
</nav>

<div class="section section-white">
  <div class="container">
    <h1 class="section-title">Platform Overview</h1>
    <p class="section-subtitle">Manage SkillBridge users, projects, and verifications.</p>

    <div class="stats-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 20px; margin-top: 2rem;">
      <div class="stat-card" style="background: var(--white); border: 1px solid var(--gray-200); border-radius: 12px; padding: 25px; box-shadow: 0 4px 6px rgba(0,0,0,0.05);">
        <div style="color: var(--blue-600); font-size: 2rem; margin-bottom: 1rem;"><i class="fas fa-users"></i></div>
        <p style="color: var(--gray-600); font-size: 0.95rem; margin-bottom: 0.5rem;">Total Users</p>
        <p style="font-size: 2rem; font-weight: 700; color: var(--gray-900);"><?php echo $total_users; ?></p>
      </div>

      <div class="stat-card" style="background: var(--white); border: 1px solid var(--gray-200); border-radius: 12px; padding: 25px; box-shadow: 0 4px 6px rgba(0,0,0,0.05);">
        <div style="color: #10b981; font-size: 2rem; margin-bottom: 1rem;"><i class="fas fa-briefcase"></i></div>
        <p style="color: var(--gray-600); font-size: 0.95rem; margin-bottom: 0.5rem;">Total Jobs</p>
        <p style="font-size: 2rem; font-weight: 700; color: var(--gray-900);"><?php echo $total_jobs; ?></p>
      </div>

      <div class="stat-card" style="background: var(--white); border: 1px solid var(--gray-200); border-radius: 12px; padding: 25px; box-shadow: 0 4px 6px rgba(0,0,0,0.05);">
        <div style="color: #f59e0b; font-size: 2rem; margin-bottom: 1rem;"><i class="fas fa-project-diagram"></i></div>
        <p style="color: var(--gray-600); font-size: 0.95rem; margin-bottom: 0.5rem;">Active Projects</p>
        <p style="font-size: 2rem; font-weight: 700; color: var(--gray-900);"><?php echo $total_projects; ?></p>
      </div>

      <div class="stat-card" style="background: var(--white); border: 1px solid var(--gray-200); border-radius: 12px; padding: 25px; box-shadow: 0 4px 6px rgba(0,0,0,0.05);">
        <div style="color: #ef4444; font-size: 2rem; margin-bottom: 1rem;"><i class="fas fa-user-check"></i></div>
        <p style="color: var(--gray-600); font-size: 0.95rem; margin-bottom: 0.5rem;">Pending Verifications</p>
        <p style="font-size: 2rem; font-weight: 700; color: var(--gray-900);"><?php echo $pending_verifications; ?></p>
      </div>
    </div>

    <div class="card" style="margin-top: 3rem; padding: 30px;">
      <h2 style="font-size: 1.5rem; margin-bottom: 1.5rem;">Quick Actions</h2>
      <div style="display: flex; gap: 15px; flex-wrap: wrap;">
        <a href="admin-verification.php" class="btn btn-primary">Review Documents</a>
        <a href="admin-freelancer-approvals.php" class="btn btn-outline">Approve Freelancers</a>
        <a href="admin-panel.php" class="btn btn-secondary">Platform Settings</a>
      </div>
    </div>
  </div>
</div>

<?php include 'includes/footer.php'; ?>
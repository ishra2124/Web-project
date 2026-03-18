<?php
session_start();
require_once 'includes/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
  header("Location: login.php");
  exit();
}

$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT full_name FROM users WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$res = $stmt->get_result()->fetch_assoc();
if (!$res) {
  header("Location: logout.php");
  exit();
}
$admin_name = $res['full_name'];

$pageTitle = 'Admin Panel - SkillBridge';
include 'includes/header.php';
?>
<style>
  /* Admin Panel */
  .admin-panel-main {
    padding: 4rem 0;
  }

  .admin-header {
    text-align: center;
    margin-bottom: 3rem;
  }

  .admin-header h1 {
    font-size: 2.4rem;
    font-weight: 700;
  }

  .admin-header p {
    font-size: 1.1rem;
    color: var(--gray-600);
    margin-top: 0.5rem;
  }

  /* Cards Layout */
  .admin-cards {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
    gap: 2rem;
  }

  .admin-card {
    background: #ffffff;
    border-radius: 14px;
    padding: 2rem;
    box-shadow: 0 12px 30px rgba(0, 0, 0, 0.08);
    text-align: center;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
  }

  .admin-card:hover {
    transform: translateY(-6px);
    box-shadow: 0 18px 40px rgba(0, 0, 0, 0.12);
  }

  .admin-card h3 {
    font-size: 1.3rem;
    margin-bottom: 0.6rem;
    color: var(--gray-800);
  }

  .admin-card p {
    font-size: 0.95rem;
    color: var(--gray-600);
    margin-bottom: 1.5rem;
  }

  .logout-card {
    border: 2px dashed #ef4444;
  }
</style>
</head>

<body>
  <nav class="admin-navbar">
    <div class="navbar-content">
      <div class="navbar-links">
        <a href="admin-dashboard.php" class="navbar-link active">Dashboard</a>
        <a href="admin-projects.php" class="navbar-link">Projects</a>
        <a href="admin-freelancer-approvals.php" class="navbar-link">Freelancer Approvals</a>
        <a href="admin-client-approvals.php" class="navbar-link">Client Approvals</a>
        <a href="admin-verification.php" class="navbar-link">Verification</a>
      </div>
    </div>
  </nav>

  <!-- Admin Panel Welcome -->
  <!-- Admin Panel Main -->
  <div class="dashboard admin-panel-main">
    <div class="container">
      <!-- Welcome Header -->
      <div class="admin-header">
        <h1>Welcome, <?php echo htmlspecialchars($admin_name); ?></h1>
        <p>Control and monitor the entire platform from here.</p>
      </div>

      <!-- Admin Cards -->
      <div class="admin-cards">
        <div class="admin-card">
          <h3>Dashboard Overview</h3>
          <p>View statistics and pending actions</p>
          <a href="admin-dashboard.php" class="btn btn-primary btn-sm">Open</a>
        </div>

        <div class="admin-card">
          <h3>Freelancer Approvals</h3>
          <p>Approve or reject freelancer requests</p>
          <a href="admin-freelancer-approvals.php" class="btn btn-success btn-sm">Manage</a>
        </div>

        <div class="admin-card">
          <h3>Client Approvals</h3>
          <p>Verify and approve client accounts</p>
          <a href="admin-client-approvals.php" class="btn btn-success btn-sm">Manage</a>
        </div>

        <div class="admin-card">
          <h3>User Verification</h3>
          <p>Review identity & document verification</p>
          <a href="admin-verification.php" class="btn btn-secondary btn-sm">Review</a>
        </div>

        <div class="admin-card logout-card">
          <h3>Log Out</h3>
          <p>End your admin session securely</p>
          <a href="logout.php" class="btn btn-danger btn-sm">Log Out</a>
        </div>
      </div>
    </div>
  </div>

  <?php include 'includes/footer.php'; ?>
</body>

</html>
</body>

</html>
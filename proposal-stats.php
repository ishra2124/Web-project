<?php
session_start();
require_once 'includes/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'freelancer') {
  header("Location: login.php");
  exit();
}

$user_id = $_SESSION['user_id'];

// Fetch user data
$stmt_user = $conn->prepare("SELECT full_name FROM users WHERE user_id = ?");
$stmt_user->bind_param("i", $user_id);
$stmt_user->execute();
$user_data = $stmt_user->get_result()->fetch_assoc();

// Fetch stats
$stmt_total = $conn->prepare("SELECT COUNT(*) as count FROM proposals WHERE freelancer_id = ?");
$stmt_total->bind_param("i", $user_id);
$stmt_total->execute();
$total_proposals = $stmt_total->get_result()->fetch_assoc()['count'];

$stmt_pending = $conn->prepare("SELECT COUNT(*) as count FROM proposals WHERE freelancer_id = ? AND status = 'pending'");
$stmt_pending->bind_param("i", $user_id);
$stmt_pending->execute();
$pending_proposals = $stmt_pending->get_result()->fetch_assoc()['count'];

$stmt_accepted = $conn->prepare("SELECT COUNT(*) as count FROM proposals WHERE freelancer_id = ? AND status = 'accepted'");
$stmt_accepted->bind_param("i", $user_id);
$stmt_accepted->execute();
$accepted_proposals = $stmt_accepted->get_result()->fetch_assoc()['count'];

// Fetch individual proposals
$query = "SELECT p.*, j.title as job_title, j.deadline 
          FROM proposals p 
          JOIN jobs j ON p.job_id = j.job_id 
          WHERE p.freelancer_id = ? 
          ORDER BY p.created_at DESC";
$stmt_proposals = $conn->prepare($query);
$stmt_proposals->bind_param("i", $user_id);
$stmt_proposals->execute();
$proposals_result = $stmt_proposals->get_result();

$pageTitle = 'Proposal Overview - SkillBridge';
include 'includes/header.php';
?>

<style>
  .dashboard-container {
    max-width: 1100px;
    margin: 40px auto;
    padding: 0 20px;
    font-family: Arial, sans-serif;
  }

  /* CARD SECTIONS */
  .card-section {
    margin-bottom: 40px;
  }

  .section-header {
    margin-bottom: 20px;
  }

  .section-header h3 {
    margin: 0;
    font-size: 18px;
  }

  .section-header p {
    margin: 5px 0 0;
    color: #6b7280;
    font-size: 14px;
  }

  /* STATS */
  .stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
    gap: 20px;
    margin-top: 25px;
  }

  .stat-card {
    background: #f9fafb;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    padding: 20px;
    text-align: center;
  }

  .stat-card h4 {
    margin: 0 0 8px;
    font-size: 14px;
    color: #6b7280;
  }

  .stat-card p {
    margin: 0;
    font-size: 22px;
    font-weight: bold;
  }

  /* PROPOSAL LIST */
  .proposal-list {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 20px;
  }

  .proposal-item {
    background: #ffffff;
    border: 1px solid #e5e7eb;
    border-radius: 12px;
    padding: 20px;
    transition: transform 0.2s, box-shadow 0.2s;
  }

  .proposal-item:hover {
    transform: translateY(-4px);
    box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
  }

  .proposal-title {
    font-size: 1.1rem;
    font-weight: 600;
    margin-bottom: 10px;
    color: #111827;
  }

  .proposal-meta {
    font-size: 0.9rem;
    color: #6b7280;
    margin-bottom: 15px;
  }

  .proposal-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
  }
</style>

<nav class="admin-navbar">
  <div class="navbar-content">
    <div class="navbar-links">
      <a href="freelancer-dashboard.php" class="navbar-link">Dashboard</a>
      <a href="proposal-stats.php" class="navbar-link active">Proposals</a>
      <a href="freelancer-chat.php" class="navbar-link">Chat</a>
      <a href="work-progress.php" class="navbar-link">Contracts</a>
      <a href="wallet.php" class="navbar-link">Earnings</a>
    </div>
  </div>
</nav>

<div class="dashboard-container">

  <!-- HERO SECTION -->
  <div class="section section-white" style="padding: 2rem 0; margin-bottom: 2rem;">
    <h1 class="section-title">My Proposals</h1>
    <p class="section-subtitle">Manage and track your project applications</p>
  </div>

  <!-- STATISTICS -->
  <section class="card-section">
    <div class="stats-grid">
      <div class="stat-card">
        <h4>Total Proposals</h4>
        <p><?php echo $total_proposals; ?></p>
      </div>
      <div class="stat-card">
        <h4>Pending Reviews</h4>
        <p><?php echo $pending_proposals; ?></p>
      </div>
      <div class="stat-card">
        <h4>Approved Proposals</h4>
        <p><?php echo $accepted_proposals; ?></p>
      </div>
      <div class="stat-card">
        <h4>Acceptance Rate</h4>
        <p><?php echo $total_proposals > 0 ? round(($accepted_proposals / $total_proposals) * 100, 1) : 0; ?>%</p>
      </div>
    </div>
  </section>

  <!-- PROPOSAL LIST -->
  <section class="card-section">
    <div class="section-header">
      <h3>Recent Applications</h3>
      <p>Track the status of your recent project biddings</p>
    </div>

    <div class="proposal-list">
      <?php if ($proposals_result->num_rows > 0): ?>
        <?php while ($prop = $proposals_result->fetch_assoc()): ?>
          <div class="proposal-item">
            <div class="proposal-title"><?php echo htmlspecialchars($prop['job_title']); ?></div>
            <div class="proposal-meta">
              <p><i class="fas fa-dollar-sign"></i> Bid Amount: <strong>$<?php echo number_format($prop['bid_amount'], 2); ?></strong></p>
              <p><i class="fas fa-calendar"></i> Deadline: <?php echo date('M d, Y', strtotime($prop['deadline'])); ?></p>
            </div>
            <div class="proposal-footer">
              <span class="badge <?php echo $prop['status'] === 'pending' ? 'badge-warning' : ($prop['status'] === 'accepted' ? 'badge-info' : 'badge-danger'); ?>">
                <?php echo ucfirst($prop['status']); ?>
              </span>
              <a href="proposal-details.php?proposal_id=<?php echo $prop['proposal_id']; ?>" class="btn btn-primary btn-sm">View Details</a>
            </div>
            <?php if ($prop['client_comment']): ?>
              <div style="margin-top: 15px; padding: 8px 12px; background: var(--blue-50); border-radius: 6px; font-size: 0.85rem; color: var(--blue-700); border-left: 3px solid var(--blue-600);">
                <i class="fas fa-comment-alt" style="margin-right: 5px;"></i> <strong>Client Feedback:</strong> <?php echo htmlspecialchars(substr($prop['client_comment'], 0, 80)) . (strlen($prop['client_comment']) > 80 ? '...' : ''); ?>
              </div>
            <?php endif; ?>
          </div>
        <?php endwhile; ?>
      <?php else: ?>
        <div class="card" style="grid-column: 1 / -1; text-align: center; padding: 50px;">
          <h3>You haven't submitted any proposals yet.</h3>
          <p style="margin-top: 1rem;"><a href="browse-projects.php" class="btn btn-primary">Browse Projects</a></p>
        </div>
      <?php endif; ?>
    </div>
  </section>

</div>

<?php include 'includes/footer.php'; ?>
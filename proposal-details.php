<?php
session_start();
require_once 'includes/db.php';

if (!isset($_SESSION['user_id'])) {
  header("Location: login.php");
  exit();
}

$proposal_id = $_GET['proposal_id'] ?? null;
if (!$proposal_id) {
  header("Location: index.php");
  exit();
}

// Fetch proposal details with job and user info
$query = "SELECT p.*, j.title as job_title, j.description as job_description, j.client_id, u.full_name as freelancer_name, u.email as freelancer_email 
          FROM proposals p 
          JOIN jobs j ON p.job_id = j.job_id 
          JOIN users u ON p.freelancer_id = u.user_id 
          WHERE p.proposal_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $proposal_id);
$stmt->execute();
$proposal = $stmt->get_result()->fetch_assoc();

if (!$proposal) {
  header("Location: index.php");
  exit();
}

// Security Check: Only the freelancer or the client should see this
if ($_SESSION['user_id'] != $proposal['freelancer_id'] && $_SESSION['user_id'] != $proposal['client_id']) {
  header("Location: index.php");
  exit();
}

$pageTitle = 'Proposal Details - ' . htmlspecialchars($proposal['job_title']);
include 'includes/header.php';
?>

<style>
  .details-container {
    max-width: 900px;
    margin: 40px auto;
    padding: 0 20px;
  }

  .info-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
  }

  .info-card {
    background: #f9fafb;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    padding: 15px;
  }

  .info-label {
    font-size: 0.85rem;
    color: #6b7280;
    margin-bottom: 5px;
    text-transform: uppercase;
    letter-spacing: 0.05em;
  }

  .info-value {
    font-size: 1.1rem;
    font-weight: 600;
    color: #111827;
  }

  .letter-section {
    background: #ffffff;
    border: 1px solid #e5e7eb;
    border-radius: 12px;
    padding: 30px;
    margin-top: 30px;
    line-height: 1.6;
    color: #374151;
  }
</style>

<?php if ($_SESSION['role'] === 'freelancer'): ?>
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
<?php else: ?>
  <nav class="admin-navbar">
    <div class="navbar-content">
      <div class="navbar-links">
        <a href="client-dashboard.php" class="navbar-link">Dashboard</a>
        <a href="request-quote.php" class="navbar-link">Quotes</a>
        <a href="client-chat.php" class="navbar-link">Chat</a>
        <a href="proposal-overview.php" class="navbar-link active">Proposals</a>
        <a href="work-progress.php" class="navbar-link">Work Approval</a>
        <a href="client-feedback.php" class="navbar-link">Ratings & Reviews</a>
      </div>
    </div>
  </nav>
<?php endif; ?>

<div class="details-container">
  <div class="section section-white" style="padding: 2rem 0; margin-bottom: 1rem;">
    <h1 class="section-title">Proposal Details</h1>
    <p class="section-subtitle" style="text-align: center;">Reviewing application for <strong><?php echo htmlspecialchars($proposal['job_title']); ?></strong></p>
  </div>

  <div class="card">
    <div class="info-grid">
      <div class="info-card">
        <div class="info-label">Bid Amount</div>
        <div class="info-value text-success">$<?php echo number_format($proposal['bid_amount'], 2); ?></div>
      </div>
      <div class="info-card">
        <div class="info-label">Status</div>
        <div class="info-value">
          <span class="badge <?php echo $proposal['status'] === 'pending' ? 'badge-warning' : ($proposal['status'] === 'accepted' ? 'badge-info' : 'badge-danger'); ?>">
            <?php echo ucfirst($proposal['status']); ?>
          </span>
        </div>
      </div>
      <div class="info-card">
        <div class="info-label">Freelancer</div>
        <div class="info-value"><?php echo htmlspecialchars($proposal['freelancer_name']); ?></div>
      </div>
      <div class="info-card">
        <div class="info-label">Submitted On</div>
        <div class="info-value"><?php echo date('M d, Y', strtotime($proposal['created_at'])); ?></div>
      </div>
    </div>

    <div class="letter-section">
      <h3 style="margin-top: 0; margin-bottom: 15px; border-bottom: 1px solid #eee; padding-bottom: 10px;">Cover Letter</h3>
      <div style="white-space: pre-wrap;"><?php echo htmlspecialchars($proposal['cover_letter']); ?></div>
    </div>

    <?php if ($proposal['client_comment']): ?>
      <div class="letter-section" style="border-left: 5px solid var(--blue-600); background: #f0f7ff;">
        <h3 style="margin-top: 0; margin-bottom: 15px; border-bottom: 1px solid #d0e7ff; padding-bottom: 10px; color: var(--blue-700);">Client Feedback</h3>
        <div style="white-space: pre-wrap; font-weight: 500;"><?php echo htmlspecialchars($proposal['client_comment']); ?></div>
      </div>
    <?php endif; ?>

    <?php if ($_SESSION['role'] === 'client' && $proposal['status'] === 'pending'): ?>
      <div style="display: flex; gap: 1rem; margin-top: 2rem; justify-content: center;">
        <a href="proposal-overview.php?action=accept&proposal_id=<?php echo $proposal['proposal_id']; ?>" class="btn btn-primary">Accept Proposal</a>
        <a href="proposal-overview.php?action=reject&proposal_id=<?php echo $proposal['proposal_id']; ?>" class="btn btn-secondary" style="background: #ef4444; color: white;">Reject Proposal</a>
      </div>
      <?php elseif ($proposal['status'] === 'accepted'):
      // Fetch project_id for the accepted proposal
      $stmt_proj_id = $conn->prepare("SELECT project_id FROM projects WHERE job_id = ? AND freelancer_id = ?");
      $stmt_proj_id->bind_param("ii", $proposal['job_id'], $proposal['freelancer_id']);
      $stmt_proj_id->execute();
      $p_res = $stmt_proj_id->get_result()->fetch_assoc();
      if ($p_res):
      ?>
        <div style="display: flex; gap: 1rem; margin-top: 2rem; justify-content: center;">
          <a href="view-contract.php?id=<?php echo $p_res['project_id']; ?>" class="btn btn-primary"><i class="fas fa-file-contract"></i> View Signed Contract</a>
        </div>
    <?php endif;
    endif; ?>
  </div>

  <div style="text-align: center; margin-top: 2rem;">
    <a href="<?php echo $_SESSION['role'] === 'client' ? 'proposal-overview.php' : 'proposal-stats.php'; ?>" class="btn btn-outline btn-sm">
      <i class="fas fa-arrow-left"></i> Back to Proposals
    </a>
  </div>
</div>

<?php include 'includes/footer.php'; ?>
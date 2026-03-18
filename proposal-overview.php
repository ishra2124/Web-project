<?php
session_start();
require_once 'includes/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'client') {
  header("Location: login.php");
  exit();
}

$client_id = $_SESSION['user_id'];

// Handle Accept/Reject Actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && isset($_POST['proposal_id'])) {
  $action = $_POST['action'];
  $proposal_id = $_POST['proposal_id'];
  $client_comment = $_POST['client_comment'] ?? '';

  if ($action === 'accept') {
    $conn->begin_transaction();
    try {
      // Update proposal status with comment
      $stmt = $conn->prepare("UPDATE proposals SET status = 'accepted', client_comment = ? WHERE proposal_id = ?");
      $stmt->bind_param("si", $client_comment, $proposal_id);
      $stmt->execute();

      // Fetch proposal details to create a project
      $stmt_prop = $conn->prepare("SELECT job_id, freelancer_id FROM proposals WHERE proposal_id = ?");
      $stmt_prop->bind_param("i", $proposal_id);
      $stmt_prop->execute();
      $prop = $stmt_prop->get_result()->fetch_assoc();

      // Create project
      $stmt_proj = $conn->prepare("INSERT INTO projects (job_id, freelancer_id, client_id, status) VALUES (?, ?, ?, 'active')");
      $stmt_proj->bind_param("iii", $prop['job_id'], $prop['freelancer_id'], $client_id);
      $stmt_proj->execute();

      // Update job status
      $stmt_job = $conn->prepare("UPDATE jobs SET status = 'in_progress' WHERE job_id = ?");
      $stmt_job->bind_param("i", $prop['job_id']);
      $stmt_job->execute();

      $conn->commit();
    } catch (Exception $e) {
      $conn->rollback();
    }
  } elseif ($action === 'reject') {
    $stmt = $conn->prepare("UPDATE proposals SET status = 'rejected', client_comment = ? WHERE proposal_id = ?");
    $stmt->bind_param("si", $client_comment, $proposal_id);
    $stmt->execute();
  }
}

// Fetch all proposals for this client's jobs
$query = "SELECT p.*, j.title as job_title, u.full_name as freelancer_name 
          FROM proposals p 
          JOIN jobs j ON p.job_id = j.job_id 
          JOIN users u ON p.freelancer_id = u.user_id 
          WHERE j.client_id = ? 
          ORDER BY p.created_at DESC";
$stmt_fetch = $conn->prepare($query);
$stmt_fetch->bind_param("i", $client_id);
$stmt_fetch->execute();
$result = $stmt_fetch->get_result();

$pageTitle = 'Proposal Overview - SkillBridge';
include 'includes/header.php';
?>

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

<!-- Proposals Section -->
<div class="section section-white">
  <div class="container">
    <h1 class="section-title">Received Proposals</h1>
    <p class="section-subtitle">Review and accept bids for your projects.</p>
  </div>
</div>

<div class="section section-gray">
  <div class="container">
    <div class="projects-grid">
      <?php if ($result->num_rows > 0): ?>
        <?php while ($prop = $result->fetch_assoc()): ?>
          <div class="project-card">
            <h3 class="project-title"><?php echo htmlspecialchars($prop['job_title']); ?></h3>
            <p><strong>Freelancer:</strong> <?php echo htmlspecialchars($prop['freelancer_name']); ?></p>
            <p><strong>Bid Amount:</strong> $<?php echo number_format($prop['bid_amount'], 2); ?></p>
            <div style="margin: 10px 0; border: 1px solid #eee; padding: 10px; border-radius: 5px; background: #fff;">
              <strong>Cover Letter:</strong><br>
              <small><?php echo nl2br(htmlspecialchars($prop['cover_letter'])); ?></small>
            </div>
            <div class="project-tags">
              <span class="badge <?php echo $prop['status'] === 'pending' ? 'badge-warning' : ($prop['status'] === 'accepted' ? 'badge-info' : 'badge-danger'); ?>">
                <?php echo ucfirst($prop['status']); ?>
              </span>
            </div>
            <?php if ($prop['status'] === 'pending'): ?>
              <form action="proposal-overview.php" method="post" class="project-actions" style="display: flex; flex-direction: column; gap: 10px; margin-top: 15px;">
                <input type="hidden" name="proposal_id" value="<?php echo $prop['proposal_id']; ?>">
                <textarea name="client_comment" class="form-input" placeholder="Add a comment or feedback (optional)" rows="2" style="font-size: 0.85rem;"></textarea>
                <div style="display: flex; gap: 0.5rem;">
                  <button type="submit" name="action" value="accept" class="btn btn-primary btn-sm" style="flex: 1;">Accept</button>
                  <button type="submit" name="action" value="reject" class="btn btn-secondary btn-sm" style="background: #ef4444; color: white; flex: 1;">Reject</button>
                </div>
              </form>
            <?php elseif ($prop['client_comment']): ?>
              <div style="margin-top: 10px; padding: 10px; background: #f8f9fa; border-left: 3px solid var(--blue-600); border-radius: 4px;">
                <strong>Your Feedback:</strong><br>
                <small><?php echo htmlspecialchars($prop['client_comment']); ?></small>
              </div>
            <?php endif; ?>
          </div>
        <?php endwhile; ?>
      <?php else: ?>
        <div class="card" style="grid-column: 1 / -1; text-align: center; padding: 50px;">
          <h3>No proposals received yet.</h3>
        </div>
      <?php endif; ?>
    </div>
  </div>
</div>

<?php include 'includes/footer.php'; ?>
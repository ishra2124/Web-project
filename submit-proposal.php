<?php
session_start();
require_once 'includes/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'freelancer') {
  header("Location: login.php");
  exit();
}

$error = '';
$success = '';
$job_id = $_GET['job_id'] ?? ($_POST['job_id'] ?? null);

if (!$job_id) {
  header("Location: browse-projects.php");
  exit();
}

// Fetch job title for display
$stmt_job = $conn->prepare("SELECT title FROM jobs WHERE job_id = ?");
$stmt_job->bind_param("i", $job_id);
$stmt_job->execute();
$job_title = $stmt_job->get_result()->fetch_assoc()['title'] ?? 'Selected Project';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $bid_amount = $_POST['bid_amount'];
  $cover_letter = $_POST['cover_letter'];
  $freelancer_id = $_SESSION['user_id'];

  $stmt = $conn->prepare("INSERT INTO proposals (job_id, freelancer_id, bid_amount, cover_letter) VALUES (?, ?, ?, ?)");
  $stmt->bind_param("iids", $job_id, $freelancer_id, $bid_amount, $cover_letter);

  try {
    if ($stmt->execute()) {
      $success = "Your proposal has been submitted successfully!";
    }
  } catch (mysqli_sql_exception $e) {
    if ($conn->errno == 1062) {
      $error = "You have already submitted a proposal for this project.";
    } else {
      $error = "Error: " . $e->getMessage();
    }
  }
}

$pageTitle = 'Submit Proposal - SkillBridge';
include 'includes/header.php';
?>

<nav class="admin-navbar">
  <div class="navbar-content">
    <div class="navbar-links">
      <a href="freelancer-dashboard.php" class="navbar-link">Dashboard</a>
      <a href="submit-proposal.php" class="navbar-link active">Proposals</a>
      <a href="client-chat.php" class="navbar-link">Chat</a>
      <a href="work-progress.php" class="navbar-link">Contracts</a>
      <a href="wallet.php" class="navbar-link">Earnings</a>
    </div>
  </div>
</nav>

<!-- Page Header -->
<div class="section section-white" style="padding-top: 4rem; padding-bottom: 2rem;">
  <div class="container">
    <h1 class="section-title">Submit Proposal for: <?php echo htmlspecialchars($job_title); ?></h1>
    <p class="section-subtitle" style="text-align: center; font-size: 1.25rem; color: var(--gray-600);">Make your proposal stand out!</p>
  </div>
</div>

<!-- Proposal Form -->
<div class="section section-gray">
  <div class="container">
    <div class="card" style="max-width: 800px; margin: 0 auto;">

      <?php if ($error): ?>
        <div class="alert alert-danger" style="margin-bottom: 1rem; color: #721c24; background-color: #f8d7da; border: 1px solid #f5c6cb; padding: 0.75rem; border-radius: 0.25rem;">
          <?php echo $error; ?>
        </div>
      <?php endif; ?>

      <?php if ($success): ?>
        <div class="alert alert-success" style="margin-bottom: 1rem; color: #155724; background-color: #d4edda; border: 1px solid #c3e6cb; padding: 0.75rem; border-radius: 0.25rem;">
          <?php echo $success; ?>
        </div>
      <?php endif; ?>

      <form action="submit-proposal.php" method="post" class="proposal-form-fields">
        <input type="hidden" name="job_id" value="<?php echo $job_id; ?>">

        <div class="form-group">
          <label class="form-label">Bid Amount ($)</label>
          <input type="number" step="0.01" name="bid_amount" class="form-input" placeholder="Enter your bid amount" required />
        </div>

        <div class="form-group">
          <label class="form-label">Cover Letter</label>
          <textarea name="cover_letter" class="form-textarea-proposal" placeholder="Explain why you are the right fit for this job"
            rows="6" required></textarea>
          <p class="form-hint">Provide details on your relevant experience and skills</p>
        </div>

        <div class="form-actions">
          <a href="view-project-details.php?id=<?php echo $job_id; ?>" class="btn btn-secondary btn-sm">Cancel</a>
          <button type="submit" class="btn btn-primary btn-sm" style="border: none; cursor: pointer;">Submit proposal</button>
        </div>
      </form>
    </div>
  </div>
</div>

<?php include 'includes/footer.php'; ?>
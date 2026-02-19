<?php
session_start();
require_once 'includes/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'client') {
  header("Location: login.php");
  exit();
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $title = $_POST['title'];
  $description = $_POST['description'];
  $budget = $_POST['budget'];
  $deadline = $_POST['deadline'];
  $client_id = $_SESSION['user_id'];

  $stmt = $conn->prepare("INSERT INTO jobs (client_id, title, description, budget, deadline, status) VALUES (?, ?, ?, ?, ?, 'open')");
  $stmt->bind_param("isdds", $client_id, $title, $description, $budget, $deadline);

  if ($stmt->execute()) {
    $success = "Project posted successfully! It is now visible to freelancers.";
  } else {
    $error = "Error posting project: " . $stmt->error;
  }
}

$pageTitle = 'Post a Project - SkillBridge';
include 'includes/header.php';
?>

<nav class="admin-navbar">
  <div class="navbar-content">
    <div class="navbar-links">
      <a href="client-dashboard.php" class="navbar-link">Dashboard</a>
      <a href="request-quote.php" class="navbar-link">Quotes</a>
      <a href="client-chat.php" class="navbar-link">Chat</a>
      <a href="proposal-overview.php" class="navbar-link">Proposals</a>
      <a href="work-progress.php" class="navbar-link">Work Approval</a>
      <a href="client-feedback.php" class="navbar-link">Ratings & Reviews</a>
    </div>
  </div>
</nav>

<!-- Post Project Form -->
<div class="dashboard">
  <div class="container">
    <div class="dashboard-header">
      <h1>Post a New Project</h1>
      <p style="color: var(--gray-600)">
        Fill out the form below to post your project.
      </p>
    </div>

    <div class="card" style="max-width: 800px; margin: 0 auto">
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

      <form action="post-project.php" method="post" id="postProjectForm">
        <div class="form-group">
          <label class="form-label">Project Title
            <span style="color: var(--red-600)">*</span></label>
          <input type="text" class="form-input" name="title" placeholder="e.g., Build a responsive e-commerce website"
            required />
        </div>

        <div class="form-group">
          <label class="form-label">Project Description
            <span style="color: var(--red-600)">*</span></label>
          <textarea class="form-textarea" name="description" rows="6"
            placeholder="Describe your project in detail. Include requirements, deliverables, and any specific features needed."
            required></textarea>
        </div>

        <div class="grid grid-2">
          <div class="form-group">
            <label class="form-label">Budget ($) <span style="color: var(--red-600)">*</span></label>
            <input type="number" step="0.01" class="form-input" name="budget" placeholder="Enter budget" required />
          </div>

          <div class="form-group">
            <label class="form-label">Deadline <span style="color: var(--red-600)">*</span></label>
            <input type="date" class="form-input" name="deadline" required />
          </div>
        </div>

        <div style="display: flex; gap: 1rem; margin-top: 2rem">
          <button type="submit" class="btn btn-primary btn-lg">
            Post Project
          </button>
          <a href="client-dashboard.php" class="btn btn-secondary btn-lg">Cancel</a>
        </div>

        <div class="alert alert-info" style="margin-top: 1.5rem">
          <strong>Note:</strong> Once posted, your project will be visible to freelancers who can
          submit proposals. You can manage your projects from the dashboard.
        </div>
      </form>
    </div>
  </div>
</div>

<?php include 'includes/footer.php'; ?>
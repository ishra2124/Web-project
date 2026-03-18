<?php
session_start();
require_once 'includes/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
  header("Location: login.php");
  exit();
}

$error = '';
$success = '';

// Handle Verification Action
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['verification_id'])) {
  $v_id = $_POST['verification_id'];
  $status = $_POST['verificationStatus'];
  $notes = $_POST['notes'];

  $stmt = $conn->prepare("UPDATE verification_documents SET status = ?, admin_notes = ? WHERE doc_id = ?");
  $stmt->bind_param("ssi", $status, $notes, $v_id);

  if ($stmt->execute()) {
    $success = "Verification status updated successfully!";
  } else {
    $error = "Error updating status: " . $stmt->error;
  }
}

// Fetch Pending Documents
$query = "SELECT vd.*, u.full_name as freelancer_name 
          FROM verification_documents vd 
          JOIN users u ON vd.user_id = u.user_id 
          WHERE vd.status = 'pending' 
          ORDER BY vd.uploaded_at ASC";
$result = $conn->query($query);

$pageTitle = 'Skill/Document Verification - SkillBridge';
include 'includes/header.php';
?>

<nav class="admin-navbar">
  <div class="navbar-content">
    <div class="navbar-links">
      <a href="admin-dashboard.php" class="navbar-link">Dashboard</a>
      <a href="admin-projects.php" class="navbar-link">Projects</a>
      <a href="admin-freelancer-approvals.php" class="navbar-link">Freelancer Approvals</a>
      <a href="admin-client-approvals.php" class="navbar-link">Client Approvals</a>
      <a href="admin-verification.php" class="navbar-link active">Verification</a>
    </div>
  </div>
</nav>

<!-- Skill/Document Verification -->
<div class="dashboard">
  <div class="container">
    <div class="dashboard-header">
      <h1>Skill/Document Verification</h1>
      <p style="color: var(--gray-600)">Verify documents submitted by freelancers.</p>
    </div>

    <?php if ($success): ?>
      <div class="alert alert-success"><?php echo $success; ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
      <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>

    <div class="approvals-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(400px, 1fr)); gap: 20px;">
      <?php if ($result->num_rows > 0): ?>
        <?php while ($doc = $result->fetch_assoc()): ?>
          <div class="card">
            <h3 style="margin-bottom: 0.5rem;"><?php echo htmlspecialchars($doc['freelancer_name']); ?></h3>
            <p style="margin-bottom: 1rem; color: var(--gray-600);">
              <strong>Type:</strong> <?php echo ucfirst($doc['document_type']); ?><br>
              <strong>Uploaded:</strong> <?php echo date('M d, Y', strtotime($doc['uploaded_at'])); ?>
            </p>

            <div style="margin-bottom: 1.5rem; padding: 10px; background: #f9f9f9; border-radius: 5px;">
              <a href="<?php echo htmlspecialchars($doc['document_path']); ?>" target="_blank" class="btn btn-secondary btn-sm" style="width: 100%;">View Document</a>
            </div>

            <form action="admin-verification.php" method="post">
              <input type="hidden" name="verification_id" value="<?php echo $doc['doc_id']; ?>">

              <div class="form-group">
                <label class="form-label">Action</label>
                <div style="display: flex; gap: 1rem; margin-top: 0.5rem;">
                  <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                    <input type="radio" name="verificationStatus" value="approved" required> Approved
                  </label>
                  <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                    <input type="radio" name="verificationStatus" value="rejected"> Rejected
                  </label>
                </div>
              </div>

              <div class="form-group">
                <label class="form-label">Notes (Optional)</label>
                <textarea class="form-input" name="notes" rows="2" placeholder="Reason for approval/rejection..."></textarea>
              </div>

              <button type="submit" class="btn btn-primary btn-sm" style="width: 100%;">Submit Verification</button>
            </form>
          </div>
        <?php endwhile; ?>
      <?php else: ?>
        <div class="card" style="grid-column: 1 / -1; text-align: center; padding: 50px;">
          <h3>No pending verifications.</h3>
        </div>
      <?php endif; ?>
    </div>
  </div>
</div>

<?php include 'includes/footer.php'; ?>
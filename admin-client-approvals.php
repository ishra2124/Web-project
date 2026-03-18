<?php
session_start();
require_once 'includes/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
  header("Location: login.php");
  exit();
}

$error = '';
$success = '';

// Handle Approval/Rejection
if (isset($_GET['action']) && isset($_GET['user_id'])) {
  $action = $_GET['action'];
  $u_id = $_GET['user_id'];
  $status = ($action === 'approve') ? 'active' : 'suspended';

  $stmt = $conn->prepare("UPDATE users SET status = ? WHERE user_id = ? AND role = 'client'");
  $stmt->bind_param("si", $status, $u_id);

  if ($stmt->execute()) {
    $success = "Client " . ($action === 'approve' ? 'approved' : 'rejected') . " successfully!";
  } else {
    $error = "Error: " . $stmt->error;
  }
}

// Fetch Pending Clients with their latest verification document if any
$query = "SELECT u.*, cp.company_name, cp.company_description, vd.document_path, vd.doc_id 
          FROM users u 
          LEFT JOIN client_profiles cp ON u.user_id = cp.client_id 
          LEFT JOIN verification_documents vd ON u.user_id = vd.user_id AND vd.status = 'pending'
          WHERE u.role = 'client' AND u.status = 'pending' 
          ORDER BY u.created_at ASC";
$result = $conn->query($query);

$pageTitle = 'Client Approvals - SkillBridge';
include 'includes/header.php';
?>

<nav class="admin-navbar">
  <div class="navbar-content">
    <div class="navbar-links">
      <a href="admin-dashboard.php" class="navbar-link">Dashboard</a>
      <a href="admin-projects.php" class="navbar-link">Projects</a>
      <a href="admin-freelancer-approvals.php" class="navbar-link">Freelancer Approvals</a>
      <a href="admin-client-approvals.php" class="navbar-link active">Client Approvals</a>
      <a href="admin-verification.php" class="navbar-link">Verification</a>
    </div>
  </div>
</nav>

<!-- Pending Client Approvals -->
<div class="dashboard">
  <div class="container">
    <div class="dashboard-header">
      <h1>Pending Client Approvals</h1>
      <p style="color: var(--gray-600)">Review and approve client registrations.</p>
    </div>

    <?php if ($success): ?>
      <div class="alert alert-success"><?php echo $success; ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
      <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>

    <div class="approvals-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(350px, 1fr)); gap: 20px;">
      <?php if ($result->num_rows > 0): ?>
        <?php while ($c = $result->fetch_assoc()): ?>
          <div class="approval-card" style="background: #ffffff; border: 1px solid var(--gray-200); border-radius: 12px; padding: 20px; box-shadow: 0 2px 4px rgba(0,0,0,0.08);">
            <div style="display: flex; align-items: center; gap: 15px; margin-bottom: 1.5rem;">
              <div class="avatar avatar-sm" style="width: 60px; height: 60px; font-size: 2rem; flex-shrink: 0; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; display: flex; align-items: center; justify-content: center;">🏢</div>
              <div style="flex: 1; min-width: 0;">
                <h3 style="font-size: 1.1rem; font-weight: 600; margin: 0 0 0.25rem 0;"><?php echo htmlspecialchars($c['company_name'] ?? $c['full_name']); ?></h3>
                <p style="color: var(--gray-600); font-size: 0.9rem; margin: 0;"><?php echo htmlspecialchars($c['email']); ?></p>
              </div>
            </div>
            <div style="background: #f9f9f9; border-radius: 8px; padding: 12px; margin-bottom: 1rem; font-size: 0.9rem; color: var(--gray-600);">
              <p style="margin: 0.5rem 0;"><strong>Rep:</strong> <?php echo htmlspecialchars($c['full_name']); ?></p>
              <p style="margin: 0.5rem 0;"><strong>Phone:</strong> <?php echo htmlspecialchars($c['phone'] ?? 'N/A'); ?></p>
              <?php if ($c['document_path']): ?>
                <p style="margin: 0.5rem 0;"><a href="<?php echo htmlspecialchars($c['document_path']); ?>" target="_blank" style="color: var(--blue-600); font-weight: 600;"><i class="fas fa-file-alt"></i> View Verification Document</a></p>
              <?php endif; ?>
            </div>
            <div style="display: flex; gap: 0.5rem;">
              <a href="admin-user-details.php?id=<?php echo $c['user_id']; ?>" class="btn btn-secondary btn-sm" style="flex: 1;">Details</a>
              <a href="admin-client-approvals.php?action=approve&user_id=<?php echo $c['user_id']; ?>" class="btn btn-success btn-sm" style="flex: 1;">Approve</a>
              <a href="admin-client-approvals.php?action=reject&user_id=<?php echo $c['user_id']; ?>" class="btn btn-danger btn-sm" style="flex: 1;">Reject</a>
            </div>
          </div>
        <?php endwhile; ?>
      <?php else: ?>
        <div class="card" style="grid-column: 1 / -1; text-align: center; padding: 50px;">
          <h3>No pending client approvals.</h3>
        </div>
      <?php endif; ?>
    </div>
  </div>
</div>

<?php include 'includes/footer.php'; ?>
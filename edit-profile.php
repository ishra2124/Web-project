<?php
session_start();
require_once 'includes/db.php';

if (!isset($_SESSION['user_id'])) {
  header("Location: login.php");
  exit();
}

$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];
$error = '';
$success = '';

// Handle Profile Update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $full_name = $_POST['full_name'];
  $phone = $_POST['phone'];

  $conn->begin_transaction();
  try {
    // Update users table
    $stmt = $conn->prepare("UPDATE users SET full_name = ?, phone = ? WHERE user_id = ?");
    $stmt->bind_param("ssi", $full_name, $phone, $user_id);
    $stmt->execute();

    if ($role === 'freelancer') {
      $title = $_POST['title'];
      $skills = $_POST['skills'];
      $hourly_rate = $_POST['hourly_rate'];
      $bio = $_POST['bio'];
      $github_link = $_POST['github_link'];

      $stmt_f = $conn->prepare("UPDATE freelancer_profiles SET title = ?, skills = ?, hourly_rate = ?, bio = ?, github_link = ? WHERE freelancer_id = ?");
      $stmt_f->bind_param("ssdssi", $title, $skills, $hourly_rate, $bio, $github_link, $user_id);
      $stmt_f->execute();
    } elseif ($role === 'client') {
      $company_name = $_POST['company_name'];
      $company_description = $_POST['company_description'];

      $stmt_c = $conn->prepare("UPDATE client_profiles SET company_name = ?, company_description = ? WHERE client_id = ?");
      $stmt_c->bind_param("ssi", $company_name, $company_description, $user_id);
      $stmt_c->execute();
    }

    $conn->commit();
    $success = "Profile updated successfully!";
  } catch (Exception $e) {
    $conn->rollback();
    $error = "Error updating profile: " . $e->getMessage();
  }
}

// Fetch current data
$stmt_user = $conn->prepare("SELECT * FROM users WHERE user_id = ?");
$stmt_user->bind_param("i", $user_id);
$stmt_user->execute();
$u = $stmt_user->get_result()->fetch_assoc();

if ($role === 'freelancer') {
  $stmt_prof = $conn->prepare("SELECT * FROM freelancer_profiles WHERE freelancer_id = ?");
  $stmt_prof->bind_param("i", $user_id);
  $stmt_prof->execute();
  $profile = $stmt_prof->get_result()->fetch_assoc();
} else {
  $stmt_prof = $conn->prepare("SELECT * FROM client_profiles WHERE client_id = ?");
  $stmt_prof->bind_param("i", $user_id);
  $stmt_prof->execute();
  $profile = $stmt_prof->get_result()->fetch_assoc();
}

$pageTitle = 'Edit Profile - SkillBridge';
include 'includes/header.php';
?>

<nav class="admin-navbar">
  <div class="navbar-content">
    <div class="navbar-links">
      <?php if ($role === 'client'): ?>
        <a href="client-dashboard.php" class="navbar-link">Dashboard</a>
        <a href="request-quote.php" class="navbar-link">Quotes</a>
        <a href="client-chat.php" class="navbar-link">Chat</a>
        <a href="proposal-overview.php" class="navbar-link">Proposals</a>
        <a href="work-progress.php" class="navbar-link">Work Approval</a>
      <?php else: ?>
        <a href="freelancer-dashboard.php" class="navbar-link">Dashboard</a>
        <a href="submit-proposal.php" class="navbar-link">Proposals</a>
        <a href="client-chat.php" class="navbar-link">Chat</a>
        <a href="work-progress.php" class="navbar-link">Contracts</a>
        <a href="wallet.php" class="navbar-link">Earnings</a>
      <?php endif; ?>
    </div>
  </div>
</nav>

<div class="section section-white" style="padding-top: 4rem; padding-bottom: 2rem;">
  <div class="container">
    <h1 class="section-title">Edit Your Profile</h1>
    <p class="section-subtitle" style="text-align: center;">Keep your professional information up to date.</p>
  </div>
</div>

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

      <form action="edit-profile.php" method="post" class="form-fields-section">
        <div class="form-group">
          <label class="form-label">Full Name</label>
          <input type="text" name="full_name" class="form-input" value="<?php echo htmlspecialchars($u['full_name']); ?>" required />
        </div>

        <div class="form-group">
          <label class="form-label">Phone Number</label>
          <input type="text" name="phone" class="form-input" value="<?php echo htmlspecialchars($u['phone'] ?? ''); ?>" />
        </div>

        <?php if ($role === 'freelancer'): ?>
          <div class="form-group">
            <label class="form-label">Professional Title</label>
            <input type="text" name="title" class="form-input" value="<?php echo htmlspecialchars($profile['title'] ?? ''); ?>" placeholder="e.g. Senior Full Stack Developer" />
          </div>

          <div class="form-group">
            <label class="form-label">Skills (comma separated)</label>
            <input type="text" name="skills" class="form-input" value="<?php echo htmlspecialchars($profile['skills'] ?? ''); ?>" placeholder="e.g. PHP, MySQL, React" />
          </div>

          <div class="form-group">
            <label class="form-label">Hourly Rate ($)</label>
            <input type="number" step="0.01" name="hourly_rate" class="form-input" value="<?php echo htmlspecialchars($profile['hourly_rate'] ?? '0.00'); ?>" />
          </div>

          <div class="form-group">
            <label class="form-label">Bio</label>
            <textarea name="bio" class="form-input" rows="4"><?php echo htmlspecialchars($profile['bio'] ?? ''); ?></textarea>
          </div>

          <div class="form-group">
            <label class="form-label">GitHub Link</label>
            <input type="url" name="github_link" class="form-input" value="<?php echo htmlspecialchars($profile['github_link'] ?? ''); ?>" placeholder="https://github.com/yourprofile" />
          </div>
        <?php elseif ($role === 'client'): ?>
          <div class="form-group">
            <label class="form-label">Company Name</label>
            <input type="text" name="company_name" class="form-input" value="<?php echo htmlspecialchars($profile['company_name'] ?? ''); ?>" />
          </div>

          <div class="form-group">
            <label class="form-label">Company Description</label>
            <textarea name="company_description" class="form-input" rows="4"><?php echo htmlspecialchars($profile['company_description'] ?? ''); ?></textarea>
          </div>
        <?php endif; ?>

        <div class="form-actions">
          <a href="<?php echo $role; ?>-dashboard.php" class="btn btn-secondary btn-sm">Cancel</a>
          <button type="submit" class="btn btn-primary btn-sm" style="border: none; cursor: pointer;">Save Changes</button>
        </div>
      </form>
    </div>
  </div>
</div>

<?php include 'includes/footer.php'; ?>
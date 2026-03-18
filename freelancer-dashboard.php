<?php
session_start();
require_once 'includes/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'freelancer') {
  header("Location: login.php");
  exit();
}

$user_id = $_SESSION['user_id'];

// Fetch user and profile info
$stmt = $conn->prepare("SELECT u.full_name, u.role, fp.title, fp.skills, fp.bio, fp.github_link 
                        FROM users u 
                        LEFT JOIN freelancer_profiles fp ON u.user_id = fp.freelancer_id 
                        WHERE u.user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user_data = $stmt->get_result()->fetch_assoc();

if (!$user_data) {
  header("Location: logout.php");
  exit();
}

// Fetch wallet balance
$stmt_wallet = $conn->prepare("SELECT balance FROM wallet WHERE user_id = ?");
$stmt_wallet->bind_param("i", $user_id);
$stmt_wallet->execute();
$wallet_balance = $stmt_wallet->get_result()->fetch_assoc()['balance'] ?? 0.00;

// Fetch project stats
$stmt_active = $conn->prepare("SELECT COUNT(*) as count FROM projects WHERE freelancer_id = ? AND status = 'active'");
$stmt_active->bind_param("i", $user_id);
$stmt_active->execute();
$active_count = $stmt_active->get_result()->fetch_assoc()['count'];

$stmt_completed = $conn->prepare("SELECT COUNT(*) as count FROM projects WHERE freelancer_id = ? AND status = 'completed'");
$stmt_completed->bind_param("i", $user_id);
$stmt_completed->execute();
$completed_count = $stmt_completed->get_result()->fetch_assoc()['count'];

// Fetch recent proposal updates
$stmt_recent_props = $conn->prepare("SELECT p.*, j.title FROM proposals p JOIN jobs j ON p.job_id = j.job_id WHERE p.freelancer_id = ? ORDER BY p.updated_at DESC LIMIT 3");
$stmt_recent_props->bind_param("i", $user_id);
$stmt_recent_props->execute();
$recent_proposals = $stmt_recent_props->get_result();

$pageTitle = 'Freelancer Dashboard - SkillBridge';
include 'includes/header.php';
?>

<nav class="admin-navbar">
  <div class="navbar-content">
    <div class="navbar-links">
      <a href="freelancer-dashboard.php" class="navbar-link active">Dashboard</a>
      <a href="submit-proposal.php" class="navbar-link">Proposals</a>
      <a href="freelancer-chat.php" class="navbar-link">Chat</a>
      <a href="work-progress.php" class="navbar-link">Contracts</a>
      <a href="wallet.php" class="navbar-link">Earnings</a>
    </div>
  </div>
</nav>

<!-- Profile Section -->
<div class="section section-white" style="padding-top: 4rem; padding-bottom: 2rem;">
  <div class="container">
    <div class="card">
      <div class="profile-card"
        style="display: flex; align-items: center; justify-content: space-between; gap: 20px; padding: 20px; background: linear-gradient(135deg, rgba(156, 199, 70, 0.1) 0%, rgba(49, 157, 214, 0.1) 100%); border-radius: 12px;">
        <div style="display: flex; align-items: center; gap: 20px; flex: 1;">
          <div
            style="width: 80px; height: 80px; background: linear-gradient(135deg, var(--blue-600), var(--blue-700)); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-size: 2rem; font-weight: 600;">
            <?php
            $initials = "";
            $names = explode(" ", $user_data['full_name']);
            foreach ($names as $n) $initials .= $n[0];
            echo strtoupper(substr($initials, 0, 2));
            ?>
          </div>
          <div>
            <h3 style="font-size: 1.3rem; font-weight: 600; margin: 0 0 0.5rem 0;"><?php echo htmlspecialchars($user_data['full_name']); ?></h3>
            <p style="color: var(--gray-600); margin: 0 0 0.5rem 0; font-size: 0.95rem;"><span
                class="badge badge-info"><?php echo ucfirst($user_data['role']); ?></span></p>
            <p style="color: var(--gray-600); margin: 0; font-size: 0.95rem;"><?php echo htmlspecialchars($user_data['title'] ?? 'Full-stack Developer'); ?></p>
          </div>
        </div>
        <div style="display: flex; gap: 10px;">
          <a href="logout.php" class="btn btn-secondary btn-sm">Log Out</a>
          <a href="edit-profile.php" class="btn btn-primary btn-sm">Edit Profile</a>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Performance Overview -->
<div class="section section-gray">
  <div class="container">
    <div class="card">
      <h2 class="section-title">Performance Overview</h2>
      <p class="section-subtitle" style="margin-bottom: 1.5rem;">Track your freelance journey</p>
      <div class="performance-grid"
        style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 20px;">
        <div class="perf-card"
          style="background: linear-gradient(135deg, var(--blue-600) 0%, var(--blue-700) 100%); border-radius: 12px; padding: 20px; color: white;">
          <p style="opacity: 0.9; margin-bottom: 0.5rem; font-size: 0.95rem;">Wallet Balance</p>
          <p style="font-size: 1.8rem; font-weight: 700; margin-bottom: 0.75rem;"><a href="wallet.php"
              style="color: white; text-decoration: none;">$<?php echo number_format($wallet_balance, 2); ?></a></p>
          <a href="wallet.php" class="btn btn-primary btn-sm" style="background: rgba(255,255,255,0.2);">View
            Details</a>
        </div>
        <div class="perf-card"
          style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); border-radius: 12px; padding: 20px; color: white;">
          <p style="opacity: 0.9; margin-bottom: 0.5rem; font-size: 0.95rem;">Rating</p>
          <p style="font-size: 1.8rem; font-weight: 700; margin-bottom: 0.75rem;">0.0 / 5.0</p>
          <span style="opacity: 0.8;">-</span>
        </div>
        <div class="perf-card"
          style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); border-radius: 12px; padding: 20px; color: white;">
          <p style="opacity: 0.9; margin-bottom: 0.5rem; font-size: 0.95rem;">Active Projects</p>
          <p style="font-size: 1.8rem; font-weight: 700; margin-bottom: 0.75rem;"><?php echo $active_count; ?></p>
          <a href="work-progress.php" class="btn btn-primary btn-sm" style="background: rgba(255,255,255,0.2);">View
            All</a>
        </div>
        <div class="perf-card"
          style="background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%); border-radius: 12px; padding: 20px; color: white;">
          <p style="opacity: 0.9; margin-bottom: 0.5rem; font-size: 0.95rem;">Completed Projects</p>
          <p style="font-size: 1.8rem; font-weight: 700; margin-bottom: 0.75rem;"><?php echo $completed_count; ?></p>
          <span style="opacity: 0.8;">-</span>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Recent Proposal Updates -->
<div class="section section-white">
  <div class="container">
    <div class="card">
      <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
        <h2 class="section-title" style="margin-bottom: 0;">Recent Proposal Updates</h2>
        <a href="proposal-stats.php" class="btn btn-outline btn-sm">View All Proposals</a>
      </div>
      <div class="proposal-activity-list" style="display: flex; flex-direction: column; gap: 15px;">
        <?php if ($recent_proposals->num_rows > 0): ?>
          <?php while ($rp = $recent_proposals->fetch_assoc()): ?>
            <div class="activity-item" style="padding: 15px; background: #f9f9f9; border-radius: 10px; display: flex; justify-content: space-between; align-items: center; border-left: 4px solid <?php echo $rp['status'] === 'accepted' ? 'var(--green-600)' : ($rp['status'] === 'rejected' ? 'var(--red-600)' : 'var(--blue-600)'); ?>;">
              <div>
                <h4 style="margin: 0 0 5px 0; font-size: 1rem;"><?php echo htmlspecialchars($rp['title']); ?></h4>
                <p style="margin: 0; font-size: 0.85rem; color: var(--gray-600);">
                  Status updated <span style="font-weight: 600; color: var(--gray-800);"><?php echo date('M d, H:i', strtotime($rp['updated_at'])); ?></span>
                </p>
                <?php if ($rp['client_comment']): ?>
                  <p style="margin: 8px 0 0 0; font-size: 0.85rem; font-style: italic; color: var(--blue-600);">
                    "<?php echo htmlspecialchars(substr($rp['client_comment'], 0, 100)); ?>..."
                  </p>
                <?php endif; ?>
              </div>
              <div style="text-align: right;">
                <span class="badge <?php echo $rp['status'] === 'pending' ? 'badge-warning' : ($rp['status'] === 'accepted' ? 'badge-info' : 'badge-danger'); ?>" style="display: inline-block; margin-bottom: 8px;">
                  <?php echo ucfirst($rp['status']); ?>
                </span>
                <br>
                <a href="proposal-details.php?proposal_id=<?php echo $rp['proposal_id']; ?>" style="font-size: 0.85rem; color: var(--blue-600); text-decoration: none; font-weight: 500;">Details &rarr;</a>
              </div>
            </div>
          <?php endwhile; ?>
        <?php else: ?>
          <div style="text-align: center; padding: 20px; color: var(--gray-500);">
            No proposals submitted yet.
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<div class="section section-white">
  <div class="container">
    <div class="card">
      <h2 class="section-title">Profile Details</h2>
      <p class="section-subtitle" style="margin-bottom: 1.5rem;">Your professional information</p>
      <div class="details-grid"
        style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px;">
        <div class="detail-item"
          style="background: #f9f9f9; border-radius: 10px; padding: 16px; display: flex; flex-direction: column;">
          <p style="color: var(--gray-600); font-size: 0.95rem; margin: 0 0 0.75rem 0; font-weight: bold;">Bio</p>
          <p style="font-size: 0.95rem; margin: 0; color: var(--gray-700);"><?php echo htmlspecialchars($user_data['bio'] ?? 'No bio provided.'); ?></p>
        </div>
        <div class="detail-item"
          style="background: #f9f9f9; border-radius: 10px; padding: 16px; display: flex; flex-direction: column;">
          <p style="color: var(--gray-600); font-size: 0.95rem; margin: 0 0 0.75rem 0; font-weight: bold;">GitHub Link
          </p>
          <p style="font-size: 0.95rem; margin: 0;"><a href="<?php echo htmlspecialchars($user_data['github_link'] ?? '#'); ?>"
              style="color: var(--blue-600); text-decoration: none;" target="_blank"><?php echo htmlspecialchars($user_data['github_link'] ?? 'Not provided'); ?></a></p>
        </div>
        <div class="detail-item"
          style="background: #f9f9f9; border-radius: 10px; padding: 16px; display: flex; flex-direction: column; gap: 5px;">
          <p style="color: var(--gray-600); font-size: 0.95rem; margin: 0 0 0.75rem 0; font-weight: bold;">Skills</p>
          <div style="display: flex; flex-wrap: wrap; gap: 8px;">
            <?php
            $skills = explode(",", $user_data['skills'] ?? '');
            foreach ($skills as $skill):
              $skill = trim($skill);
              if ($skill):
            ?>
                <span class="badge"><?php echo htmlspecialchars($skill); ?></span>
            <?php endif;
            endforeach;
            if (empty($skills) || (count($skills) == 1 && $skills[0] == '')) echo "No skills listed."; ?>
          </div>
        </div>
        <div class="detail-item"
          style="background: #f9f9f9; border-radius: 10px; padding: 16px; display: flex; flex-direction: column;">
          <p style="color: var(--gray-600); font-size: 0.95rem; margin: 0 0 0.75rem 0; font-weight: bold;">Portfolio
            Documents</p>
          <a href="view-portfolio.php?id=<?php echo $user_id; ?>" style="font-size: 0.95rem; margin: 0;"><i class="fas fa-file-pdf"
              style="margin-right: 0.5rem; color: #ef4444;"></i>Generate Dynamic Portfolio (PDF)</a>
        </div>
      </div>
    </div>
  </div>
</div>

<?php include 'includes/footer.php'; ?>
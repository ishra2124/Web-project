<?php
session_start();
require_once 'includes/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$user_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$user_id) {
    echo "No user selected.";
    exit();
}

// Fetch user basic data
$stmt = $conn->prepare("SELECT * FROM users WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

if (!$user) {
    echo "User not found.";
    exit();
}

// Fetch role-specific profile data
$profile = null;
if ($user['role'] === 'freelancer') {
    $stmt_p = $conn->prepare("SELECT * FROM freelancer_profiles WHERE freelancer_id = ?");
    $stmt_p->bind_param("i", $user_id);
    $stmt_p->execute();
    $profile = $stmt_p->get_result()->fetch_assoc();
} elseif ($user['role'] === 'client') {
    $stmt_p = $conn->prepare("SELECT * FROM client_profiles WHERE client_id = ?");
    $stmt_p->bind_param("i", $user_id);
    $stmt_p->execute();
    $profile = $stmt_p->get_result()->fetch_assoc();
}

// Fetch verification documents
$stmt_docs = $conn->prepare("SELECT * FROM verification_documents WHERE user_id = ?");
$stmt_docs->bind_param("i", $user_id);
$stmt_docs->execute();
$docs = $stmt_docs->get_result();

$pageTitle = 'User Details - Admin Panel';
include 'includes/header.php';
?>

<nav class="admin-navbar">
    <div class="navbar-content">
        <div class="navbar-links">
            <a href="admin-dashboard.php" class="navbar-link">Dashboard</a>
            <a href="admin-projects.php" class="navbar-link">Projects</a>
            <a href="admin-freelancer-approvals.php" class="navbar-link">Freelancer Approvals</a>
            <a href="admin-client-approvals.php" class="navbar-link">Client Approvals</a>
            <a href="admin-verification.php" class="navbar-link">Verification</a>
        </div>
    </div>
</nav>

<div class="section section-gray" style="padding: 4rem 0;">
    <div class="container">
        <div style="max-width: 900px; margin: 0 auto;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
                <h1 style="font-size: 2rem; font-weight: 700; margin: 0;">User Details: <?php echo htmlspecialchars($user['full_name']); ?></h1>
                <a href="<?php echo $user['role'] === 'freelancer' ? 'admin-freelancer-approvals.php' : 'admin-client-approvals.php'; ?>" class="btn btn-secondary btn-sm">Back to List</a>
            </div>

            <div class="grid grid-2">
                <!-- Basic Info -->
                <div class="card">
                    <h3 style="margin-bottom: 1.5rem; border-bottom: 1px solid var(--gray-200); padding-bottom: 0.5rem;">Basic Information</h3>
                    <div style="display: flex; flex-direction: column; gap: 15px;">
                        <p><strong>Full Name:</strong> <?php echo htmlspecialchars($user['full_name']); ?></p>
                        <p><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
                        <p><strong>Phone:</strong> <?php echo htmlspecialchars($user['phone'] ?? 'Not provided'); ?></p>
                        <p><strong>Role:</strong> <span class="badge"><?php echo ucfirst($user['role']); ?></span></p>
                        <p><strong>Status:</strong> <span class="badge <?php echo $user['status'] === 'approved' ? 'badge-success' : ($user['status'] === 'pending' ? 'badge-warning' : 'badge-danger'); ?>"><?php echo ucfirst($user['status']); ?></span></p>
                        <p><strong>Joined:</strong> <?php echo date('M d, Y', strtotime($user['created_at'])); ?></p>
                    </div>
                </div>

                <!-- Profile Info -->
                <div class="card">
                    <h3 style="margin-bottom: 1.5rem; border-bottom: 1px solid var(--gray-200); padding-bottom: 0.5rem;">Profile Information</h3>
                    <?php if ($user['role'] === 'freelancer' && $profile): ?>
                        <div style="display: flex; flex-direction: column; gap: 15px;">
                            <p><strong>Title:</strong> <?php echo htmlspecialchars($profile['title'] ?? 'N/A'); ?></p>
                            <p><strong>Experience:</strong> <?php echo ucfirst($profile['experience_level'] ?? 'N/A'); ?></p>
                            <p><strong>Hourly Rate:</strong> $<?php echo number_format($profile['hourly_rate'] ?? 0, 2); ?>/hr</p>
                            <p><strong>GitHub:</strong> <a href="<?php echo htmlspecialchars($profile['github_link'] ?? '#'); ?>" target="_blank"><?php echo htmlspecialchars($profile['github_link'] ?? 'N/A'); ?></a></p>
                            <div>
                                <strong>Skills:</strong>
                                <div style="display: flex; flex-wrap: wrap; gap: 5px; margin-top: 5px;">
                                    <?php
                                    $skills = explode(',', $profile['skills'] ?? '');
                                    foreach ($skills as $skill) {
                                        if (trim($skill)) echo '<span class="skill-tag">' . htmlspecialchars(trim($skill)) . '</span>';
                                    }
                                    if (empty(array_filter($skills))) echo 'N/A';
                                    ?>
                                </div>
                            </div>
                        </div>
                    <?php elseif ($user['role'] === 'client' && $profile): ?>
                        <div style="display: flex; flex-direction: column; gap: 15px;">
                            <p><strong>Company Name:</strong> <?php echo htmlspecialchars($profile['company_name'] ?? 'N/A'); ?></p>
                            <p><strong>Company Description:</strong><br><small style="color: var(--gray-600);"><?php echo nl2br(htmlspecialchars($profile['company_description'] ?? 'N/A')); ?></small></p>
                        </div>
                    <?php else: ?>
                        <p>No extra profile information found.</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Bio / Description -->
            <?php if ($user['role'] === 'freelancer' && $profile && $profile['bio']): ?>
                <div class="card" style="margin-top: 2rem;">
                    <h3 style="margin-bottom: 1rem; border-bottom: 1px solid var(--gray-200); padding-bottom: 0.5rem;">Professional Bio</h3>
                    <p style="line-height: 1.6; color: var(--gray-700);"><?php echo nl2br(htmlspecialchars($profile['bio'])); ?></p>
                </div>
            <?php endif; ?>

            <!-- Verification Documents -->
            <div class="card" style="margin-top: 2rem;">
                <h3 style="margin-bottom: 1.5rem; border-bottom: 1px solid var(--gray-200); padding-bottom: 0.5rem;">Verification Documents</h3>
                <?php if ($docs->num_rows > 0): ?>
                    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 15px;">
                        <?php while ($doc = $docs->fetch_assoc()): ?>
                            <div style="border: 1px solid var(--gray-200); padding: 10px; border-radius: 8px; text-align: center;">
                                <p style="font-weight: 600; font-size: 0.85rem; margin-bottom: 0.5rem;"><?php echo ucfirst($doc['document_type']); ?></p>
                                <a href="<?php echo htmlspecialchars($doc['document_path']); ?>" target="_blank" class="btn btn-secondary btn-sm" style="width: 100%; padding: 5px; font-size: 0.8rem;">View File</a>
                                <p style="font-size: 0.75rem; color: var(--gray-500); margin-top: 5px;">Status: <?php echo ucfirst($doc['status']); ?></p>
                            </div>
                        <?php endwhile; ?>
                    </div>
                <?php else: ?>
                    <p style="color: var(--gray-500); text-align: center;">No documents uploaded for verification yet.</p>
                <?php endif; ?>
            </div>

            <!-- Admin Actions -->
            <?php if ($user['status'] === 'pending'): ?>
                <div class="card" style="margin-top: 2rem; border: 2px solid var(--gray-200); background-color: var(--blue-50);">
                    <h3 style="margin-bottom: 1.5rem; text-align: center;">Pending Approval Action</h3>
                    <div style="display: flex; justify-content: center; gap: 20px;">
                        <a href="<?php echo $user['role'] === 'freelancer' ? 'admin-freelancer-approvals.php' : 'admin-client-approvals.php'; ?>?action=approve&user_id=<?php echo $user['user_id']; ?>" class="btn btn-success" style="padding-left: 3rem; padding-right: 3rem;">Approve Account</a>
                        <a href="<?php echo $user['role'] === 'freelancer' ? 'admin-freelancer-approvals.php' : 'admin-client-approvals.php'; ?>?action=reject&user_id=<?php echo $user['user_id']; ?>" class="btn btn-danger" style="padding-left: 3rem; padding-right: 3rem;">Reject Account</a>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
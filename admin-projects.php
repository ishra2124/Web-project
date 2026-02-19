<?php
session_start();
require_once 'includes/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Fetch all projects with job, client, and freelancer info
$query = "SELECT p.*, 
                 j.title as job_title, 
                 u_client.full_name as client_name, 
                 u_free.full_name as freelancer_name
          FROM projects p
          JOIN jobs j ON p.job_id = j.job_id
          JOIN users u_client ON p.client_id = u_client.user_id
          JOIN users u_free ON p.freelancer_id = u_free.user_id
          ORDER BY p.start_date DESC";

$result = $conn->query($query);

$pageTitle = 'Manage Projects - Admin Panel';
include 'includes/header.php';
?>

<nav class="admin-navbar">
    <div class="navbar-content">
        <div class="navbar-links">
            <a href="admin-dashboard.php" class="navbar-link">Dashboard</a>
            <a href="admin-projects.php" class="navbar-link active">Projects</a>
            <a href="admin-freelancer-approvals.php" class="navbar-link">Freelancers</a>
            <a href="admin-client-approvals.php" class="navbar-link">Clients</a>
            <a href="admin-verification.php" class="navbar-link">Verifications</a>
        </div>
    </div>
</nav>

<div class="section section-white">
    <div class="container">
        <h1 class="section-title">All Project Contracts</h1>
        <p class="section-subtitle">Monitor all active and completed contracts platform-wide.</p>

        <div class="card" style="margin-top: 2rem;">
            <div class="table-container">
                <table class="table admin-table" style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="background: #f9fafb; display: table-row;">
                            <th style="padding: 15px; text-align: left; width: 5%; display: table-cell;">ID</th>
                            <th style="padding: 15px; text-align: left; width: 30%; display: table-cell;">Project Title</th>
                            <th style="padding: 15px; text-align: left; width: 20%; display: table-cell;">Client</th>
                            <th style="padding: 15px; text-align: left; width: 20%; display: table-cell;">Freelancer</th>
                            <th style="padding: 15px; text-align: left; width: 10%; display: table-cell;">Status</th>
                            <th style="padding: 15px; text-align: left; width: 15%; display: table-cell;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result->num_rows > 0): ?>
                            <?php while ($row = $result->fetch_assoc()): ?>
                                <tr style="border-bottom: 1px solid #eee; display: table-row;">
                                    <td style="padding: 15px; display: table-cell;">#<?php echo $row['project_id']; ?></td>
                                    <td style="padding: 15px; display: table-cell;"><strong><?php echo htmlspecialchars($row['job_title']); ?></strong></td>
                                    <td style="padding: 15px; display: table-cell;"><?php echo htmlspecialchars($row['client_name']); ?></td>
                                    <td style="padding: 15px; display: table-cell;"><?php echo htmlspecialchars($row['freelancer_name']); ?></td>
                                    <td style="padding: 15px; display: table-cell;">
                                        <span class="badge <?php echo $row['status'] === 'active' ? 'badge-info' : ($row['status'] === 'completed' ? 'badge-success' : 'badge-danger'); ?>">
                                            <?php echo ucfirst($row['status']); ?>
                                        </span>
                                    </td>
                                    <td style="padding: 15px; display: table-cell;">
                                        <a href="view-contract.php?id=<?php echo $row['project_id']; ?>" class="btn btn-secondary btn-sm" style="padding: 4px 10px; font-size: 0.8rem;">
                                            <i class="fas fa-file-contract"></i> View Contract
                                        </a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr style="display: table-row;">
                                <td colspan="6" style="padding: 30px; text-align: center; color: #9ca3af; display: table-cell;">No projects found on the platform.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
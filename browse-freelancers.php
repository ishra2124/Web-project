<?php
session_start();
require_once 'includes/db.php';

// Handle Search
$search = $_GET['search'] ?? '';

$query = "SELECT u.user_id, u.full_name, fp.title, fp.skills, fp.bio, fp.hourly_rate 
          FROM users u 
          JOIN freelancer_profiles fp ON u.user_id = fp.freelancer_id 
          WHERE u.role = 'freelancer' AND u.status = 'approved'";

if ($search) {
  $search_safe = $conn->real_escape_string($search);
  $query .= " AND (u.full_name LIKE '%$search_safe%' OR fp.title LIKE '%$search_safe%' OR fp.skills LIKE '%$search_safe%' OR fp.bio LIKE '%$search_safe%')";
}

$result = $conn->query($query);

$pageTitle = 'Browse Freelancers - SkillBridge';
include 'includes/header.php';
?>
<?php include 'includes/header.php'; ?>

<?php if (isset($_SESSION['role'])): ?>
  <nav class="admin-navbar">
    <div class="navbar-content">
      <div class="navbar-links">
        <?php if ($_SESSION['role'] === 'client'): ?>
          <a href="client-dashboard.php" class="navbar-link">Dashboard</a>
          <a href="request-quote.php" class="navbar-link">Quotes</a>
          <a href="client-chat.php" class="navbar-link">Chat</a>
          <a href="proposal-overview.php" class="navbar-link">Proposals</a>
          <a href="work-progress.php" class="navbar-link">Work Approval</a>
          <a href="client-feedback.php" class="navbar-link">Ratings & Reviews</a>
        <?php else: ?>
          <a href="freelancer-dashboard.php" class="navbar-link">Dashboard</a>
          <a href="submit-proposal.php" class="navbar-link">Proposals</a>
          <a href="freelancer-chat.php" class="navbar-link">Chat</a>
          <a href="work-progress.php" class="navbar-link">Contracts</a>
          <a href="wallet.php" class="navbar-link">Earnings</a>
        <?php endif; ?>
      </div>
    </div>
  </nav>
<?php endif; ?>

<!-- Browse Freelancers -->
<div class="dashboard">
  <div class="container">
    <div class="dashboard-header">
      <h1>Browse Freelancers</h1>
      <p style="color: var(--gray-600)">
        Find the perfect talent for your project
      </p>
    </div>

    <!-- Search & Filters -->
    <div class="card" style="margin-bottom: 2rem">
      <form action="browse-freelancers.php" method="get" style="display: flex; gap: 1rem; margin-bottom: 1.5rem;">
        <input type="text" name="search" class="form-input" placeholder="Search freelancers by name, title, or skills..." value="<?php echo htmlspecialchars($search); ?>" style="flex: 1;">
        <button type="submit" class="btn btn-primary">Search</button>
      </form>
      <h3 style="margin-bottom: 1rem">Popular Skills</h3>
      <div class="skill-tags">
        <a href="browse-freelancers.php?search=React" class="skill-tag" style="text-decoration: none;">React</a>
        <a href="browse-freelancers.php?search=Node.js" class="skill-tag" style="text-decoration: none;">Node.js</a>
        <a href="browse-freelancers.php?search=UI Design" class="skill-tag" style="text-decoration: none;">UI Design</a>
        <a href="browse-freelancers.php?search=PHP" class="skill-tag" style="text-decoration: none;">PHP</a>
        <a href="browse-freelancers.php?search=Python" class="skill-tag" style="text-decoration: none;">Python</a>
      </div>
    </div>

    <!-- Freelancers Grid -->
    <div class="grid grid-3">
      <?php if ($result && $result->num_rows > 0): ?>
        <?php while ($f = $result->fetch_assoc()): ?>
          <div class="card">
            <div class="avatar avatar-lg" style="margin: 0 auto 1rem; display: flex; align-items: center; justify-content: center; background: var(--blue-600); color: white; border-radius: 50%;">
              <?php echo strtoupper(substr($f['full_name'], 0, 1)); ?>
            </div>
            <h3 style="text-align: center"><?php echo htmlspecialchars($f['full_name']); ?></h3>
            <p style="text-align: center; color: var(--gray-600)"><?php echo htmlspecialchars($f['title'] ?? 'Freelancer'); ?></p>
            <div style="display: flex; justify-content: center; align-items: center; gap: 1rem; font-size: 0.875rem; color: var(--gray-600); margin-bottom: 1rem;">
              <div class="rating">
                ⭐ <span>5.0</span>
              </div>
              <span>•</span>
              <span>$<?php echo number_format($f['hourly_rate'] ?? 0, 2); ?>/hr</span>
            </div>
            <div class="skill-tags" style="justify-content: center; margin-bottom: 1rem">
              <?php
              $skills = explode(',', $f['skills'] ?? '');
              foreach (array_slice($skills, 0, 3) as $skill): if (trim($skill)): ?>
                  <span class="skill-tag"><?php echo htmlspecialchars(trim($skill)); ?></span>
              <?php endif;
              endforeach; ?>
            </div>
            <p style="text-align: center; font-size: 0.875rem; color: var(--gray-600); margin-bottom: 1rem; height: 3rem; overflow: hidden; text-overflow: ellipsis;">
              <?php echo htmlspecialchars(substr($f['bio'] ?? '', 0, 100)); ?>...
            </p>
            <div style="text-align: center">
              <a href="view-portfolio.php?id=<?php echo $f['user_id']; ?>" class="btn btn-primary btn-sm">View Portfolio</a>
            </div>
          </div>
        <?php endwhile; ?>
      <?php else: ?>
        <div class="col-span-3 text-center" style="grid-column: 1 / -1; padding: 4rem;">
          <p style="color: var(--gray-500); font-size: 1.2rem;">No freelancers found in the database.</p>
        </div>
      <?php endif; ?>
    </div>
  </div>
</div>

<?php include 'includes/footer.php'; ?>
</body>

</html>
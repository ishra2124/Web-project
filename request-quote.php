<?php
session_start();
// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
  header("Location: login.php");
  exit();
}
$pageTitle = 'Request a Quote - SkillBridge';
include 'includes/header.php';
?>

<nav class="admin-navbar">
  <div class="navbar-content">
    <div class="navbar-links">
      <a href="client-dashboard.php" class="navbar-link">Dashboard</a>
      <a href="request-quote.php" class="navbar-link active">Quotes</a>
      <a href="client-chat.php" class="navbar-link">Chat</a>
      <a href="proposal-overview.php" class="navbar-link">Proposals</a>
      <a href="work-progress.php" class="navbar-link">Work Approval</a>
      <a href="client-feedback.php" class="navbar-link">Ratings & Reviews</a>
    </div>
  </div>
</nav>

<!-- Request a Quote Section -->
<div class="section section-white" style="padding-top: 4rem; padding-bottom: 2rem;">
  <div class="container">
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 3rem; align-items: center;">
      <div>
        <h1 class="section-title" style="text-align: left; margin-bottom: 1rem;">Request a Quote</h1>
        <div class="form-group" style="margin-bottom: 1.5rem;">
          <input type="text" class="form-input" placeholder="I'm looking for a quote for..." />
        </div>
        <button class="btn btn-primary btn-sm">Get Quote</button>
      </div>
      <div
        style="background-color: var(--gray-100); border-radius: 0.5rem; padding: 2rem; text-align: center; min-height: 300px; display: flex; align-items: center; justify-content: center;">
        <div style="color: var(--gray-400); font-size: 1.25rem;">Quote Request</div>
      </div>
    </div>
  </div>
</div>

<!-- Quote Request Form -->
<div class="section section-gray">
  <div class="container">
    <div class="card" style="max-width: 800px; margin: 0 auto;">
      <h2 class="section-title" style="font-size: 1.5rem; margin-bottom: 2rem;">Quote Request Form</h2>
      <div class="form-fields-section">
        <div class="form-group">
          <label class="form-label">Project Name</label>
          <input type="text" class="form-input" placeholder="Enter project name" />
        </div>

        <div class="form-group">
          <label class="form-label">Project Type</label>
          <select class="form-input">
            <option>Web Development</option>
            <option>Mobile App Development</option>
            <option>Graphic Design</option>
            <option>Content Writing</option>
            <option>SEO</option>
            <option>Other</option>
          </select>
        </div>

        <div class="form-group">
          <label class="form-label">Budget</label>
          <input type="text" class="form-input" placeholder="Enter your budget range" />
        </div>

        <div class="form-group">
          <label class="form-label">Deadline</label>
          <input type="date" class="form-input" />
        </div>

        <div class="form-group">
          <label class="form-label">Description</label>
          <textarea class="form-textarea" rows="5"
            placeholder="Describe your project requirements in detail..."></textarea>
        </div>

        <div class="form-actions">
          <button class="btn btn-primary btn-sm">Submit</button>
        </div>
      </div>
    </div>
  </div>
</div>

<?php include 'includes/footer.php'; ?>
</body>

</html>
</body>

</html>
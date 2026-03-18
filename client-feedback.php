<?php
session_start();
// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
  header("Location: login.php");
  exit();
}
$pageTitle = 'Customer Feedback - SkillBridge';
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
      <a href="client-feedback.php" class="navbar-link active">Ratings & Reviews</a>
    </div>
  </div>
</nav>

<!-- Customer Feedback Header -->
<div class="section section-white" style="padding-top: 4rem; padding-bottom: 2rem;">
  <div class="container">
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 3rem; align-items: center;">
      <div>
        <h1 class="section-title" style="text-align: left; margin-bottom: 1rem;">Customer Feedback</h1>
        <p style="color: var(--gray-600); font-size: 1.125rem;">See what our clients say about working with
          freelancers on SkillBridge</p>
      </div>
      <div
        style="background-color: var(--gray-100); border-radius: 0.5rem; padding: 2rem; text-align: center; min-height: 250px; display: flex; align-items: center; justify-content: center;">
        <div style="color: var(--gray-400); font-size: 1.25rem;">Customer Feedback</div>
      </div>
    </div>
  </div>
</div>

<!-- Recent Reviews -->
<div class="section section-gray">
  <div class="container">
    <!-- Rate a Freelancer Section -->
    <div class="card" style="margin-bottom: 2rem;">
      <h2 class="section-title" style="font-size: 1.5rem; margin-bottom: 1.5rem; text-align: left;">Rate a Freelancer
      </h2>
      <form>
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 1rem;">
          <div class="form-group" style="margin-bottom: 0;">
            <label class="form-label">Select Freelancer</label>
            <select class="form-select">
              <option value="">Select a freelancer...</option>
              <option value="1">John Smith - Web Design</option>
              <option value="2">Sarah Johnson - SEO</option>
              <option value="3">Emily Davis - Illustration</option>
            </select>
          </div>
          <div class="form-group" style="margin-bottom: 0;">
            <label class="form-label">Rating</label>
            <div style="font-size: 1.5rem; color: #fbbf24; padding-top: 5px;">
              <i class="far fa-star service-rating" style="cursor: pointer;"></i>
              <i class="far fa-star service-rating" style="cursor: pointer;"></i>
              <i class="far fa-star service-rating" style="cursor: pointer;"></i>
              <i class="far fa-star service-rating" style="cursor: pointer;"></i>
              <i class="far fa-star service-rating" style="cursor: pointer;"></i>
            </div>
          </div>
        </div>

        <div class="form-group">
          <label class="form-label">Your Review</label>
          <textarea class="form-input" rows="3"
            placeholder="Share your experience working with this freelancer..."></textarea>
        </div>

        <div style="text-align: right;">
          <button type="button" class="btn btn-primary btn-sm">Submit Review</button>
        </div>
      </form>
    </div>

    <div class="card">
      <h2 class="section-title" style="font-size: 1.5rem; margin-bottom: 1.5rem;">Recent Reviews</h2>
      <div class="reviews-grid"
        style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 20px;">
        <div class="review-card" style="background: #f9f9f9; border-radius: 12px; padding: 20px;">
          <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 1rem;">
            <div
              style="width: 50px; height: 50px; background: linear-gradient(135deg, var(--blue-600), var(--blue-700)); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-weight: 600; font-size: 1rem;">
              JS</div>
            <div>
              <p style="font-weight: 600; margin: 0; font-size: 0.95rem;">John Smith</p>
              <div style="display: flex; gap: 1px;">
                <i class="fas fa-star" style="color: #fbbf24; font-size: 0.8rem;"></i>
                <i class="fas fa-star" style="color: #fbbf24; font-size: 0.8rem;"></i>
                <i class="fas fa-star" style="color: #fbbf24; font-size: 0.8rem;"></i>
                <i class="fas fa-star" style="color: #fbbf24; font-size: 0.8rem;"></i>
                <i class="fas fa-star" style="color: #fbbf24; font-size: 0.8rem;"></i>
              </div>
            </div>
          </div>
          <p style="color: var(--gray-600); margin-bottom: 0.75rem; font-size: 0.95rem;">Excellent work quality and
            timely delivery. Highly recommended!</p>
          <p style="color: var(--gray-500); margin: 0; font-size: 0.85rem;"><i class="fas fa-calendar-alt"
              style="margin-right: 0.5rem;"></i>2026-01-15</p>
        </div>
        <div class="review-card" style="background: #f9f9f9; border-radius: 12px; padding: 20px;">
          <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 1rem;">
            <div
              style="width: 50px; height: 50px; background: linear-gradient(135deg, #10b981, #059669); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-weight: 600; font-size: 1rem;">
              SJ</div>
            <div>
              <p style="font-weight: 600; margin: 0; font-size: 0.95rem;">Sarah Johnson</p>
              <div style="display: flex; gap: 1px;">
                <i class="fas fa-star" style="color: #fbbf24; font-size: 0.8rem;"></i>
                <i class="fas fa-star" style="color: #fbbf24; font-size: 0.8rem;"></i>
                <i class="fas fa-star" style="color: #fbbf24; font-size: 0.8rem;"></i>
                <i class="fas fa-star" style="color: #fbbf24; font-size: 0.8rem;"></i>
                <i class="fas fa-star-half-alt" style="color: #fbbf24; font-size: 0.8rem;"></i>
              </div>
            </div>
          </div>
          <p style="color: var(--gray-600); margin-bottom: 0.75rem; font-size: 0.95rem;">Very professional and
            communicative throughout the project. Exceeded expectations.</p>
          <p style="color: var(--gray-500); margin: 0; font-size: 0.85rem;"><i class="fas fa-calendar-alt"
              style="margin-right: 0.5rem;"></i>2026-01-12</p>
        </div>
        <div class="review-card" style="background: #f9f9f9; border-radius: 12px; padding: 20px;">
          <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 1rem;">
            <div
              style="width: 50px; height: 50px; background: linear-gradient(135deg, #f59e0b, #d97706); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-weight: 600; font-size: 1rem;">
              ED</div>
            <div>
              <p style="font-weight: 600; margin: 0; font-size: 0.95rem;">Emily Davis</p>
              <div style="display: flex; gap: 1px;">
                <i class="fas fa-star" style="color: #fbbf24; font-size: 0.8rem;"></i>
                <i class="fas fa-star" style="color: #fbbf24; font-size: 0.8rem;"></i>
                <i class="fas fa-star" style="color: #fbbf24; font-size: 0.8rem;"></i>
                <i class="fas fa-star" style="color: #fbbf24; font-size: 0.8rem;"></i>
                <i class="fas fa-star" style="color: #fbbf24; font-size: 0.8rem;"></i>
              </div>
            </div>
          </div>
          <p style="color: var(--gray-600); margin-bottom: 0.75rem; font-size: 0.95rem;">Outstanding work, exceeded
            expectations. Will definitely work with again.</p>
          <p style="color: var(--gray-500); margin: 0; font-size: 0.85rem;"><i class="fas fa-calendar-alt"
              style="margin-right: 0.5rem;"></i>2026-01-10</p>
        </div>
        <div class="review-card" style="background: #f9f9f9; border-radius: 12px; padding: 20px;">
          <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 1rem;">
            <div
              style="width: 50px; height: 50px; background: linear-gradient(135deg, #ef4444, #dc2626); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-weight: 600; font-size: 1rem;">
              MB</div>
            <div>
              <p style="font-weight: 600; margin: 0; font-size: 0.95rem;">Michael Brown</p>
              <div style="display: flex; gap: 1px;">
                <i class="fas fa-star" style="color: #fbbf24; font-size: 0.8rem;"></i>
                <i class="fas fa-star" style="color: #fbbf24; font-size: 0.8rem;"></i>
                <i class="fas fa-star" style="color: #fbbf24; font-size: 0.8rem;"></i>
                <i class="fas fa-star" style="color: #fbbf24; font-size: 0.8rem;"></i>
                <i class="fas fa-star-half-alt" style="color: #fbbf24; font-size: 0.8rem;"></i>
              </div>
            </div>
          </div>
          <p style="color: var(--gray-600); margin-bottom: 0.75rem; font-size: 0.95rem;">Good quality work, completed
            on time. Minor revisions needed but overall satisfied.</p>
          <p style="color: var(--gray-500); margin: 0; font-size: 0.85rem;"><i class="fas fa-calendar-alt"
              style="margin-right: 0.5rem;"></i>2026-01-08</p>
        </div>
      </div>
      <div style="text-align: center; margin-top: 1.5rem;">
        <a href="client-feedback.html" class="btn btn-primary btn-sm">View All</a>
      </div>
    </div>
  </div>
</div>

<!-- What Makes Us Special -->
<div class="section section-white">
  <div class="container">
    <h2 class="section-title" style="margin-bottom: 3rem;">What Makes Us Special</h2>
    <div class="features-grid"
      style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 30px;">
      <div class="feature-card"
        style="background: #ffffff; border-radius: 12px; padding: 30px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); text-align: center;">
        <div style="font-size: 2.5rem; margin-bottom: 1rem;">âœ“</div>
        <h3 style="font-size: 1.1rem; font-weight: 600; margin-bottom: 0.75rem;">Quality Assurance</h3>
        <p style="color: var(--gray-600); font-size: 0.95rem; margin: 0;">We provide high-quality work with attention
          to detail and professional standards. All freelancers are vetted and verified for their expertise.</p>
      </div>
      <div class="feature-card"
        style="background: #ffffff; border-radius: 12px; padding: 30px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); text-align: center;">
        <div style="font-size: 2.5rem; margin-bottom: 1rem;">ðŸ•</div>
        <h3 style="font-size: 1.1rem; font-weight: 600; margin-bottom: 0.75rem;">24/7 Support</h3>
        <p style="color: var(--gray-600); font-size: 0.95rem; margin: 0;">Our team is always ready to help you. We
          offer round-the-clock customer support to assist with any questions or issues you may have.</p>
      </div>
      <div class="feature-card"
        style="background: #ffffff; border-radius: 12px; padding: 30px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); text-align: center;">
        <div style="font-size: 2.5rem; margin-bottom: 1rem;">ðŸ”’</div>
        <h3 style="font-size: 1.1rem; font-weight: 600; margin-bottom: 0.75rem;">Secure Payments</h3>
        <p style="color: var(--gray-600); font-size: 0.95rem; margin: 0;">All transactions are secure and protected.
          We use industry-standard encryption to keep your financial information safe.</p>
      </div>
      <div class="feature-card"
        style="background: #ffffff; border-radius: 12px; padding: 30px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); text-align: center;">
        <div style="font-size: 2.5rem; margin-bottom: 1rem;">ðŸ“Š</div>
        <h3 style="font-size: 1.1rem; font-weight: 600; margin-bottom: 0.75rem;">Project Management</h3>
        <p style="color: var(--gray-600); font-size: 0.95rem; margin: 0;">Track your projects in real-time with our
          built-in project management tools. Stay updated on progress and milestones.</p>
      </div>
    </div>
  </div>
</div>

<?php include 'includes/footer.php'; ?>
</body>

</html>
</body>

</html>
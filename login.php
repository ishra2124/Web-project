<?php
session_start();
require_once 'includes/db.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $email = $_POST['email'];
  $password = $_POST['password'];
  $role = $_POST['role'];

  $stmt = $conn->prepare("SELECT user_id, password, role FROM users WHERE email = ? AND role = ?");
  $stmt->bind_param("ss", $email, $role);
  $stmt->execute();
  $result = $stmt->get_result();

  if ($user = $result->fetch_assoc()) {
    if (password_verify($password, $user['password'])) {
      $_SESSION['user_id'] = $user['user_id'];
      $_SESSION['role'] = $user['role'];

      if ($role === 'admin') {
        header("Location: admin-dashboard.php");
      } elseif ($role === 'freelancer') {
        header("Location: freelancer-dashboard.php");
      } elseif ($role === 'client') {
        header("Location: client-dashboard.php");
      }
      exit();
    } else {
      $error = "Invalid password.";
    }
  } else {
    $error = "No user found with those credentials/role.";
  }
}

$pageTitle = 'Login - SkillBridge';
include 'includes/header.php';
?>

<!-- Login Form -->
<div class="auth-container">
  <div class="auth-card">
    <div class="auth-title">
      <h1>Login</h1>
      <p style="color: var(--gray-600)">Welcome back to SkillBridge</p>
    </div>

    <?php if ($error): ?>
      <div class="alert alert-danger" style="margin-bottom: 1rem; color: #721c24; background-color: #f8d7da; border: 1px solid #f5c6cb; padding: 0.75rem; border-radius: 0.25rem;">
        <?php echo $error; ?>
      </div>
    <?php endif; ?>

    <form action="login.php" method="post">
      <div class="form-group">
        <label class="form-label">Email</label>
        <input type="email" class="form-input" name="email" placeholder="you@example.com" required />
      </div>

      <div class="form-group">
        <label class="form-label">Password</label>
        <input type="password" class="form-input" name="password" placeholder="**********" required />
      </div>

      <div class="form-group">
        <label class="form-label">Login as:</label>
        <select class="form-select" name="role" required>
          <option value="">Select role...</option>
          <option value="admin">Admin</option>
          <option value="freelancer">Freelancer</option>
          <option value="client">Client</option>
        </select>
      </div>

      <button type="submit" class="btn btn-primary"
        style="width: 100%; margin-bottom: 1rem; display: block; border: none; font-size: 1rem; cursor: pointer;">
        Login
      </button>

      <p style="text-align: center; color: var(--gray-600)">
        Don't have an account?
        <a href="signup.php" style="color: var(--blue-600)">Sign up</a>
      </p>
    </form>



    <div style="text-align: center; margin-top: 1rem">
      <a href="index.php" style="color: var(--gray-600); font-size: 0.875rem">&larr; Back to Home</a>
    </div>
  </div>
</div>

<?php include 'includes/footer.php'; ?>
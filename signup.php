<?php
session_start();
// die("PHP is working on signup.php"); // Uncomment to test if script is reached
error_reporting(E_ALL);
ini_set('display_errors', 1);
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
require_once 'includes/db.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $name = $_POST['name'];
  $email = $_POST['email'];
  $password = $_POST['password'];
  $confirmPassword = $_POST['confirmPassword'];
  $role = $_POST['role'];

  if ($password !== $confirmPassword) {
    $error = "Passwords do not match.";
  } else {
    // Start transaction for multi-table insert
    $conn->begin_transaction();

    try {
      // 1. Insert into users table
      $hashed_password = password_hash($password, PASSWORD_DEFAULT);
      $stmt = $conn->prepare("INSERT INTO users (full_name, email, password, role) VALUES (?, ?, ?, ?)");
      $stmt->bind_param("ssss", $name, $email, $hashed_password, $role);
      $stmt->execute();
      $user_id = $conn->insert_id;

      // 2. Initialize wallet
      $stmt_wallet = $conn->prepare("INSERT INTO wallet (user_id, balance) VALUES (?, 0.00)");
      $stmt_wallet->bind_param("i", $user_id);
      $stmt_wallet->execute();

      // 3. Initialize profile based on role
      if ($role === 'freelancer') {
        $github = $_POST['github'] ?? '';
        $stmt_profile = $conn->prepare("INSERT INTO freelancer_profiles (freelancer_id, github_link) VALUES (?, ?)");
        $stmt_profile->bind_param("is", $user_id, $github);
        $stmt_profile->execute();
      } elseif ($role === 'client') {
        $stmt_profile = $conn->prepare("INSERT INTO client_profiles (client_id) VALUES (?)");
        $stmt_profile->bind_param("i", $user_id);
        $stmt_profile->execute();
      }

      $conn->commit();
      $success = "Account created successfully! You can now <a href='login.php'>Login</a>.";
    } catch (Throwable $e) {
      $conn->rollback();
      if (isset($conn->errno) && $conn->errno == 1062) {
        $error = "Email already registered.";
      } else {
        $error = "System Error: " . $e->getMessage() . " in " . $e->getFile() . " on line " . $e->getLine();
      }
    }
  }
}

$pageTitle = 'Sign Up - SkillBridge';
include 'includes/header.php';
?>
<style>
  .role-selector {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
  }

  .role-option {
    display: none;
  }

  .role-option+label {
    display: block;
    padding: 0.625rem 0.75rem;
    border: 1px solid var(--gray-300);
    border-radius: 0.375rem;
    cursor: pointer;
    background-color: var(--white);
    transition: all 0.2s;
  }

  .role-option+label:hover {
    background-color: var(--gray-50);
  }

  .role-option:checked+label {
    border-color: var(--blue-600);
    background-color: var(--blue-50);
    color: var(--blue-700);
  }

  #freelancerFields {
    display: none;
  }

  #roleFreelancer:checked~#freelancerFields {
    display: block;
  }
</style>

<!-- Signup Form -->
<div class="auth-container">
  <div class="auth-card">
    <div class="auth-title">
      <h1>Sign Up</h1>
      <p style="color: var(--gray-600)">Create your SkillBridge account</p>
    </div>

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

    <form action="signup.php" method="post" id="signupForm">
      <div class="form-group">
        <label class="form-label">Full Name</label>
        <input type="text" class="form-input" name="name" required />
      </div>

      <div class="form-group">
        <label class="form-label">Email</label>
        <input type="email" class="form-input" name="email" required />
      </div>

      <div class="form-group">
        <label class="form-label">Password</label>
        <input type="password" class="form-input" name="password" required />
      </div>

      <div class="form-group">
        <label class="form-label">Confirm Password</label>
        <input type="password" class="form-input" name="confirmPassword" required />
      </div>

      <div class="form-group">
        <label class="form-label">I want to:</label>
        <div class="role-selector">
          <input type="radio" name="role" id="roleFreelancer" value="freelancer" class="role-option" required />
          <label for="roleFreelancer">Work as a Freelancer</label>

          <input type="radio" name="role" id="roleClient" value="client" class="role-option" required />
          <label for="roleClient">Hire Freelancers</label>

          <div id="freelancerFields">
            <div class="form-group" style="margin-top: 1rem">
              <label class="form-label">Portfolio URL
                <span style="color: var(--red-600)">*</span></label>
              <input type="url" class="form-input" name="portfolio" placeholder="https://your-portfolio.com" />
            </div>

            <div class="form-group">
              <label class="form-label">GitHub Profile URL
                <span style="color: var(--red-600)">*</span></label>
              <input type="url" class="form-input" name="github" placeholder="https://github.com/username" />
            </div>
          </div>
        </div>
      </div>

      <button type="submit" class="btn btn-primary" style="width: 100%; margin-bottom: 1rem; border: none; font-size: 1rem; cursor: pointer;">
        Create Account
      </button>

      <p style="text-align: center; color: var(--gray-600)">
        Already have an account?
        <a href="login.php" style="color: var(--blue-600)">Login</a>
      </p>
    </form>

    <div class="alert alert-info" style="margin-top: 1.5rem">
      <strong>Note:</strong> Freelancer accounts require admin approval
      before you can start bidding on jobs.
    </div>

    <div style="text-align: center; margin-top: 1rem">
      <a href="index.php" style="color: var(--gray-600); font-size: 0.875rem">&larr; Back to Home</a>
    </div>
  </div>
</div>

<?php include 'includes/footer.php'; ?>
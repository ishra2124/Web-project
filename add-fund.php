<?php
session_start();
require_once 'includes/db.php';

if (!isset($_SESSION['user_id'])) {
  header("Location: login.php");
  exit();
}

$user_id = $_SESSION['user_id'];
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $amount = (float)$_POST['amount'];
  $method = $_POST['method'];

  if ($amount <= 0) {
    $error = "Please enter a valid positive amount.";
  } else {
    $conn->begin_transaction();
    try {
      // Get wallet ID
      $stmt_wallet = $conn->prepare("SELECT wallet_id FROM wallet WHERE user_id = ?");
      $stmt_wallet->bind_param("i", $user_id);
      $stmt_wallet->execute();
      $wallet = $stmt_wallet->get_result()->fetch_assoc();
      $wallet_id = $wallet['wallet_id'];

      // Update balance
      $stmt_update = $conn->prepare("UPDATE wallet SET balance = balance + ? WHERE wallet_id = ?");
      $stmt_update->bind_param("di", $amount, $wallet_id);
      $stmt_update->execute();

      // Record transaction
      $desc = "Funds added via " . ucfirst($method);
      $type = 'credit';
      $stmt_trans = $conn->prepare("INSERT INTO transactions (wallet_id, amount, type, description) VALUES (?, ?, ?, ?)");
      $stmt_trans->bind_param("idss", $wallet_id, $amount, $type, $desc);
      $stmt_trans->execute();

      $conn->commit();
      $success = "Successfully added $" . number_format($amount, 2) . " to your wallet!";
    } catch (Exception $e) {
      $conn->rollback();
      $error = "Transaction failed: " . $e->getMessage();
    }
  }
}

$pageTitle = 'Add Fund - SkillBridge';
include 'includes/header.php';
?>

<nav class="admin-navbar">
  <div class="navbar-content">
    <div class="navbar-links">
      <?php if ($_SESSION['role'] === 'client'): ?>
        <a href="client-dashboard.php" class="navbar-link">Dashboard</a>
        <a href="client-chat.php" class="navbar-link">Chat</a>
        <a href="wallet.php" class="navbar-link active">Wallet</a>
      <?php else: ?>
        <a href="freelancer-dashboard.php" class="navbar-link">Dashboard</a>
        <a href="freelancer-chat.php" class="navbar-link">Chat</a>
        <a href="wallet.php" class="navbar-link active">Earnings</a>
      <?php endif; ?>
    </div>
  </div>
</nav>

<!-- Main Content -->
<div class="section section-gray" style="padding-top: 6rem; min-height: 80vh;">
  <div class="container">
    <div class="card" style="max-width: 600px; margin: 0 auto;">
      <h2 class="section-title" style="margin-bottom: 1.5rem;">Add Fund</h2>

      <?php if ($error): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
      <?php endif; ?>
      <?php if ($success): ?>
        <div class="alert alert-success"><?php echo $success; ?></div>
      <?php endif; ?>

      <form action="add-fund.php" method="post" style="display: flex; flex-direction: column; gap: 20px;">
        <div class="form-group">
          <label class="form-label">Amount ($)</label>
          <input type="number" step="0.01" name="amount" class="form-input" placeholder="Enter amount to add" required />
        </div>

        <div class="form-group">
          <label class="form-label">Payment Method</label>
          <select name="method" class="form-input" required>
            <option value="" disabled selected>Select Payment Method</option>
            <option value="bkash">bKash</option>
            <option value="nagad">Nagad</option>
            <option value="bank">Bank Transfer</option>
          </select>
        </div>

        <div class="form-actions" style="margin-top: 1rem; display: flex; gap: 10px;">
          <a href="wallet.php" class="btn btn-secondary btn-sm">Cancel</a>
          <button type="submit" class="btn btn-primary btn-sm">Add Funds</button>
        </div>
      </form>
    </div>
  </div>
</div>

<?php include 'includes/footer.php'; ?>
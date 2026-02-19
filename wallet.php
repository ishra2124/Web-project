<?php
session_start();
require_once 'includes/db.php';

if (!isset($_SESSION['user_id'])) {
  header("Location: login.php");
  exit();
}

$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];

// Fetch wallet balance
$stmt_wallet = $conn->prepare("SELECT wallet_id, balance FROM wallet WHERE user_id = ?");
$stmt_wallet->bind_param("i", $user_id);
$stmt_wallet->execute();
$wallet = $stmt_wallet->get_result()->fetch_assoc();
$balance = $wallet['balance'] ?? 0.00;
$wallet_id = $wallet['wallet_id'] ?? null;

// Fetch transaction history
$transactions = [];
if ($wallet_id) {
  $stmt_trans = $conn->prepare("SELECT * FROM transactions WHERE wallet_id = ? ORDER BY created_at DESC");
  $stmt_trans->bind_param("i", $wallet_id);
  $stmt_trans->execute();
  $transactions = $stmt_trans->get_result();
}

$pageTitle = 'Wallet Dashboard - SkillBridge';
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
        <a href="freelancer-chat.php" class="navbar-link">Chat</a>
        <a href="work-progress.php" class="navbar-link">Contracts</a>
        <a href="wallet.php" class="navbar-link active">Earnings</a>
      <?php endif; ?>
    </div>
  </div>
</nav>

<!-- Wallet Header -->
<div class="section section-white" style="padding-top: 4rem; padding-bottom: 2rem;">
  <div class="container">
    <h1 class="section-title">Wallet Dashboard</h1>
    <p class="section-subtitle" style="text-align: center;">Manage your wallet balance and transactions.</p>
  </div>
</div>

<!-- Current Wallet Balance -->
<div class="section section-gray">
  <div class="container">
    <div class="card">
      <h2 class="section-title" style="font-size: 1.5rem; margin-bottom: 1rem;">Current Wallet Balance</h2>
      <div class="wallet-balance-container"
        style="background: linear-gradient(135deg, var(--blue-600) 0%, var(--blue-700) 100%); border-radius: 14px; padding: 30px; color: white; display: flex; justify-content: space-between; align-items: center;">
        <div>
          <p style="opacity: 0.9; margin-bottom: 0.5rem;">Available Balance</p>
          <p style="font-size: 2.5rem; font-weight: 700;">$<?php echo number_format($balance, 2); ?></p>
        </div>
        <div style="display: flex; gap: 10px;">
          <a href="withdraw.php" class="btn btn-outline btn-sm">Withdraw</a>
          <a href="add-fund.php" class="btn btn-secondary btn-sm"
            style="color: var(--blue-600); background: white; border-color: white;">Add Funds</a>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Transaction History -->
<div class="section section-white" id="transaction-history">
  <div class="container">
    <div class="card">
      <h2 class="section-title" style="font-size: 1.5rem; margin-bottom: 1rem;">Transaction History</h2>
      <div class="transactions-list" style="display: flex; flex-direction: column; gap: 12px;">
        <?php if ($transactions && $transactions->num_rows > 0): ?>
          <?php while ($t = $transactions->fetch_assoc()): ?>
            <div class="transaction-item"
              style="display: flex; justify-content: space-between; align-items: center; padding: 16px; background: #f9f9f9; border-radius: 10px; border-left: 4px solid <?php echo $t['type'] === 'credit' ? '#22c55e' : '#ef4444'; ?>;">
              <div>
                <p style="font-weight: 600; margin-bottom: 0.25rem;"><?php echo htmlspecialchars($t['description']); ?></p>
                <p style="font-size: 0.9rem; color: var(--gray-600);"><?php echo date('M d, Y H:i', strtotime($t['created_at'])); ?></p>
              </div>
              <p style="font-size: 1.1rem; font-weight: 600; color: <?php echo $t['type'] === 'credit' ? '#22c55e' : '#ef4444'; ?>;">
                <?php echo ($t['type'] === 'credit' ? '+' : '-') . '$' . number_format($t['amount'], 2); ?>
              </p>
            </div>
          <?php endwhile; ?>
        <?php else: ?>
          <div style="text-align: center; padding: 20px; color: var(--gray-500);">
            No transactions found.
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<!-- Supported Wallets -->
<div class="section section-gray">
  <div class="container">
    <div class="card">
      <h2 class="section-title" style="font-size: 1.5rem; margin-bottom: 1rem;">Supported Wallets</h2>
      <div class="wallets-grid"
        style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px;">
        <div class="wallet-option" style="background: #ffffff; border-radius: 12px; padding: 24px; text-align: center; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
          <i class="fas fa-mobile" style="font-size: 2rem; color: #d81b60; margin-bottom: 1rem; display: block;"></i>
          <h3 style="font-weight: 600; margin-bottom: 0.5rem;">bKash</h3>
        </div>
        <div class="wallet-option" style="background: #ffffff; border-radius: 12px; padding: 24px; text-align: center; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
          <i class="fas fa-credit-card" style="font-size: 2rem; color: #1434cb; margin-bottom: 1rem; display: block;"></i>
          <h3 style="font-weight: 600; margin-bottom: 0.5rem;">VISA</h3>
        </div>
        <div class="wallet-option" style="background: #ffffff; border-radius: 12px; padding: 24px; text-align: center; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
          <i class="fas fa-paypal" style="font-size: 2rem; color: #003087; margin-bottom: 1rem; display: block;"></i>
          <h3 style="font-weight: 600; margin-bottom: 0.5rem;">PayPal</h3>
        </div>
      </div>
    </div>
  </div>
</div>

<?php include 'includes/footer.php'; ?>
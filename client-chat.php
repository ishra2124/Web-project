<?php
session_start();
require_once 'includes/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'client') {
  header("Location: login.php");
  exit();
}

$user_id = $_SESSION['user_id'];
$other_user_id = isset($_GET['user_id']) ? (int)$_GET['user_id'] : 0;

// Handle message sending
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['message']) && $other_user_id) {
  $msg_text = $conn->real_escape_string($_POST['message']);
  $stmt = $conn->prepare("INSERT INTO messages (sender_id, receiver_id, message) VALUES (?, ?, ?)");
  $stmt->bind_param("iis", $user_id, $other_user_id, $msg_text);
  $stmt->execute();
}

// Fetch freelancers this client can chat with (those who applied for their jobs)
$conv_query = "SELECT DISTINCT u.user_id, u.full_name, u.role, fp.title 
               FROM users u 
               JOIN proposals p ON u.user_id = p.freelancer_id 
               JOIN jobs j ON p.job_id = j.job_id 
               LEFT JOIN freelancer_profiles fp ON u.user_id = fp.freelancer_id 
               WHERE j.client_id = $user_id";
$conversations = $conn->query($conv_query);

// Fetch messages if a user is selected
$messages = [];
$other_user_name = "Select a contact";
if ($other_user_id) {
  $msg_query = "SELECT * FROM messages 
                  WHERE (sender_id = $user_id AND receiver_id = $other_user_id) 
                     OR (sender_id = $other_user_id AND receiver_id = $user_id) 
                  ORDER BY sent_at ASC";
  $messages = $conn->query($msg_query);

  $name_query = "SELECT full_name FROM users WHERE user_id = $other_user_id";
  $res_name = $conn->query($name_query);
  if ($res_name && $u = $res_name->fetch_assoc()) {
    $other_user_name = $u['full_name'];
  }
}

$pageTitle = 'Chat - SkillBridge';
include 'includes/header.php';
?>

<nav class="admin-navbar">
  <div class="navbar-content">
    <div class="navbar-links">
      <a href="client-dashboard.php" class="navbar-link">Dashboard</a>
      <a href="request-quote.php" class="navbar-link">Quotes</a>
      <a href="client-chat.php" class="navbar-link active">Chat</a>
      <a href="proposal-overview.php" class="navbar-link">Proposals</a>
      <a href="work-progress.php" class="navbar-link">Work Approval</a>
      <a href="client-feedback.php" class="navbar-link">Ratings & Reviews</a>
    </div>
  </div>
</nav>

<!-- Two-Panel Chat Interface -->
<div class="section section-gray" style="padding-top: 4rem; min-height: 80vh;">
  <div class="container">
    <div style="display: flex; gap: 20px; max-width: 1200px; margin: 0 auto; height: 600px;">

      <!-- Left Panel: Freelancer List -->
      <div class="card" style="width: 300px; flex-shrink: 0; padding: 20px; overflow-y: auto;">
        <h3 style="font-size: 1.2rem; font-weight: 600; margin-bottom: 1.5rem;">Messages</h3>
        <div style="display: flex; flex-direction: column; gap: 10px;">
          <?php if ($conversations->num_rows > 0): ?>
            <?php while ($conv = $conversations->fetch_assoc()): ?>
              <a href="client-chat.php?user_id=<?php echo $conv['user_id']; ?>"
                style="text-decoration: none; color: inherit; display: flex; align-items: center; gap: 12px; padding: 12px; border-radius: 10px; border-left: <?php echo ($other_user_id == $conv['user_id']) ? '4px solid var(--blue-600)' : 'none'; ?>; background: <?php echo ($other_user_id == $conv['user_id']) ? 'var(--blue-50)' : '#f9f9f9'; ?>;">
                <div style="width: 45px; height: 45px; background: linear-gradient(135deg, var(--blue-600), var(--blue-700)); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-weight: 600;">
                  <?php echo strtoupper(substr($conv['full_name'], 0, 1) . substr(strrchr($conv['full_name'], " "), 1, 1)); ?>
                </div>
                <div style="flex: 1; min-width: 0;">
                  <p style="font-weight: 600; margin: 0; font-size: 0.95rem; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;"><?php echo htmlspecialchars($conv['full_name']); ?></p>
                  <p style="color: var(--gray-600); margin: 0; font-size: 0.85rem;"><?php echo htmlspecialchars($conv['title'] ?? 'Freelancer'); ?></p>
                </div>
              </a>
            <?php endwhile; ?>
          <?php else: ?>
            <p style="color: var(--gray-500); text-align: center; margin-top: 2rem;">No contacts found.</p>
          <?php endif; ?>
        </div>
      </div>

      <!-- Right Panel: Chat Area -->
      <div class="card" style="flex: 1; display: flex; flex-direction: column;">
        <?php if ($other_user_id): ?>
          <!-- Chat Header -->
          <div style="display: flex; align-items: center; justify-content: space-between; padding: 15px 20px; border-bottom: 1px solid var(--gray-200);">
            <div style="display: flex; align-items: center; gap: 15px;">
              <div style="width: 45px; height: 45px; background: linear-gradient(135deg, var(--blue-600), var(--blue-700)); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-weight: 600;">
                <?php echo strtoupper(substr($other_user_name, 0, 1)); ?>
              </div>
              <div>
                <h3 style="font-size: 1.1rem; font-weight: 600; margin: 0;"><?php echo htmlspecialchars($other_user_name); ?></h3>
                <p style="color: var(--gray-600); margin: 0; font-size: 0.85rem;"><i class="fas fa-circle" style="color: var(--green-600); font-size: 0.7rem; margin-right: 0.3rem;"></i> Online</p>
              </div>
            </div>
          </div>

          <!-- Messages -->
          <div id="chatBox" style="flex: 1; overflow-y: auto; padding: 20px; display: flex; flex-direction: column; gap: 15px;">
            <?php if ($messages && $messages->num_rows > 0): ?>
              <?php while ($msg = $messages->fetch_assoc()): ?>
                <?php $isMe = ($msg['sender_id'] == $user_id); ?>
                <div style="display: flex; flex-direction: column; align-items: <?php echo $isMe ? 'flex-end' : 'flex-start'; ?>;">
                  <div style="max-width: 80%; padding: 10px 15px; border-radius: 12px; font-size: 0.95rem; background: <?php echo $isMe ? 'var(--blue-600)' : '#f1f1f1'; ?>; color: <?php echo $isMe ? 'white' : 'var(--gray-900)'; ?>;">
                    <?php echo htmlspecialchars($msg['message']); ?>
                  </div>
                  <span style="font-size: 0.75rem; color: var(--gray-500); margin-top: 4px;">
                    <?php echo date('H:i', strtotime($msg['sent_at'])); ?>
                  </span>
                </div>
              <?php endwhile; ?>
            <?php else: ?>
              <div style="text-align: center; color: var(--gray-500); margin-top: 5rem;">No messages yet. Say hi!</div>
            <?php endif; ?>
          </div>

          <!-- Message Input -->
          <div style="padding: 20px; border-top: 1px solid var(--gray-200);">
            <form action="client-chat.php?user_id=<?php echo $other_user_id; ?>" method="post" style="display: flex; gap: 1rem;">
              <input type="text" name="message" class="form-input" placeholder="Type your message..." required style="flex: 1;" />
              <button type="submit" class="btn btn-primary">Send</button>
            </form>
          </div>
        <?php else: ?>
          <div style="flex: 1; display: flex; flex-direction: column; align-items: center; justify-content: center; color: var(--gray-500);">
            <i class="fas fa-comments" style="font-size: 4rem; margin-bottom: 1rem; opacity: 0.3;"></i>
            <p>Select a contact to start messaging</p>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<script>
  const chatBox = document.getElementById('chatBox');
  if (chatBox) chatBox.scrollTop = chatBox.scrollHeight;
</script>

<?php include 'includes/footer.php'; ?>
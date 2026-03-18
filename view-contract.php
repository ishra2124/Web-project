<?php
session_start();
require_once 'includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$project_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$project_id) {
    echo "No project selected.";
    exit();
}

// Fetch project details with job, client, and freelancer info
$query = "SELECT p.*, 
                 j.title as job_title, j.description as job_description, j.budget as job_budget,
                 u_client.full_name as client_name, u_client.email as client_email,
                 u_free.full_name as freelancer_name, u_free.email as freelancer_email
          FROM projects p
          JOIN jobs j ON p.job_id = j.job_id
          JOIN users u_client ON p.client_id = u_client.user_id
          JOIN users u_free ON p.freelancer_id = u_free.user_id
          WHERE p.project_id = ?";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $project_id);
$stmt->execute();
$project = $stmt->get_result()->fetch_assoc();

if (!$project) {
    echo "Project not found.";
    exit();
}

// Security Check: Only the freelancer, the client, or an admin should see this
if (
    $_SESSION['role'] !== 'admin' &&
    $_SESSION['user_id'] != $project['freelancer_id'] &&
    $_SESSION['user_id'] != $project['client_id']
) {
    header("Location: index.php");
    exit();
}

$pageTitle = 'Service Contract - ' . htmlspecialchars($project['job_title']);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageTitle); ?></title>
    <link rel="stylesheet" href="styles.css">
    <style>
        body {
            background-color: #f3f4f6;
            color: #1f2937;
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            line-height: 1.6;
        }

        .contract-page {
            max-width: 850px;
            margin: 40px auto;
            background: white;
            padding: 60px 80px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.05);
            border-radius: 8px;
            position: relative;
        }

        .contract-header {
            text-align: center;
            border-bottom: 2px solid #e5e7eb;
            margin-bottom: 40px;
            padding-bottom: 20px;
        }

        .contract-header h1 {
            font-size: 28px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #111827;
        }

        .contract-header p {
            color: #6b7280;
            font-size: 14px;
        }

        .section-title {
            font-size: 18px;
            font-weight: 700;
            color: #111827;
            margin-bottom: 15px;
            border-left: 4px solid var(--blue-600);
            padding-left: 12px;
            background: #f9fafb;
            padding-top: 5px;
            padding-bottom: 5px;
        }

        .contract-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 40px;
            margin-bottom: 30px;
        }

        .party-box h3 {
            font-size: 14px;
            text-transform: uppercase;
            color: #6b7280;
            margin-bottom: 10px;
        }

        .party-box p {
            font-weight: 600;
            margin-bottom: 5px;
        }

        .terms-list {
            margin-bottom: 30px;
        }

        .term-item {
            margin-bottom: 20px;
        }

        .term-item h4 {
            font-weight: 600;
            margin-bottom: 5px;
            color: #374151;
        }

        .signatures {
            margin-top: 60px;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 100px;
        }

        .sig-box {
            border-top: 1px solid #d1d5db;
            padding-top: 10px;
            text-align: center;
        }

        .sig-box p {
            font-size: 14px;
            color: #6b7280;
        }

        .watermark {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-45deg);
            font-size: 100px;
            font-weight: 900;
            color: rgba(0, 0, 0, 0.03);
            pointer-events: none;
            z-index: 0;
            white-space: nowrap;
        }

        @media print {
            body {
                background: white;
            }

            .contract-page {
                box-shadow: none;
                margin: 0;
                padding: 40px;
                width: 100%;
            }

            .no-print {
                display: none;
            }
        }
    </style>
</head>

<body>

    <div class="no-print" style="position: sticky; top: 0; background: white; z-index: 100; border-bottom: 1px solid #e5e7eb; padding: 15px 0;">
        <div class="container" style="display: flex; justify-content: space-between; align-items: center;">
            <div style="display: flex; align-items: center; gap: 15px;">
                <a href="index.php" style="text-decoration: none; font-weight: 700; color: var(--blue-600); font-size: 20px;">SkillBridge</a>
                <span style="color: #d1d5db;">|</span>
                <span style="font-size: 14px; color: #6b7280;">Contract ID: SB-<?php echo str_pad($project['project_id'], 6, '0', STR_PAD_LEFT); ?></span>
            </div>
            <div style="display: flex; gap: 10px;">
                <button onclick="window.history.back()" class="btn btn-secondary btn-sm">Go Back</button>
                <button onclick="window.print()" class="btn btn-primary btn-sm"><i class="fas fa-file-pdf"></i> Download / Print</button>
            </div>
        </div>
    </div>

    <div class="contract-page">
        <div class="watermark">SKILLBRIDGE CONTRACT</div>

        <div class="contract-header">
            <h1>Independent Contractor Agreement</h1>
            <p>Executed on <?php echo date('F d, Y', strtotime($project['start_date'])); ?></p>
        </div>

        <div class="contract-grid">
            <div class="party-box">
                <h3>The Client</h3>
                <p><?php echo htmlspecialchars($project['client_name']); ?></p>
                <p style="font-weight: 400; color: #6b7280;"><?php echo htmlspecialchars($project['client_email']); ?></p>
            </div>
            <div class="party-box">
                <h3>The Freelancer</h3>
                <p><?php echo htmlspecialchars($project['freelancer_name']); ?></p>
                <p style="font-weight: 400; color: #6b7280;"><?php echo htmlspecialchars($project['freelancer_email']); ?></p>
            </div>
        </div>

        <h2 class="section-title">1. Project Title & Scope</h2>
        <div class="terms-list">
            <div class="term-item">
                <h4>1.1 Project Title</h4>
                <p><?php echo htmlspecialchars($project['job_title']); ?></p>
            </div>
            <div class="term-item">
                <h4>1.2 Description of Services</h4>
                <p><?php echo nl2br(htmlspecialchars($project['job_description'])); ?></p>
            </div>
        </div>

        <h2 class="section-title">2. Payment & Milestone</h2>
        <div class="terms-list">
            <div class="term-item">
                <h4>2.1 Total Contract Amount</h4>
                <p>The client agrees to pay the total sum of <strong>$<?php echo number_format($project['job_budget'], 2); ?></strong> for the completed services.</p>
            </div>
            <div class="term-item">
                <h4>2.2 Payment Schedule</h4>
                <p>Payment will be released through the SkillBridge escrow system upon successful completion and approval of project milestones as defined in the work progress tracking system.</p>
            </div>
        </div>

        <h2 class="section-title">3. Timeline</h2>
        <div class="terms-list">
            <div class="term-item">
                <h4>3.1 Start Date</h4>
                <p><?php echo date('F d, Y', strtotime($project['start_date'])); ?></p>
            </div>
            <div class="term-item">
                <h4>3.2 Estimated Completion</h4>
                <p><?php echo $project['end_date'] ? date('F d, Y', strtotime($project['end_date'])) : 'To be determined by project milestones.'; ?></p>
            </div>
        </div>

        <h2 class="section-title">4. Terms & Conditions</h2>
        <div class="terms-list">
            <p style="font-size: 13px; color: #4b5563;">
                This agreement is governed by the SkillBridge Terms of Service. Both parties agree that all intellectual property rights for the deliverables created during this contract shall transfer to the Client upon final payment. SkillsBridge provides the platform for this collaboration but is not a party to the independent contractor relationship.
            </p>
        </div>

        <div class="signatures">
            <div class="sig-box">
                <p style="font-family: 'Dancing Script', cursive, serif; font-size: 24px; color: #1f2937; margin-bottom: 2px;">/s/ <?php echo htmlspecialchars($project['client_name']); ?></p>
                <p>Authorized Signature (Client)</p>
            </div>
            <div class="sig-box">
                <p style="font-family: 'Dancing Script', cursive, serif; font-size: 24px; color: #1f2937; margin-bottom: 2px;">/s/ <?php echo htmlspecialchars($project['freelancer_name']); ?></p>
                <p>Digital Signature (Freelancer)</p>
            </div>
        </div>

        <div style="margin-top: 50px; text-align: center; color: #9ca3af; font-size: 11px;">
            This is a digitally generated contract on SkillBridge. No physical signature required.
        </div>
    </div>

    <footer class="no-print" style="text-align: center; padding: 40px 0; color: #9ca3af;">
        &copy; <?php echo date('Y'); ?> SkillBridge Platform - Legal Services
    </footer>

</body>

</html>
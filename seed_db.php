<?php
require_once 'includes/db.php';

// Function to safely execute inserts
function executeQuery($conn, $sql, $params = [], $types = "")
{
    $stmt = $conn->prepare($sql);
    if ($params) {
        $stmt->bind_param($types, ...$params);
    }
    if (!$stmt->execute()) {
        throw new Exception("Error executing query: " . $stmt->error);
    }
    return $stmt;
}

try {
    $conn->begin_transaction();

    // 1. Clear existing data
    $tables = ['project_files', 'project_progress', 'messages', 'portfolios', 'certifications', 'verification_documents', 'reviews', 'transactions', 'wallet', 'projects', 'proposals', 'jobs', 'client_profiles', 'freelancer_profiles', 'users'];
    $conn->query("SET FOREIGN_KEY_CHECKS = 0;");
    foreach ($tables as $table) {
        $conn->query("TRUNCATE TABLE $table;");
    }
    $conn->query("SET FOREIGN_KEY_CHECKS = 1;");

    $password = 'password123';
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // 2. Insert Users (Total: 5 Freelancers, 3 Clients, 1 Admin)
    $users = [
        ['John Doe', 'john@example.com', $hashed_password, 'freelancer', '1234567890', 'approved'],
        ['Jane Smith', 'jane@example.com', $hashed_password, 'freelancer', '0987654321', 'approved'],
        ['Alice Web', 'alice@example.com', $hashed_password, 'freelancer', '1122334455', 'approved'],
        ['Bob Coder', 'bob@example.com', $hashed_password, 'freelancer', '5544332211', 'pending'],
        ['Sarah Dev', 'sarah@example.com', $hashed_password, 'freelancer', '9988776655', 'approved'],
        ['Tech Corp', 'info@techcorp.com', $hashed_password, 'client', '5566778899', 'approved'],
        ['Creative Studio', 'hello@creative.com', $hashed_password, 'client', '6677889900', 'approved'],
        ['Global Biz', 'contact@globalbiz.com', $hashed_password, 'client', '7788990011', 'pending'],
        ['Admin User', 'admin@skillbridge.com', $hashed_password, 'admin', '0000000000', 'approved']
    ];

    foreach ($users as $u) {
        executeQuery($conn, "INSERT INTO users (full_name, email, password, role, phone, status) VALUES (?, ?, ?, ?, ?, ?)", $u, "ssssss");
    }

    // IDs Mapping
    $f1 = 1;
    $f2 = 2;
    $f3 = 3;
    $f4 = 4;
    $f5 = 5;
    $c1 = 6;
    $c2 = 7;
    $c3 = 8;
    $admin = 9;

    // 3. Insert Freelancer Profiles
    $f_profiles = [
        [$f1, 'Senior Full Stack Developer', 'PHP, MySQL, React, Laravel', 'expert', 50.00, 'Passionate developer with 10 years of experience.', 'https://github.com/johndoe'],
        [$f2, 'UI/UX Designer', 'Figma, Adobe XD, CSS, Tailwind', 'intermediate', 35.00, 'Designing beautiful interfaces for modern web apps.', 'https://github.com/janesmith'],
        [$f3, 'Backend Engineer', 'Python, Django, PostgreSQL, Docker', 'expert', 45.00, 'Focusing on scalable backend systems.', 'https://github.com/aliceweb'],
        [$f4, 'Mobile Developer', 'React Native, Flutter, Swift', 'beginner', 25.00, 'Building cross-platform mobile apps.', 'https://github.com/bobcoder'],
        [$f5, 'Frontend Developer', 'Vue.js, Nuxt, SCSS, JavaScript', 'intermediate', 40.00, 'Expert in creating responsive and interactive UIs.', 'https://github.com/sarahdev']
    ];

    foreach ($f_profiles as $fp) {
        executeQuery($conn, "INSERT INTO freelancer_profiles (freelancer_id, title, skills, experience_level, hourly_rate, bio, github_link) VALUES (?, ?, ?, ?, ?, ?, ?)", $fp, "isssdss");
    }

    // 4. Insert Client Profiles
    $c_profiles = [
        [$c1, 'Tech Corp', 'A leading software development company based in Silicon Valley.'],
        [$c2, 'Creative Studio', 'Digital agency focused on branding, design, and marketing.'],
        [$c3, 'Global Biz', 'Multinational corporation handling logistics and supply chain.']
    ];

    foreach ($c_profiles as $cp) {
        executeQuery($conn, "INSERT INTO client_profiles (client_id, company_name, company_description) VALUES (?, ?, ?)", $cp, "iss");
    }

    // 5. Insert Wallets & Initial Transactions
    for ($i = 1; $i <= 9; $i++) {
        executeQuery($conn, "INSERT INTO wallet (user_id, balance) VALUES (?, ?)", [$i, 1000.00], "id");
        $wallet_id = $conn->insert_id;
        executeQuery($conn, "INSERT INTO transactions (wallet_id, amount, type, description) VALUES (?, ?, 'credit', 'Initial signup bonus')", [$wallet_id, 1000.00], "id");
    }

    // 6. Insert Jobs
    $jobs = [
        [$c1, 'PHP Website Refactor', 'Need to refactor an old PHP site to use modern practices and PDO.', 1500.00, '2026-03-30', 'open'],
        [$c1, 'Database Optimization', 'Looking for an expert to optimize complex MySQL queries and indexing.', 800.00, '2026-03-15', 'open'],
        [$c2, 'New Logo Design', 'Create a modern, minimalist logo for our new SaaS startup.', 400.00, '2026-02-10', 'completed'],
        [$c2, 'React Dashboard Implementation', 'Build a clean dashboard for internal analytics using React and Chart.js.', 1200.00, '2026-04-01', 'in_progress'],
        [$c3, 'Mobile App Prototype', 'Create a high-fidelity prototype for a logistics tracking app.', 2000.00, '2026-05-20', 'open'],
        [$c3, 'Python Data Scraper', 'Build a scraper to collect market data from various sources.', 600.00, '2026-03-10', 'open']
    ];

    foreach ($jobs as $j) {
        executeQuery($conn, "INSERT INTO jobs (client_id, title, description, budget, deadline, status) VALUES (?, ?, ?, ?, ?, ?)", $j, "issdss");
    }

    // 7. Insert Proposals
    $proposals = [
        [1, $f1, 1400.00, 'I can refactor your site using Laravel architecture for better scalability.', 'pending'],
        [1, $f3, 1500.00, 'Experienced dev here, I will ensure clean code and best practices.', 'pending'],
        [2, $f3, 800.00, 'I specialize in SQL optimization and can reduce query time significantly.', 'pending'],
        [3, $f2, 400.00, 'I have a strong portfolio in minimalist logos. Check out my work.', 'accepted'],
        [4, $f5, 1200.00, 'I can build this dashboard using Vue or React as per your preference.', 'accepted'],
        [5, $f4, 2000.00, 'Logging app prototype? I have built 3 similar projects recently.', 'pending']
    ];

    foreach ($proposals as $p) {
        executeQuery($conn, "INSERT INTO proposals (job_id, freelancer_id, bid_amount, cover_letter, status) VALUES (?, ?, ?, ?, ?)", $p, "iidss");
    }

    // 8. Insert Projects (Contracts)
    $projects = [
        [3, $f2, $c2, '2026-01-10', '2026-01-20', 'completed'],
        [4, $f5, $c2, '2026-01-22', NULL, 'active']
    ];

    foreach ($projects as $pr) {
        executeQuery($conn, "INSERT INTO projects (job_id, freelancer_id, client_id, start_date, end_date, status) VALUES (?, ?, ?, ?, ?, ?)", $pr, "iiisss");
    }

    // 9. Insert Project Progress & Files
    // Project 1 (Completed)
    executeQuery($conn, "INSERT INTO project_progress (project_id, progress_percent, details) VALUES (?, ?, ?)", [1, 100, 'Final logo files delivered and approved.'], "iis");
    executeQuery($conn, "INSERT INTO project_files (project_id, uploaded_by, file_name, file_path, file_size) VALUES (?, ?, ?, ?, ?)", [1, $f2, 'FinalLogo.zip', 'uploads/project_files/logo_final.zip', 2048000], "iissi");

    // Project 2 (Active)
    executeQuery($conn, "INSERT INTO project_progress (project_id, progress_percent, details) VALUES (?, ?, ?)", [2, 60, 'Main dashboard components implemented. Working on API integration.'], "iis");
    executeQuery($conn, "INSERT INTO project_progress (project_id, progress_percent, details) VALUES (?, ?, ?)", [2, 40, 'Initial setup and layout completed.'], "iis");

    // 10. Insert Reviews
    executeQuery($conn, "INSERT INTO reviews (project_id, reviewer_id, reviewee_id, rating, comment) VALUES (?, ?, ?, ?, ?)", [1, $c2, $f2, 5, 'Jane did an amazing job on the logo. Highly recommended!'], "iiiis");
    executeQuery($conn, "INSERT INTO reviews (project_id, reviewer_id, reviewee_id, rating, comment) VALUES (?, ?, ?, ?, ?)", [1, $f2, $c2, 5, 'Great client, clear requirements and fast feedback.'], "iiiis");

    // 11. Insert Verification Documents
    $v_docs = [
        [$f1, 'NID', 'uploads/verifications/nid1.jpg', 'approved', 'Verified via government database.'],
        [$f2, 'Passport', 'uploads/verifications/pass1.jpg', 'approved', 'Passport details match profile.'],
        [$f3, 'Diploma', 'uploads/verifications/cert1.jpg', 'pending', NULL],
        [$f4, 'NID', 'uploads/verifications/nid2.jpg', 'pending', NULL],
        [$f5, 'NID', 'uploads/verifications/nid3.jpg', 'approved', 'Clear document, verified.']
    ];

    foreach ($v_docs as $vd) {
        executeQuery($conn, "INSERT INTO verification_documents (user_id, document_type, document_path, status, admin_notes) VALUES (?, ?, ?, ?, ?)", $vd, "issss");
    }

    // 12. Insert Certifications & Portfolios
    executeQuery($conn, "INSERT INTO certifications (freelancer_id, title, issuer, year) VALUES (?, 'AWS Certified Developer', 'Amazon', 2023)", [$f1], "i");
    executeQuery($conn, "INSERT INTO certifications (freelancer_id, title, issuer, year) VALUES (?, 'Google UX Design Professional', 'Coursera', 2022)", [$f2], "i");

    executeQuery($conn, "INSERT INTO portfolios (freelancer_id, title, description, status) VALUES (?, 'E-commerce Engine', 'Custom PHP engine with multi-vendor support.', 'approved')", [$f1], "i");
    executeQuery($conn, "INSERT INTO portfolios (freelancer_id, title, description, status) VALUES (?, 'Portfolio Mockup', 'Modern glassmorphism design for creatives.', 'approved')", [$f2], "i");

    // 13. Insert Robust Messages (Chats)
    // Conversation between C2 and F2 (Logo Project)
    $msgs = [
        [$c2, $f2, 1, 'Hi Jane, looking forward to the sketches.', 1],
        [$f2, $c2, 1, 'Working on them now, should have them by tomorrow.', 1],
        [$f2, $c2, 1, 'Just sent the ZIP file with 3 concepts. Let me know what you think.', 1],
        [$c2, $f2, 1, 'Concept 2 is perfect! No changes needed.', 1],

        // Conversation between C2 and F5 (Dashboard Project)
        [$c2, $f5, 2, 'How is the API integration going?', 0],
        [$f5, $c2, 2, 'Going well, mostly handled. Just mapping the final endpoints.', 0],

        // Random chat (No project context)
        [$c1, $f1, NULL, 'Hey John, are you available for a quick consult?', 1],
        [$f1, $c1, NULL, 'Sure, what do you have in mind?', 0]
    ];

    foreach ($msgs as $m) {
        executeQuery($conn, "INSERT INTO messages (sender_id, receiver_id, project_id, message, is_read) VALUES (?, ?, ?, ?, ?)", $m, "iiisi");
    }

    $conn->commit();
    echo "Database successfully re-seeded with extensive dummy data!\n";
    echo "--- Users ---\n";
    echo "Freelancers: john@example.com, jane@example.com (Approved), bob@example.com (Pending)\n";
    echo "Clients: info@techcorp.com, hello@creative.com (Approved), contact@globalbiz.com (Pending)\n";
    echo "Admin: admin@skillbridge.com\n";
    echo "Password for all accounts: password123\n";
} catch (Exception $e) {
    if (isset($conn)) {
        $conn->rollback();
    }
    echo "CRITICAL ERROR while seeding: " . $e->getMessage() . "\n";
}

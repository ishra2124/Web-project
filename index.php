<?php
session_start();
$pageTitle = 'SkillBridge - Professional Freelance Marketplace';
include 'includes/header.php';

// Handle role selection via GET for now (since JS is removed)
$role = isset($_GET['role']) ? $_GET['role'] : 'hiring';
$isFreelancer = ($role === 'freelancer');
?>

<!-- Hero Section -->
<div class="hero skillbridge-hero" style="min-height: 80vh; display: flex; align-items: center;">
  <div class="hero-blob"></div>
  <div class="container">
    <div class="hero-content-wrapper">
      <div class="hero-text-content">
        <div class="hero-toggle" style="background: var(--white); padding: 0.5rem; border-radius: 2rem; width: fit-content; box-shadow: 0 2px 10px rgba(0,0,0,0.05); border: 1px solid var(--gray-200);">
          <a href="index.php?role=hiring" class="<?php echo !$isFreelancer ? 'toggle-active' : ''; ?>" style="text-decoration: none; padding: 0.5rem 1rem; border-radius: 1.5rem; background: <?php echo !$isFreelancer ? 'var(--blue-600)' : 'transparent'; ?>; color: <?php echo !$isFreelancer ? 'var(--white)' : 'var(--gray-600)'; ?>;">For hiring</a>
          <a href="index.php?role=freelancer" class="<?php echo $isFreelancer ? 'toggle-active' : ''; ?>" style="text-decoration: none; padding: 0.5rem 1rem; border-radius: 1.5rem; background: <?php echo $isFreelancer ? 'var(--blue-600)' : 'transparent'; ?>; color: <?php echo $isFreelancer ? 'var(--white)' : 'var(--gray-600)'; ?>;">For Freelancers</a>
        </div>
        <h1 class="hero-title" id="heroTitle" style="font-size: clamp(2rem, 5vw, 3.5rem);">
          <?php echo $isFreelancer ? "Find the best projects and grow your career." : "Connect with world-class talent and grow your business."; ?>
        </h1>
        <p class="hero-subtitle" id="heroSubtitle" style="font-size: 1.25rem; color: var(--gray-600);">
          <?php echo $isFreelancer ? "Explore thousands of opportunities from top clients." : "The world's largest talent marketplace for your most ambitious goals."; ?>
        </p>
        <form action="<?php echo $isFreelancer ? 'browse-projects.php' : 'browse-freelancers.php'; ?>" method="get" class="hero-search" style="display: flex; box-shadow: 0 10px 25px rgba(0,0,0,0.1); border-radius: 0.75rem; overflow: hidden; background: white;">
          <input type="text" name="search" id="searchInput" class="search-input" style="border: none; padding: 1.25rem; flex: 1; outline: none;"
            placeholder="<?php echo $isFreelancer ? "Search for projects, categories, or skills..." : "Search for skills, services, or freelancers..."; ?>" />
          <button type="submit" class="btn btn-primary search-btn" style="border-radius: 0; padding: 0 2.5rem; border: none; cursor: pointer;">Search</button>
        </form>
        <div class="popular-searches">
          <span class="popular-label">Popular Searches:</span>
          <div class="popular-tags">
            <button class="popular-tag">UI/UX Designer</button>
            <button class="popular-tag">Web Developer</button>
            <button class="popular-tag">Video Editor</button>
          </div>
        </div>
        <div class="hero-cta-buttons">
          <a href="signup.php" class="btn btn-primary btn-lg">Get started</a>
          <a href="<?php echo $isFreelancer ? 'browse-projects.php' : 'browse-freelancers.php'; ?>" class="btn btn-outline-secondary btn-lg" style="border: 2px solid var(--blue-600); color: var(--blue-600);">
            <?php echo $isFreelancer ? "Browse Projects" : "Browse Freelancers"; ?>
          </a>
        </div>
      </div>
      <div class="hero-image-content">
        <div class="hero-image-placeholder" style="background: transparent; box-shadow: none;">
          <div class="hero-person" style="position: relative; width: 100%; height: 100%;">
            <?php if ($isFreelancer): ?>
              <img id="heroImage" class="hero-img-base" src="images/Adobe Express - file (1).png" style="z-index: 1; opacity: 0.5;">
              <img id="heroImageOver" class="hero-img-over" src="images/Adobe Express - file.png" style="z-index: 2; transform: scale(1.1); filter: drop-shadow(0 20px 50px rgba(0,0,0,0.15));">
            <?php else: ?>
              <img id="heroImage" class="hero-img-base" src="images/Adobe Express - file (1).png" style="z-index: 2; transform: translateY(-10px); filter: drop-shadow(0 20px 50px rgba(0,0,0,0.2));">
              <img id="heroImageOver" class="hero-img-over" src="images/Adobe Express - file.png" style="z-index: 1; opacity: 0.3; transform: scale(0.9);">
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Our Trusted Clients Section -->
<div class="section section-white">
  <div class="container">
    <h2 class="section-title">Our trusted clients</h2>
    <div class="trusted-clients-container">
      <?php
      $clients = [
        ['name' => 'Fariha Malon', 'img' => 'testi-1.jpg'],
        ['name' => 'Kashif Khan', 'img' => 'testi-2.jpg'],
        ['name' => 'Sarah Ahmed', 'img' => 'testi-3.jpg'],
        ['name' => 'John Smith', 'img' => 'testi-4.jpg'],
        ['name' => 'Emily Davis', 'img' => 'testi-5.jpg'],
        ['name' => 'Michael Brown', 'img' => 'testi-6.jpg'],
      ];
      foreach ($clients as $client):
      ?>
        <div class="client-item">
          <div class="client-avatar" style="overflow: hidden; border: 2px solid var(--blue-600);">
            <img src="images/testis/<?php echo $client['img']; ?>" alt="<?php echo $client['name']; ?>"
              style="width: 100%; height: 100%; object-fit: cover;">
          </div>
          <div class="client-content">
            <h3 class="client-name"><?php echo $client['name']; ?></h3>
            <p class="client-description">Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor
              incididunt ut labore.</p>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</div>

<?php include 'includes/footer.php'; ?>
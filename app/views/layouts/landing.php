<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="Rebirth - A compassionate platform supporting addiction recovery through progress tracking, evidence-based resources, and community connection.">
  <title><?= htmlspecialchars($pageTitle) ?></title>
  <link rel="icon" type="image/png" href="/logo.png">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <link rel="stylesheet" href="/css/styles.css">
</head>
<body>

  <header class="header" id="header">
    <nav class="nav" aria-label="Main navigation">
      <a href="/" class="nav-logo">
        <img src="/logo.png" alt="Rebirth" width="40" height="40">
      </a>
      <ul class="nav-links" id="navLinks" role="menubar">
        <li role="none"><a href="#hero" role="menuitem" class="active">Home</a></li>
        <li role="none"><a href="#features" role="menuitem">Features</a></li>
        <li role="none"><a href="#how-it-works" role="menuitem">How It Works</a></li>
        <li role="none"><a href="#testimonials" role="menuitem">Resources</a></li>
        <li role="none"><a href="#community" role="menuitem">Community</a></li>
      </ul>
      <div class="nav-cta">
        <a href="/login" class="topbar-avatar" style="width:36px;height:36px;font-size:14px;display:inline-flex;align-items:center;justify-content:center;border-radius:50%;background:var(--color-accent);color:var(--color-primary-dark);font-weight:600;text-decoration:none;" aria-label="Profile">
          <i class="fa-regular fa-user"></i>
        </a>
        <button class="nav-toggle" id="navToggle" aria-label="Toggle navigation menu" aria-expanded="false">
          <span></span><span></span><span></span>
        </button>
      </div>
    </nav>
  </header>

  <?= $content ?>

  <footer class="footer">
    <img src="/assets/images/footer.png" alt="" class="footer-bg-image" aria-hidden="true" loading="lazy">
    <div class="footer-grid">
      <div class="footer-brand">
        <div class="footer-logo">
          <img src="/logo.png" alt="Rebirth" width="40" height="40">
        </div>
        <p>A compassionate platform supporting addiction recovery through progress tracking, evidence-based resources, and community connection.</p>
        <div class="footer-contact">
          <div class="footer-contact-item"><i class="fa-solid fa-location-dot"></i> 8819 Ohio St. South Gate, CA 90280</div>
          <div class="footer-contact-item"><i class="fa-regular fa-envelope"></i> Ourstudio@hello.com</div>
          <div class="footer-contact-item"><i class="fa-solid fa-phone"></i> +1 386-688-3295</div>
        </div>
      </div>
      <div>
        <h4>Navigation</h4>
        <div class="footer-links">
          <a href="#hero">Home</a>
          <a href="#features">Features</a>
          <a href="#how-it-works">How it Works</a>
          <a href="#testimonials">Resources</a>
          <a href="#community">For Doctors</a>
        </div>
      </div>
      <div>
        <h4>Legal</h4>
        <div class="footer-links">
          <a href="#">Mention legale</a>
          <a href="#">Confidentalite</a>
          <a href="#">Cookies</a>
          <a href="#">CGU</a>
        </div>
      </div>
      <div class="footer-newsletter">
        <h4>Join a Newsletter</h4>
        <p>Stay informed with the latest resources, tips, and community updates.</p>
        <form class="footer-newsletter-form">
          <input type="email" placeholder="Enter Your Email" aria-label="Email address" required>
          <button type="submit"><i class="fa-regular fa-paper-plane"></i></button>
        </form>
      </div>
    </div>
    <div class="footer-bottom">
      <span>&copy; Copyright Satyam Studio</span>
      <span>All rights reserved</span>
    </div>
  </footer>

</body>
</html>

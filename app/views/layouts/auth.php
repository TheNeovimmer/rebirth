<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($pageTitle) ?></title>
  <link rel="icon" type="image/png" href="/logo.png">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <link rel="stylesheet" href="/css/styles.css">
  <link rel="stylesheet" href="/css/app.css">
</head>
<body>

  <header class="header" id="header">
    <nav class="nav" aria-label="Main navigation">
      <a href="/" class="nav-logo">
        <img src="/logo.png" alt="Rebirth" width="40" height="40">
      </a>
      <ul class="nav-links" id="navLinks" role="menubar">
        <li role="none"><a href="/#hero" role="menuitem">Home</a></li>
        <li role="none"><a href="/#features" role="menuitem">Features</a></li>
        <li role="none"><a href="/#how-it-works" role="menuitem">How It Works</a></li>
        <li role="none"><a href="/#testimonials" role="menuitem">Resources</a></li>
        <li role="none"><a href="/#community" role="menuitem">Community</a></li>
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

  <main class="auth-main">
    <div class="auth-bg-circle" aria-hidden="true"></div>
    <div class="auth-bg-circle-2" aria-hidden="true"></div>
    <?= $content ?>
  </main>

  <script>
    var header = document.getElementById('header');
    var navToggle = document.getElementById('navToggle');
    var navLinks = document.getElementById('navLinks');
    navToggle.addEventListener('click', function () {
      var isOpen = navLinks.classList.toggle('open');
      navToggle.classList.toggle('active');
      navToggle.setAttribute('aria-expanded', isOpen);
    });
    navLinks.querySelectorAll('a').forEach(function (a) {
      a.addEventListener('click', function () {
        navLinks.classList.remove('open');
        navToggle.classList.remove('active');
        navToggle.setAttribute('aria-expanded', 'false');
      });
    });
    window.addEventListener('scroll', function () {
      header.classList.toggle('scrolled', window.pageYOffset > 50);
    }, { passive: true });
  </script>
  <script src="/js/auth.js"></script>
</body>
</html>

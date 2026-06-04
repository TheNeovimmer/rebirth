<div class="auth-wrap">
  <div class="auth-card">
    <div class="auth-card-header">
      <div class="auth-logo"><img src="/logo.png" alt="Rebirth"></div>
      <div class="auth-tag"><i class="fa-solid fa-heart"></i> Safe & confidential</div>
      <h1 class="auth-title">Welcome Back</h1>
      <p class="auth-subtitle">Sign in to continue your journey</p>
    </div>

    <?php if (isset($_GET['error'])): ?>
    <div class="alert alert-error"><?= htmlspecialchars($_GET['error']) ?></div>
    <?php endif; ?>
    <?php if (isset($_GET['success'])): ?>
    <div class="alert alert-success"><?= htmlspecialchars($_GET['success']) ?></div>
    <?php endif; ?>

    <form action="/login" method="POST" id="loginForm">
      <input type="hidden" name="_token" value="<?= $_token ?>">
      <div class="auth-field">
        <label class="auth-label" for="loginEmail">Email</label>
        <div class="auth-input-wrap">
          <i class="fa-solid fa-envelope auth-input-icon"></i>
          <input class="auth-input" id="loginEmail" name="email" type="email" placeholder="your@email.com" required>
        </div>
      </div>
      <div class="auth-field">
        <label class="auth-label" for="loginPassword">Password</label>
        <div class="auth-input-wrap">
          <i class="fa-solid fa-lock auth-input-icon"></i>
          <input class="auth-input" id="loginPassword" name="password" type="password" placeholder="Enter your password" required>
        </div>
      </div>
      <div class="auth-row">
        <label class="auth-checkbox"><input type="checkbox" name="remember"> Remember me</label>
        <a href="#" class="auth-link">Forgot password?</a>
      </div>
      <button type="submit" class="btn btn-primary btn-block">Sign In</button>
    </form>

    <div class="auth-divider">New to Rebirth?</div>
    <a href="/signup" class="btn btn-outline btn-block">Create Account</a>

    <p class="auth-footer">
      By continuing, you agree to our <a href="#">Terms</a> and <a href="#">Privacy Policy</a>.
    </p>
  </div>
</div>

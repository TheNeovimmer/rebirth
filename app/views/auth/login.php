<div class="auth-page">
  <div class="auth-card">
    <div class="auth-logo"><img src="/logo.png" alt="Rebirth"></div>
    <h1 class="auth-title">Welcome Back</h1>
    <p class="auth-subtitle">Sign in to continue your journey</p>

    <?php if (isset($_GET['error'])): ?>
    <div class="alert alert-error" style="margin-bottom:20px;"><?= htmlspecialchars($_GET['error']) ?></div>
    <?php endif; ?>
    <?php if (isset($_GET['success'])): ?>
    <div class="alert alert-success" style="margin-bottom:20px;"><?= htmlspecialchars($_GET['success']) ?></div>
    <?php endif; ?>

    <form action="/login" method="POST" id="loginForm">
      <input type="hidden" name="_token" value="<?= $_token ?>">
      <div class="form-group">
        <label class="form-label" for="loginEmail">Email</label>
        <input class="form-input" id="loginEmail" name="email" type="email" placeholder="your@email.com" required>
      </div>
      <div class="form-group">
        <label class="form-label" for="loginPassword">Password</label>
        <input class="form-input" id="loginPassword" name="password" type="password" placeholder="Enter your password" required>
      </div>
      <div class="form-row">
        <label class="form-checkbox"><input type="checkbox" name="remember"> Remember me</label>
        <a href="#" class="form-link">Forgot password?</a>
      </div>
      <button type="submit" class="btn btn-primary btn-block">Sign In</button>
    </form>

    <div class="auth-divider">New to Rebirth?</div>
    <a href="/signup" class="btn btn-outline btn-block">Create Account</a>

    <div class="auth-footer">
      By continuing, you agree to our <a href="#">Terms</a> and <a href="#">Privacy Policy</a>.
    </div>
  </div>
</div>

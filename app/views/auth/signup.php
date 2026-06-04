<div class="auth-page">
  <div class="auth-card">
    <div class="auth-logo"><img src="/logo.png" alt="Rebirth"></div>
    <h1 class="auth-title">Create Account</h1>
    <p class="auth-subtitle">Start your recovery journey today</p>

    <?php if (isset($_GET['error'])): ?>
    <div class="alert alert-error" style="margin-bottom:20px;"><?= htmlspecialchars($_GET['error']) ?></div>
    <?php endif; ?>

    <form action="/signup" method="POST" id="signupForm">
      <input type="hidden" name="_token" value="<?= $_token ?>">
      <div class="form-group">
        <label class="form-label" for="signupName">Full Name</label>
        <input class="form-input" id="signupName" name="name" type="text" placeholder="Your name" required>
      </div>
      <div class="form-group">
        <label class="form-label" for="signupEmail">Email</label>
        <input class="form-input" id="signupEmail" name="email" type="email" placeholder="your@email.com" required>
      </div>
      <div class="form-group">
        <label class="form-label" for="signupPassword">Password</label>
        <input class="form-input" id="signupPassword" name="password" type="password" placeholder="Create a strong password" required>
      </div>
      <button type="submit" class="btn btn-primary btn-block">Create Account</button>
    </form>

    <div class="auth-divider">Already have an account?</div>
    <a href="/login" class="btn btn-outline btn-block">Sign In</a>

    <div class="auth-footer">
      By continuing, you agree to our <a href="#">Terms</a> and <a href="#">Privacy Policy</a>.
    </div>
  </div>
</div>

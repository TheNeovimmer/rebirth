<div class="auth-wrap">
  <div class="auth-card">
    <div class="auth-card-header">
      <div class="auth-logo"><img src="/logo.png" alt="Rebirth"></div>
      <div class="auth-tag"><i class="fa-solid fa-heart"></i> Safe & confidential</div>
      <h1 class="auth-title">Create Account</h1>
      <p class="auth-subtitle">Start your recovery journey today</p>
    </div>

    <?php if (isset($_GET['error'])): ?>
    <div class="alert alert-error"><?= htmlspecialchars($_GET['error']) ?></div>
    <?php endif; ?>

    <form action="/signup" method="POST" id="signupForm">
      <input type="hidden" name="_token" value="<?= $_token ?>">
      <div class="auth-field">
        <label class="auth-label" for="signupName">Full Name</label>
        <div class="auth-input-wrap">
          <i class="fa-solid fa-user auth-input-icon"></i>
          <input class="auth-input" id="signupName" name="name" type="text" placeholder="Your name" required>
        </div>
      </div>
      <div class="auth-field">
        <label class="auth-label" for="signupEmail">Email</label>
        <div class="auth-input-wrap">
          <i class="fa-solid fa-envelope auth-input-icon"></i>
          <input class="auth-input" id="signupEmail" name="email" type="email" placeholder="your@email.com" required>
        </div>
      </div>
      <div class="auth-field">
        <label class="auth-label" for="signupPassword">Password</label>
        <div class="auth-input-wrap">
          <i class="fa-solid fa-lock auth-input-icon"></i>
          <input class="auth-input" id="signupPassword" name="password" type="password" placeholder="Create a strong password" required>
        </div>
      </div>
      <button type="submit" class="btn btn-primary btn-block">Create Account</button>
    </form>

    <div class="auth-divider">Already have an account?</div>
    <a href="/login" class="btn btn-outline btn-block">Sign In</a>

    <p class="auth-footer">
      By continuing, you agree to our <a href="#">Terms</a> and <a href="#">Privacy Policy</a>.
    </p>
  </div>
</div>

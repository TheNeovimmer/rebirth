<div class="auth-card">
  <div class="auth-logo"><img src="/logo.png" alt="Rebirth"></div>
  <h1 class="auth-title">Begin Your Journey</h1>
  <p class="auth-subtitle">Create your profile to get started</p>
  <?php if ($error): ?>
  <div class="alert alert-error" style="background:rgba(209,69,59,0.08);color:var(--color-danger);padding:10px 14px;border-radius:10px;margin-bottom:16px;font-size:14px;text-align:center;"><?= htmlspecialchars($error) ?></div>
  <?php endif; ?>
  <form action="/signup" method="POST">
    <input type="hidden" name="_token" value="<?= $_token ?>">
    <div class="form-group">
      <label class="form-label" for="name">Full Name</label>
      <input class="form-input" id="name" name="name" type="text" placeholder="Your name" required>
    </div>
    <div class="form-group">
      <label class="form-label" for="signup-email">Email</label>
      <input class="form-input" id="signup-email" name="email" type="email" placeholder="you@example.com" required>
    </div>
    <div class="form-group">
      <label class="form-label" for="signup-password">Password</label>
      <input class="form-input" id="signup-password" name="password" type="password" placeholder="Create a password" required>
    </div>
    <div class="form-group">
      <label class="form-label" for="stage">Recovery Stage</label>
      <select class="form-select" id="stage" name="stage">
        <option value="Onboarding">Just starting out</option>
        <option value="Active">In early recovery</option>
        <option value="Maintenance">In sustained recovery</option>
        <option value="Alumni">Supporting a loved one</option>
      </select>
    </div>
    <button type="submit" class="btn btn-primary btn-block">Create Account</button>
  </form>
  <div class="auth-footer">Already have an account? <a href="/login">Sign in</a></div>
</div>

<div class="auth-card">
  <div class="auth-logo"><img src="/logo.png" alt="Rebirth"></div>
  <h1 class="auth-title">Welcome Back</h1>
  <p class="auth-subtitle">Sign in to continue your journey</p>
  <?php if ($error): ?>
  <div class="alert alert-error" style="background:rgba(209,69,59,0.08);color:var(--color-danger);padding:10px 14px;border-radius:10px;margin-bottom:16px;font-size:14px;text-align:center;"><?= htmlspecialchars($error) ?></div>
  <?php endif; ?>
  <form action="/login" method="POST">
    <input type="hidden" name="_token" value="<?= $_token ?>">
    <div class="form-group">
      <label class="form-label" for="email">Email</label>
      <input class="form-input" id="email" name="email" type="email" placeholder="you@example.com" required>
    </div>
    <div class="form-group">
      <label class="form-label" for="password">Password</label>
      <input class="form-input" id="password" name="password" type="password" placeholder="Enter your password" required>
    </div>
    <button type="submit" class="btn btn-primary btn-block">Sign In</button>
  </form>
  <div class="auth-footer">Don't have an account? <a href="/signup">Create one</a></div>
</div>

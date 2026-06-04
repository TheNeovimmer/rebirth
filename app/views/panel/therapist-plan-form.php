<?php if (isset($_GET['error'])): ?>
<div class="alert" style="background:rgba(209,69,59,0.08);color:var(--color-danger);padding:12px 20px;border-radius:var(--radius-sm);margin-bottom:16px;"><?= htmlspecialchars($_GET['error']) ?></div>
<?php endif; ?>

<a href="/therapist/patient/<?= $patient['id'] ?>/plans" class="btn btn-outline" style="margin-bottom:12px;"><i class="fa-solid fa-arrow-left"></i> Back to Plans</a>

<div class="card">
  <h2><?= $plan ? 'Edit' : 'Create' ?> Treatment Plan — <?= htmlspecialchars($patient['name']) ?></h2>
</div>

<div class="card">
  <form action="/therapist/patient/<?= $patient['id'] ?>/plans/<?= $plan ? 'update' : 'create' ?>" method="POST">
    <input type="hidden" name="_token" value="<?= $_token ?>">
    <?php if ($plan): ?>
    <input type="hidden" name="plan_id" value="<?= $plan['id'] ?>">
    <?php endif; ?>

    <div class="form-group">
      <label class="form-label">Plan Title</label>
      <input class="form-input" name="title" value="<?= htmlspecialchars($plan['title'] ?? '') ?>" placeholder="e.g., 90-Day Recovery Plan" required>
    </div>

    <div class="form-group">
      <label class="form-label">Recovery Goals</label>
      <textarea class="form-input" name="goals" rows="3" placeholder="What are the main recovery goals?"><?= htmlspecialchars($plan['goals'] ?? '') ?></textarea>
    </div>

    <div class="form-group">
      <label class="form-label">Treatment Objectives</label>
      <textarea class="form-input" name="objectives" rows="3" placeholder="Specific objectives to achieve the goals"><?= htmlspecialchars($plan['objectives'] ?? '') ?></textarea>
    </div>

    <div class="form-group">
      <label class="form-label">Assigned Activities</label>
      <textarea class="form-input" name="activities" rows="3" placeholder="Activities the patient should complete"><?= htmlspecialchars($plan['activities'] ?? '') ?></textarea>
    </div>

    <div class="form-group">
      <label class="form-label">Coping Strategies</label>
      <textarea class="form-input" name="coping_strategies" rows="3" placeholder="Strategies to prevent relapse"><?= htmlspecialchars($plan['coping_strategies'] ?? '') ?></textarea>
    </div>

    <div class="form-group">
      <label class="form-label">Therapist Recommendations</label>
      <textarea class="form-input" name="recommendations" rows="3" placeholder="Additional recommendations"><?= htmlspecialchars($plan['recommendations'] ?? '') ?></textarea>
    </div>

    <?php if ($plan): ?>
    <div class="form-group">
      <label class="form-label">Status</label>
      <select class="form-select" name="status">
        <option value="active" <?= $plan['status'] === 'active' ? 'selected' : '' ?>>Active</option>
        <option value="completed" <?= $plan['status'] === 'completed' ? 'selected' : '' ?>>Completed</option>
        <option value="archived" <?= $plan['status'] === 'archived' ? 'selected' : '' ?>>Archived</option>
      </select>
    </div>
    <?php endif; ?>

    <button type="submit" class="btn btn-primary btn-block"><?= $plan ? 'Update Plan' : 'Create Plan' ?></button>
  </form>
</div>

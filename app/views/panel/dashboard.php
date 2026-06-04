<?php
$checkedInToday = $todayCheckin !== null;
$checkinTime = $checkedInToday ? date('g:i A', strtotime($todayCheckin['created_at'])) : '';
$moodEmojis = ['great' => '😊', 'good' => '🙂', 'neutral' => '😐', 'difficult' => '😔', 'struggling' => '😢'];
$journals = array_slice($recentJournals, 0, 3);
$resources = array_slice($recentResources, 0, 3);
?>

<div class="stats-row">
  <div class="stat-card">
    <div class="stat-icon green"><i class="fa-regular fa-calendar-check"></i></div>
    <div>
      <div class="stat-value"><?= $totalCheckins ?></div>
      <div class="stat-label">Total Check-ins</div>
    </div>
  </div>
  <div class="stat-card">
    <div class="stat-icon blue"><i class="fa-regular fa-envelope"></i></div>
    <div>
      <div class="stat-value"><?= $unreadCount ?></div>
      <div class="stat-label">Unread Messages</div>
    </div>
  </div>
</div>

<div class="card">
  <div class="section-header">
    <h2>Today's Overview</h2>
  </div>
  <div class="data-row">
    <div class="data-row-icon green"><i class="fa-regular fa-face-smile"></i></div>
    <div class="data-row-body">
      <?php if ($checkedInToday): ?>
      <strong style="color:var(--color-success);">Checked In</strong>
      <span>Today at <?= $checkinTime ?> &middot; Mood: <?= ucfirst($todayCheckin['mood']) ?></span>
      <?php else: ?>
      <strong>Not checked in yet</strong>
      <span>Start your daily check-in</span>
      <?php endif; ?>
    </div>
    <?php if (!$checkedInToday): ?>
    <a href="/panel/checkin" class="btn btn-primary btn-sm">Check In Now</a>
    <?php endif; ?>
  </div>
  <div class="data-row">
    <div class="data-row-icon blue"><i class="fa-regular fa-envelope"></i></div>
    <div class="data-row-body">
      <?php if ($unreadCount > 0): ?>
      <strong><?= $unreadCount ?> unread messages</strong>
      <span>From your therapist</span>
      <?php else: ?>
      <strong>No unread messages</strong>
      <span>All caught up!</span>
      <?php endif; ?>
    </div>
    <a href="/panel/messages" class="btn btn-ghost btn-sm"><i class="fa-solid fa-arrow-right"></i></a>
  </div>
</div>

<div class="grid-2">
  <div class="card">
    <div class="section-header">
      <h2>Recent Journal</h2>
      <a href="/panel/journal" class="btn btn-ghost btn-xs">View all</a>
    </div>
    <?php if (empty($journals)): ?>
    <div class="empty-state" style="padding:20px;">
      <i class="fa-regular fa-book"></i>
      <p>No journal entries yet.</p>
      <a href="/panel/journal" class="btn btn-primary btn-sm">Write Your First Entry</a>
    </div>
    <?php else: ?>
    <?php foreach ($journals as $entry): ?>
    <div class="data-row">
      <span style="font-size:20px;"><?= $moodEmojis[$entry['mood']] ?? '😐' ?></span>
      <div class="data-row-body">
        <strong><?= date('M j, g:i A', strtotime($entry['created_at'])) ?></strong>
        <span><?= htmlspecialchars(substr($entry['content'], 0, 80)) ?><?= strlen($entry['content']) > 80 ? '...' : '' ?></span>
      </div>
    </div>
    <?php endforeach; ?>
    <?php endif; ?>
  </div>

  <div class="card">
    <div class="section-header">
      <h2>Resources</h2>
      <a href="/panel/resources" class="btn btn-ghost btn-xs">View all</a>
    </div>
    <?php if (empty($resources)): ?>
    <div class="empty-state" style="padding:20px;">
      <i class="fa-regular fa-file-lines"></i>
      <p>No resources available yet.</p>
    </div>
    <?php else: ?>
    <?php foreach ($resources as $resource): ?>
    <div class="data-row">
      <div class="data-row-body">
        <strong><?= htmlspecialchars($resource['title']) ?></strong>
        <span><?= htmlspecialchars($resource['type']) ?></span>
      </div>
    </div>
    <?php endforeach; ?>
    <?php endif; ?>
  </div>
</div>

<div class="card" style="padding-bottom:16px;">
  <h2>Quick Actions</h2>
  <div class="quick-actions">
    <a href="/panel/checkin" class="btn"><i class="fa-regular fa-face-smile"></i> Check In</a>
    <a href="/panel/journal" class="btn"><i class="fa-solid fa-book"></i> Journal</a>
    <a href="/panel/community" class="btn"><i class="fa-regular fa-comments"></i> Community</a>
    <a href="/panel/messages" class="btn"><i class="fa-regular fa-comment-dots"></i> Messages</a>
    <a href="/panel/resources" class="btn"><i class="fa-regular fa-file-lines"></i> Resources</a>
    <a href="/panel/sos" class="btn danger"><i class="fa-solid fa-triangle-exclamation"></i> SOS</a>
  </div>
</div>

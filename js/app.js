// Chat polling
document.querySelectorAll('#messageContainer').forEach(container => {
  const convId = container.dataset.conversationId;
  if (!convId) return;
  setInterval(async () => {
    const lastId = container.dataset.lastId || '0';
    try {
      const resp = await fetch(`/messages/poll?conversation_id=${convId}&after=${lastId}`);
      const msgs = await resp.json();
      if (msgs.length > 0) {
        const userId = document.body.dataset.userId;
        msgs.forEach(msg => {
          const div = document.createElement('div');
          div.className = 'chat-msg ' + (parseInt(msg.sender_id) === parseInt(userId) ? 'chat-msg-sent' : 'chat-msg-received');
          div.innerHTML = `<div class="chat-msg-content">${escapeHtml(msg.content)}</div><div class="chat-msg-time">${new Date(msg.created_at).toLocaleTimeString([], {hour:'2-digit',minute:'2-digit'})}</div>`;
          container.appendChild(div);
          container.dataset.lastId = msg.id;
        });
        container.scrollTop = container.scrollHeight;
      }
    } catch(e) { /* silent */ }
  }, 3000);
  container.scrollTop = container.scrollHeight;
});

// Availability status (patient side)
const availabilityEl = document.getElementById('availabilityStatus');
if (availabilityEl && window.__therapistId) {
  fetch(`/therapist/availability/check?therapist_id=${window.__therapistId}`)
    .then(r => r.json())
    .then(data => {
      availabilityEl.textContent = data.available ? '🟢 Online now' : '⚫ Currently offline';
      availabilityEl.style.color = data.available ? 'var(--color-success)' : 'var(--color-text-muted)';
    })
    .catch(() => { availabilityEl.textContent = '⚫ Offline'; });
}

// SOS badge polling
const sosBadge = document.getElementById('sosBadge');
if (sosBadge) {
  async function updateSosBadge() {
    try {
      const resp = await fetch('/therapist/sos/count');
      const data = await resp.json();
      if (data.count > 0) {
        sosBadge.textContent = data.count;
        sosBadge.classList.add('show');
      } else {
        sosBadge.classList.remove('show');
      }
    } catch(e) { /* silent */ }
  }
  updateSosBadge();
  setInterval(updateSosBadge, 10000);
}

function escapeHtml(str) {
  const div = document.createElement('div');
  div.textContent = str;
  return div.innerHTML;
}

// Notification system
const notifBell = document.getElementById('notifBell');
const notifDot = document.getElementById('notifDot');
const notifDropdown = document.getElementById('notifDropdown');
const notifList = document.getElementById('notifList');
const markAllBtn = document.getElementById('markAllRead');

if (notifBell) {
  async function fetchNotifCount() {
    try {
      const resp = await fetch('/notifications/count');
      const data = await resp.json();
      if (data.count > 0) {
        notifDot.classList.add('show');
        notifDot.textContent = data.count > 9 ? '9+' : data.count;
        notifDot.style.cssText = 'display:flex;align-items:center;justify-content:center;font-size:8px;font-weight:700;color:white;width:16px;height:16px;top:4px;right:2px;';
      } else {
        notifDot.classList.remove('show');
      }
    } catch(e) { /* silent */ }
  }

  async function fetchNotifList() {
    try {
      const resp = await fetch('/notifications/list');
      const list = await resp.json();
      notifList.innerHTML = '';
      if (list.length === 0) {
        notifList.innerHTML = '<div class="notif-empty">No notifications</div>';
      } else {
        list.forEach(n => {
          const item = document.createElement('a');
          item.href = n.link || '#';
          item.className = 'notif-item' + (n.read_at ? '' : ' unread');
          item.dataset.id = n.id;
          const icons = { message:'fa-regular fa-comment-dots', sos:'fa-solid fa-triangle-exclamation', appointment:'fa-regular fa-calendar', resource:'fa-solid fa-file', relapse:'fa-solid fa-heart-crack', treatment_plan:'fa-solid fa-clipboard-list' };
          const icon = icons[n.type] || 'fa-regular fa-bell';
          const colors = { message:'rgba(0,122,255,0.1);color:#007aff', sos:'rgba(209,69,59,0.1);color:var(--color-danger)', appointment:'rgba(180,191,115,0.15);color:var(--color-primary-dark)', resource:'rgba(120,80,200,0.1);color:#7850c8', relapse:'rgba(232,168,56,0.12);color:var(--color-warning)', treatment_plan:'rgba(76,175,125,0.1);color:var(--color-success)' };
          const color = colors[n.type] || 'var(--color-bg);color:var(--color-text-muted)';
          const timeAgo = (() => {
            const diff = (Date.now() - new Date(n.created_at).getTime()) / 1000;
            if (diff < 60) return 'Just now';
            if (diff < 3600) return Math.floor(diff/60) + 'm';
            if (diff < 86400) return Math.floor(diff/3600) + 'h';
            return Math.floor(diff/86400) + 'd';
          })();
          item.innerHTML = `
            <div class="notif-item-icon" style="background:${color}"><i class="${icon}"></i></div>
            <div class="notif-item-content">
              <div class="notif-item-title">${escapeHtml(n.title)}</div>
              ${n.body ? '<div class="notif-item-body">' + escapeHtml(n.body) + '</div>' : ''}
            </div>
            <div class="notif-item-time">${timeAgo}</div>
          `;
          if (!n.read_at) {
            item.addEventListener('click', async function(e) {
              try { await fetch('/notifications/read', { method:'POST', headers:{'Content-Type':'application/x-www-form-urlencoded'}, body:'id=' + this.dataset.id }); } catch(e) {}
            });
          }
          notifList.appendChild(item);
        });
      }
    } catch(e) { /* silent */ }
  }

  notifBell.addEventListener('click', function(e) {
    e.stopPropagation();
    const isOpen = notifDropdown.style.display !== 'none';
    notifDropdown.style.display = isOpen ? 'none' : 'block';
    if (!isOpen) fetchNotifList();
  });

  document.addEventListener('click', function(e) {
    if (!e.target.closest('.notif-wrap')) {
      notifDropdown.style.display = 'none';
    }
  });

  if (markAllBtn) {
    markAllBtn.addEventListener('click', async function() {
      try {
        await fetch('/notifications/read-all', { method:'POST' });
        notifDot.classList.remove('show');
        notifDropdown.style.display = 'none';
      } catch(e) {}
    });
  }

  fetchNotifCount();
  setInterval(fetchNotifCount, 10000);
}

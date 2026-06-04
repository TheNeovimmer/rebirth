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

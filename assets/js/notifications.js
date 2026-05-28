/**
 * notifications.js
 * Polls the API for unread notifications and updates the bell UI.
 */

'use strict';

const NotificationCenter = (() => {
  const POLL_INTERVAL = 30000; // 30 seconds
  let pollTimer       = null;

  async function fetchNotifications() {
    try {
      const res  = await fetch('/newssite/api/get_notifications.php', {
        credentials: 'same-origin',
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
      });

      if (!res.ok) return;

      const data = await res.json();
      updateBell(data.count, data.notifications);

    } catch (err) {
      console.warn('[Notifications] Fetch failed:', err);
    }
  }

  function updateBell(count, notifications) {
    const badge   = document.getElementById('notif-badge');
    const dropdown = document.getElementById('notif-dropdown');

    if (!badge || !dropdown) return;

    // Update badge
    badge.textContent = count > 99 ? '99+' : String(count);
    badge.style.display = count > 0 ? 'flex' : 'none';

    // Render dropdown list
    if (notifications.length === 0) {
      dropdown.innerHTML = '<p class="notif-empty">You\'re all caught up! 🎉</p>';
      return;
    }

    dropdown.innerHTML = notifications.map(n => `
      <a class="notif-item" href="/newssite/article.php?id=${n.article_id}">
        <span class="notif-dot"></span>
        <div class="notif-text">
          <strong>${n.headline}</strong>
          <small>${n.message}</small>
        </div>
      </a>
    `).join('');
  }

  async function markAllRead() {
    try {
      await fetch('/newssite/api/mark_notifications_read.php', {
        method: 'POST',
        credentials: 'same-origin',
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
      });

      const badge    = document.getElementById('notif-badge');
      const dropdown = document.getElementById('notif-dropdown');
      if (badge)    badge.style.display = 'none';
      if (dropdown) dropdown.innerHTML  = '<p class="notif-empty">You\'re all caught up! 🎉</p>';

    } catch (err) {
      console.warn('[Notifications] Mark read failed:', err);
    }
  }

  function init() {
    // Only run if user is logged in (bell element exists)
    const bell = document.getElementById('notif-bell');
    if (!bell) return;

    // Initial fetch on load
    fetchNotifications();

    // Poll every 30s
    pollTimer = setInterval(fetchNotifications, POLL_INTERVAL);

    // Toggle dropdown on bell click + mark as read
    bell.addEventListener('click', async (e) => {
      e.stopPropagation();
      const dropdown = document.getElementById('notif-dropdown');
      if (!dropdown) return;

      const isOpen = dropdown.classList.toggle('open');
      if (isOpen) await markAllRead();
    });

    // Close dropdown when clicking outside
    document.addEventListener('click', () => {
      document.getElementById('notif-dropdown')?.classList.remove('open');
    });
  }

  return { init, fetchNotifications };
})();

document.addEventListener('DOMContentLoaded', () => NotificationCenter.init());
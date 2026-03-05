/**
 * notifications.js — Polling AJAX des notifications (fonctionnalité bonus)
 * Emplacement : /public/js/notifications.js
 *
 * Ce fichier doit être inclus dans /app/views/layouts/layout.php
 * UNIQUEMENT si l'utilisateur est connecté :
 *
 *   <?php if (isset($_SESSION['user_id'])): ?>
 *     <script src="public/js/notifications.js"></script>
 *   <?php endif; ?>
 *
 * Il s'appuie sur :
 *  - Un badge HTML #notif-count  dans la navbar (compteur de non-lus)
 *  - Un dropdown #notif-list     dans la navbar (liste des notifications)
 *  - Un bouton  #notif-mark-all  pour tout marquer comme lu
 *  - Un container #toast-container pour les toasts Bootstrap
 */

(function () {
  'use strict';

  const POLL_INTERVAL = 10000; // 10 secondes
  let   lastCount     = -1;
  const BASE = (window.APP_BASE_URL || '') + 'index.php';

  // ──────────────────────────────────────────────
  // Éléments DOM
  // ──────────────────────────────────────────────
  const badge    = document.getElementById('notif-count');
  const list     = document.getElementById('notif-list');
  const markAll  = document.getElementById('notif-mark-all');

  if (!badge || !list) return; // Ne rien faire si les éléments n'existent pas

  // ──────────────────────────────────────────────
  // Polling : appel toutes les POLL_INTERVAL ms
  // ──────────────────────────────────────────────
  function fetchNotifications() {
    fetch(BASE + '?action=getNotifications', { credentials: 'same-origin' })
      .then(res => res.json())
      .then(data => {
        if (!data.success) return;

        const unread = data.unread;

        // Mettre à jour le badge
        badge.textContent = unread > 0 ? (unread > 99 ? '99+' : unread) : '';
        badge.style.display = unread > 0 ? 'inline-block' : 'none';

        // Afficher un toast si de nouvelles notifications sont arrivées
        if (unread > lastCount && lastCount >= 0) {
          showToast('Nouvelle notification', `Vous avez ${unread} notification(s) non lue(s).`, 'info');
        }
        lastCount = unread;

        // Remplir la liste du dropdown
        renderList(data.notifications);
      })
      .catch(() => {}); // Silencieux si hors ligne
  }

  // ──────────────────────────────────────────────
  // Rendu de la liste dropdown
  // ──────────────────────────────────────────────
  function renderList(notifications) {
    if (!notifications || notifications.length === 0) {
      list.innerHTML = '<li><span class="dropdown-item text-muted">Aucune notification</span></li>';
      return;
    }

    list.innerHTML = notifications.map(n => {
      const date  = new Date(n.created_at).toLocaleDateString('fr-FR', { day: '2-digit', month: 'short', hour: '2-digit', minute: '2-digit' });
      const read  = n.is_read == 1;
      const icon  = iconForType(n.type);
      return `
        <li>
          <a href="#" class="dropdown-item${read ? '' : ' fw-semibold bg-light'} notif-item"
             data-id="${n.id}" data-link="${n.link_id ?? ''}">
            <span class="me-2">${icon}</span>${escHtml(n.message)}
            <br><small class="text-muted fw-normal">${date}</small>
          </a>
        </li>`;
    }).join('') + '<li><hr class="dropdown-divider"></li>';
  }

  // ──────────────────────────────────────────────
  // Icône selon le type de notification
  // ──────────────────────────────────────────────
  function iconForType(type) {
    const icons = {
      new_vote:       '👍',
      new_comment:    '💬',
      new_submission: '🚀',
      badge_earned:   '🏅',
    };
    return icons[type] || '🔔';
  }

  // ──────────────────────────────────────────────
  // Clic sur une notification → marquer comme lue
  // ──────────────────────────────────────────────
  list.addEventListener('click', function (e) {
    const item = e.target.closest('.notif-item');
    if (!item) return;
    e.preventDefault();

    const id = item.dataset.id;
    fetch(BASE + '?action=markNotifRead', {
      method: 'POST',
      credentials: 'same-origin',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: 'id=' + encodeURIComponent(id),
    }).then(() => fetchNotifications());
  });

  // ──────────────────────────────────────────────
  // Bouton "Tout marquer comme lu"
  // ──────────────────────────────────────────────
  if (markAll) {
    markAll.addEventListener('click', function (e) {
      e.preventDefault();
      fetch(BASE + '?action=markAllNotifsRead', {
        method: 'POST',
        credentials: 'same-origin',
      }).then(() => fetchNotifications());
    });
  }

  // ──────────────────────────────────────────────
  // Toast Bootstrap 5
  // ──────────────────────────────────────────────
  function showToast(title, message, type = 'info') {
    let container = document.getElementById('toast-container');
    if (!container) {
      container = document.createElement('div');
      container.id = 'toast-container';
      container.className = 'toast-container position-fixed bottom-0 end-0 p-3';
      container.style.zIndex = 9999;
      document.body.appendChild(container);
    }

    const toastEl = document.createElement('div');
    toastEl.className = `toast align-items-center text-bg-${type} border-0`;
    toastEl.setAttribute('role', 'alert');
    toastEl.innerHTML = `
      <div class="d-flex">
        <div class="toast-body">
          <strong>${escHtml(title)}</strong><br>${escHtml(message)}
        </div>
        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
      </div>`;
    container.appendChild(toastEl);

    // Utilise Bootstrap Toast s'il est disponible
    if (window.bootstrap && window.bootstrap.Toast) {
      new window.bootstrap.Toast(toastEl, { delay: 4000 }).show();
    } else {
      toastEl.style.display = 'block';
      setTimeout(() => toastEl.remove(), 4000);
    }
  }

  // ──────────────────────────────────────────────
  // Utilitaire XSS
  // ──────────────────────────────────────────────
  function escHtml(str) {
    return String(str)
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;');
  }

  // ──────────────────────────────────────────────
  // Démarrage
  // ──────────────────────────────────────────────
  fetchNotifications();
  setInterval(fetchNotifications, POLL_INTERVAL);

})();
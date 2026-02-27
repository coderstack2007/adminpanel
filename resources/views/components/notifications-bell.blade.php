{{-- resources/views/components/notifications-bell.blade.php --}}
{{-- Добавь этот компонент в свой navigation/header --}}

<div class="dropdown" id="notifDropdown">
    <button class="btn btn-link position-relative p-2 text-light"
            id="notifBell"
            data-bs-toggle="dropdown"
            aria-expanded="false"
            style="text-decoration:none">
        <i class="bi bi-bell fs-5"></i>
        <span id="notifBadge"
              class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger"
              style="font-size:0.6rem;display:none">
            0
        </span>
    </button>

    <div class="dropdown-menu dropdown-menu-end p-0 shadow"
         style="width:360px;max-height:480px;overflow-y:auto;background:rgb(31,41,65);border:1px solid rgba(255,255,255,0.1)">

        {{-- Шапка --}}
        <div class="d-flex align-items-center justify-content-between px-3 py-2"
             style="border-bottom:1px solid rgba(255,255,255,0.1)">
            <span class="fw-semibold text-white small">Уведомления</span>
            <button class="btn btn-link btn-sm text-muted p-0" id="markAllRead"
                    style="font-size:0.75rem;text-decoration:none">
                Прочитать все
            </button>
        </div>

        {{-- Список уведомлений --}}
        <div id="notifList">
            <div class="text-center py-4 text-muted small" id="notifEmpty">
                <i class="bi bi-bell-slash d-block fs-3 opacity-25 mb-2"></i>
                Нет уведомлений
            </div>
        </div>

    </div>
</div>

@once
@push('scripts')
<script>
(function() {
    const icons = {
        submitted:           'bi-send text-info',
        hr_edited:           'bi-pencil text-warning',
        supervisor_review:   'bi-hourglass-split text-warning',
        supervisor_approved: 'bi-check-circle text-success',
        supervisor_rejected: 'bi-x-circle text-danger',
        supervisor_on_hold:  'bi-pause-circle text-warning',
        closed:              'bi-lock text-secondary',
        confirmed_closed:    'bi-lock-fill text-dark',
    };

    function loadNotifications() {
        fetch('/notifications')
            .then(r => r.json())
            .then(data => {
                const badge  = document.getElementById('notifBadge');
                const list   = document.getElementById('notifList');
                const empty  = document.getElementById('notifEmpty');
                const count  = data.unread_count;

                // Обновить badge
                if (count > 0) {
                    badge.textContent = count > 99 ? '99+' : count;
                    badge.style.display = '';
                } else {
                    badge.style.display = 'none';
                }

                // Очистить список
                list.innerHTML = '';

                if (!data.notifications.length) {
                    list.appendChild(empty);
                    return;
                }

                data.notifications.forEach(n => {
                    const isUnread = !n.read_at;
                    const iconClass = icons[n.type] || 'bi-bell text-primary';
                    const posName = n.vacancy_request?.position?.name || '';

                    const item = document.createElement('div');
                    item.className = 'notif-item d-flex align-items-start gap-2 px-3 py-2';
                    item.style.cssText = `
                        border-bottom:1px solid rgba(255,255,255,0.07);
                        cursor:pointer;
                        background:${isUnread ? 'rgba(99,102,241,0.08)' : 'transparent'};
                        transition:background .2s;
                    `;
                    item.dataset.id = n.id;
                    item.dataset.requestId = n.vacancy_request_id;

                    item.innerHTML = `
                        <div class="mt-1 flex-shrink-0">
                            <i class="bi ${iconClass} fs-5"></i>
                        </div>
                        <div class="flex-grow-1">
                            <div class="small" style="color:#f9fafb;line-height:1.3">${n.message}</div>
                            ${posName ? `<div class="text-muted" style="font-size:0.7rem">${posName}</div>` : ''}
                            <div class="text-muted" style="font-size:0.68rem">${timeAgo(n.created_at)}</div>
                        </div>
                        ${isUnread ? '<div class="flex-shrink-0 mt-2"><span style="width:7px;height:7px;background:#6366f1;border-radius:50%;display:inline-block"></span></div>' : ''}
                    `;

                    item.addEventListener('click', () => {
                        markRead(n.id);
                        if (n.vacancy_request_id) {
                            // Переход на страницу заявки (адаптируй route под свою роль)
                            window.location.href = `/hr/statements/${n.vacancy_request_id}`;
                        }
                    });

                    list.appendChild(item);
                });
            })
            .catch(console.error);
    }

    function markRead(id) {
        fetch(`/notifications/${id}/read`, { method: 'POST', headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content } })
            .then(() => loadNotifications());
    }

    document.getElementById('markAllRead').addEventListener('click', (e) => {
        e.stopPropagation();
        fetch('/notifications/read-all', { method: 'POST', headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content } })
            .then(() => loadNotifications());
    });

    function timeAgo(dateStr) {
        const diff = Math.floor((Date.now() - new Date(dateStr)) / 1000);
        if (diff < 60)   return 'только что';
        if (diff < 3600) return Math.floor(diff/60) + ' мин. назад';
        if (diff < 86400) return Math.floor(diff/3600) + ' ч. назад';
        return Math.floor(diff/86400) + ' д. назад';
    }

    // Загрузить при открытии dropdown
    document.getElementById('notifBell').addEventListener('click', loadNotifications);

    // Автообновление каждые 30 секунд (только badge)
    function updateBadge() {
        fetch('/notifications')
            .then(r => r.json())
            .then(data => {
                const badge = document.getElementById('notifBadge');
                if (data.unread_count > 0) {
                    badge.textContent = data.unread_count > 99 ? '99+' : data.unread_count;
                    badge.style.display = '';
                } else {
                    badge.style.display = 'none';
                }
            });
    }

    // Первая загрузка badge при открытии страницы
    updateBadge();
    setInterval(updateBadge, 30000);
})();
</script>
@endpush
@endonce
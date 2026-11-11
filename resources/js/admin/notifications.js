function initializeNotifications() {
    const bell = document.getElementById('notification-bell');
    if (!bell) return;

    const labels = JSON.parse(bell.dataset.labels);
    const toggle = document.getElementById('notification-bell-toggle');
    const badge = document.getElementById('notifBadge');
    const recent = document.getElementById('notification-recent');
    const bellStatus = document.getElementById('notification-bell-status');
    const pageStatus = document.getElementById('notification-page-status');
    const inboxPage = document.getElementById('notification-inbox');
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';
    let loading = false;
    let writing = false;
    let expired = false;
    let unreadCount = 0;
    let loadController;
    let loadVersion = 0;

    function element(tag, className, text) {
        const node = document.createElement(tag);
        node.className = className;
        if (text !== undefined && text !== null) node.textContent = text;
        return node;
    }

    function internalUrl(value) {
        const url = new URL(value, window.location.origin);
        if (url.origin !== window.location.origin || !url.pathname.startsWith('/admin/')) {
            throw new Error('Invalid notification destination');
        }
        return url.href;
    }

    function formatTime(value) {
        const date = new Date(value);
        return value && !Number.isNaN(date.getTime())
            ? date.toLocaleString(document.documentElement.lang || undefined)
            : value || '';
    }

    function updateDisabled() {
        document.querySelectorAll('[data-notification-write]').forEach(button => {
            const readAll = button.closest('form')?.dataset.notificationForm === 'read-all';
            button.disabled = writing || expired || (readAll && (loading || unreadCount === 0));
        });
    }

    function setStatus(target, message, state = '') {
        if (!target) return;
        target.replaceChildren(element('span', '', message));
        target.classList.toggle('text-danger', state === 'error');
        if (state === 'expired') {
            const login = element('a', 'd-block mt-2', labels.login);
            login.href = internalUrl(bell.dataset.loginUrl);
            target.append(login);
        }
        if (state === 'error') {
            const retry = element('button', 'btn btn-sm btn-outline-secondary d-block mt-2', labels.retry);
            retry.type = 'button';
            retry.addEventListener('click', () => {
                if (inboxPage) window.location.reload();
                else loadFeed();
            });
            target.append(retry);
        }
    }

    function showError(error, isWrite = false) {
        expired = error.status === 401 || error.status === 419 || error.sessionExpired;
        const denied = error.status === 403 || error.status === 404;
        const message = expired ? labels.expired : denied ? labels.forbidden
            : isWrite ? labels.writeError : labels.networkError;
        recent.replaceChildren();
        badge.classList.add('d-none');
        unreadCount = 0;
        toggle.setAttribute('aria-label', labels.bell);
        setStatus(bellStatus, message, expired ? 'expired' : 'error');
        setStatus(pageStatus, message, expired ? 'expired' : 'error');
        if (isWrite && pageStatus) pageStatus.focus();
    }

    async function request(url, options = {}) {
        const response = await fetch(internalUrl(url), {
            credentials: 'same-origin',
            cache: 'no-store',
            ...options,
            headers: { Accept: 'application/json', 'X-CSRF-TOKEN': csrf },
        });
        if (response.redirected || !response.headers.get('content-type')?.includes('application/json')) {
            const error = new Error('Unexpected session response');
            error.status = response.status;
            error.sessionExpired = response.redirected || response.status === 419;
            throw error;
        }
        const payload = await response.json();
        if (!response.ok || !payload.success) {
            const error = new Error('Notification request failed');
            error.status = response.status;
            throw error;
        }
        return payload;
    }

    function actionForm(notification, action, label) {
        const form = element('form', '');
        form.method = 'POST';
        form.action = internalUrl(notification[`${action}_url`]);
        form.dataset.notificationForm = action;
        const token = document.createElement('input');
        token.type = 'hidden';
        token.name = '_token';
        token.value = csrf;
        const button = element('button', 'btn btn-sm btn-outline-secondary', label);
        button.type = 'submit';
        button.dataset.notificationWrite = '';
        form.append(token, button);
        return form;
    }

    function renderNotification(notification) {
        const item = element('article', 'px-3 py-3 border-bottom text-break');
        item.append(element('div', notification.read ? '' : 'fw-semibold', notification.title));
        item.append(element('p', 'small text-body-secondary mb-1', notification.message));
        item.append(element('div', 'small text-body-secondary mb-1', notification.type_label));
        const time = element('time', 'small text-body-secondary', formatTime(notification.occurred_at));
        time.dateTime = notification.occurred_at || '';
        const status = element('span', `badge ms-2 text-bg-${notification.read ? 'secondary' : 'primary'}`,
            notification.read ? labels.read : labels.unread);
        item.append(time, status);
        const actions = element('div', 'd-flex flex-wrap gap-2 mt-2');
        actions.append(actionForm(notification, 'open', labels.open));
        if (!notification.read) actions.append(actionForm(notification, 'read', labels.markRead));
        item.append(actions);
        return item;
    }

    async function loadFeed() {
        if (loading || writing || expired) return;
        loading = true;
        const version = ++loadVersion;
        loadController = new AbortController();
        recent.replaceChildren();
        recent.setAttribute('aria-busy', 'true');
        setStatus(bellStatus, labels.loading);
        updateDisabled();
        try {
            const { data } = await request(bell.dataset.feedUrl, { signal: loadController.signal });
            if (version !== loadVersion) return;
            unreadCount = data.counts.unread;
            badge.textContent = String(unreadCount);
            badge.classList.toggle('d-none', unreadCount === 0);
            toggle.setAttribute('aria-label', `${labels.bell}: ${unreadCount} ${labels.unread}`);
            recent.replaceChildren(...data.notifications.map(renderNotification));
            setStatus(bellStatus, data.notifications.length ? '' : labels.empty);
        } catch (error) {
            if (error.name !== 'AbortError' && version === loadVersion) showError(error);
        } finally {
            if (version === loadVersion) {
                loading = false;
                recent.setAttribute('aria-busy', 'false');
                updateDisabled();
            }
        }
    }

    async function submitNotification(form) {
        if (writing || expired) return;
        writing = true;
        loadController?.abort();
        ++loadVersion;
        loading = false;
        recent.setAttribute('aria-busy', 'false');
        updateDisabled();
        setStatus(bellStatus, labels.working);
        setStatus(pageStatus, labels.working);
        try {
            const result = await request(form.action, { method: 'POST', body: new FormData(form) });
            // Read state changes only after a successful POST. Failed writes are never auto-retried.
            if (form.dataset.notificationForm === 'open') {
                window.location.assign(internalUrl(result.data.url));
                return;
            }
            if (inboxPage) {
                window.location.reload();
                return;
            }
            writing = false;
            await loadFeed();
            toggle.focus();
        } catch (error) {
            writing = false;
            showError(error, true);
        } finally {
            // Keep controls locked while navigating away after a successful write.
            updateDisabled();
        }
    }

    document.addEventListener('submit', event => {
        const form = event.target.closest('form[data-notification-form]');
        if (!form) return;
        event.preventDefault();
        submitNotification(form);
    });
    toggle.addEventListener('show.bs.dropdown', loadFeed);
    document.querySelectorAll('[data-notification-time]').forEach(time => {
        time.textContent = formatTime(time.dateTime);
    });
    loadFeed();
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initializeNotifications);
} else {
    initializeNotifications();
}

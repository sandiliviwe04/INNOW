<!DOCTYPE html>
<html lang="en" class="h-full bg-zinc-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/assets/tailwind.css">
    <link rel="preconnect" href="https://unpkg.com">
    <link rel="preconnect" href="https://cdn.jsdelivr.net">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <script>
        // Force a fresh server request whenever this page is restored from the
        // browser's back/forward cache (bfcache), instead of showing a stale,
        // possibly-authenticated snapshot after logout. The server will then
        // re-check the session via AuthMiddleware::guard() and redirect to
        // /login if it's no longer valid.
        window.addEventListener('pageshow', function (event) {
            if (event.persisted || (window.performance && performance.getEntriesByType('navigation')[0]?.type === 'back_forward')) {
                window.location.reload();
            }
        });
    </script>
    <?php if (!empty($csrfToken)): ?>
    <meta name="csrf-token" content="<?= htmlspecialchars($csrfToken) ?>">
    <script>window.csrfToken = <?= json_encode($csrfToken) ?>;</script>
    <?php else: ?>
    <script>window.csrfToken = '';</script>
    <?php endif; ?>
    <script>
        function getCsrfToken() {
            const meta = document.querySelector('meta[name="csrf-token"]');
            const metaToken = meta ? meta.getAttribute('content') : '';
            return metaToken || (typeof window !== 'undefined' ? (window.csrfToken || '') : '');
        }
        async function authFetch(url, options = {}) {
            const headers = {
                'Content-Type': 'application/json',
                'X-CSRF-Token': getCsrfToken(),
                ...(options.headers || {})
            };
            let res;
            try {
                res = await fetch(url, {
                    ...options,
                    headers,
                    credentials: 'same-origin'
                });
            } catch (networkErr) {
                // Genuine network-level failure (offline, DNS, CORS, server unreachable)
                throw new Error('NETWORK_FAILURE');
            }
            // Do NOT throw on 4xx/5xx here — the backend returns proper JSON
            // error bodies (success:false + message) that callers should read
            // and display directly, instead of a generic error.
            return res;
        }
    </script>
    <?php if (!empty($user)): ?>
    <script>
        // --- Live activity notifications -------------------------------------------------
        // Polls the server every few seconds for announcements/check-ins made by OTHER
        // staff members while this user is logged in, and shows a pop-up toast for each.
        (function () {
            let cursor = null;
            const POLL_INTERVAL_MS = 8000;

            function ensureToastContainer() {
                let el = document.getElementById('innow-toast-container');
                if (!el) {
                    el = document.createElement('div');
                    el.id = 'innow-toast-container';
                    document.body.appendChild(el);
                }
                return el;
            }

            function actionLabel(action) {
                switch (action) {
                    case 'CLOCK_IN': return 'clocked in';
                    case 'CLOCK_OUT': return 'clocked out';
                    case 'BREAK_START': return 'started a break';
                    case 'BREAK_END': return 'ended their break';
                    default: return action.toLowerCase().replace('_', ' ');
                }
            }

            function showToast(event) {
                const container = ensureToastContainer();
                const toast = document.createElement('div');
                toast.className = 'innow-toast';

                let icon = '🔔';
                let text = '';
                if (event.type === 'announcement') {
                    icon = '📢';
                    text = `<strong>${event.author}</strong> posted an announcement: <strong>${event.title}</strong>`;
                } else if (event.type === 'attendance') {
                    icon = '🕒';
                    text = `<strong>${event.staff_name}</strong> ${actionLabel(event.action)}`;
                }

                toast.innerHTML = `<span style="font-size:1rem;line-height:1.2;">${icon}</span><span style="flex:1;line-height:1.4;">${text}</span>`;
                container.appendChild(toast);

                requestAnimationFrame(() => {
                    toast.classList.add('show');
                });

                setTimeout(() => {
                    toast.classList.remove('show');
                    setTimeout(() => toast.remove(), 300);
                }, 6000);
            }

            async function poll() {
                try {
                    const url = cursor ? `/api/notifications/poll?since=${encodeURIComponent(cursor)}` : '/api/notifications/poll';
                    const res = await authFetch(url);
                    const data = await res.json();
                    if (data.success) {
                        cursor = data.server_time;
                        (data.events || []).forEach(showToast);
                    }
                } catch (e) {
                    // Silently ignore — a missed poll just means we retry next interval.
                }
            }

            poll();
            setInterval(poll, POLL_INTERVAL_MS);
        })();
    </script>
    <?php endif; ?>
    <script>
        document.addEventListener('click', function(e) {
            const link = e.target.closest('a[href]');
            if (!link) return;
            if (link.target === '_blank') return;
            if (link.href.includes('#')) return;
            if (link.getAttribute('download')) return;
            if (link.getAttribute('role') === 'button' && !link.getAttribute('href')) return;

            const linkPath = new URL(link.href, window.location.origin).pathname;
            if (linkPath === window.location.pathname) return;

            const overlay = document.getElementById('page-transition-overlay');
            if (overlay) {
                overlay.classList.add('active');
            }
        });

        document.addEventListener('DOMContentLoaded', function() {
            const overlay = document.getElementById('page-transition-overlay');
            if (overlay) {
                requestAnimationFrame(() => {
                    requestAnimationFrame(() => {
                        overlay.classList.remove('active');
                    });
                });
            }
        });
    </script>
    <title><?= htmlspecialchars($pageTitle ?? 'INNOW — Digital Attendance System') ?></title>
    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@500;700&display=swap');
        body {
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, sans-serif;
        }
        .font-mono {
            font-family: 'JetBrains Mono', monospace;
        }

        #page-transition-overlay {
          position: fixed;
          inset: 0;
          background: #fafafa;
          opacity: 0;
          pointer-events: none;
          transition: opacity 0.15s ease;
          z-index: 99999;
        }
        #page-transition-overlay.active {
          opacity: 1;
          pointer-events: auto;
        }

        .mobile-nav-animate {
          display: grid;
          grid-template-rows: 0fr;
          transition: grid-template-rows 0.3s ease, opacity 0.3s ease;
          opacity: 0;
        }
        .mobile-nav-animate.open {
          grid-template-rows: 1fr;
          opacity: 1;
        }
        .mobile-nav-animate > div {
          overflow: hidden;
        }

        .modal-backdrop {
          opacity: 0;
          transition: opacity 0.2s ease;
        }
        .modal-backdrop.modal-visible {
          opacity: 1;
        }
        .modal-content {
          opacity: 0;
          transform: scale(0.95) translateY(8px);
          transition: opacity 0.2s ease, transform 0.2s ease;
        }
        .modal-visible .modal-content {
          opacity: 1;
          transform: scale(1) translateY(0);
        }

        #innow-toast-container {
          position: fixed;
          top: 1rem;
          right: 1rem;
          z-index: 9999;
          display: flex;
          flex-direction: column;
          gap: 0.5rem;
          max-width: 22rem;
        }
        .innow-toast {
          background: #18181b;
          color: #fff;
          border-radius: 0.75rem;
          padding: 0.75rem 1rem;
          box-shadow: 0 10px 25px rgba(0,0,0,0.25);
          font-family: inherit;
          font-size: 0.8rem;
          display: flex;
          gap: 0.6rem;
          align-items: flex-start;
          opacity: 0;
          transform: translateX(1rem);
          transition: opacity 0.25s ease, transform 0.25s ease;
          will-change: transform, opacity;
          transform: translateZ(0);
        }
        .innow-toast.show {
          opacity: 1;
          transform: translateX(0);
        }
        #checkin-toast {
          will-change: transform, opacity;
          transform: translateZ(0);
        }
    </style>
</head>
<body class="h-full bg-zinc-50 text-zinc-900 antialiased flex flex-col selection:bg-red-500 selection:text-white">
    <!-- Page Transition Overlay -->
    <div id="page-transition-overlay"></div>
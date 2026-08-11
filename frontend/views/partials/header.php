<!DOCTYPE html>
<html lang="en" class="h-full bg-zinc-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
                    el.style.cssText = 'position:fixed;top:1rem;right:1rem;z-index:9999;display:flex;flex-direction:column;gap:0.5rem;max-width:22rem;';
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
                toast.style.cssText = 'background:#18181b;color:#fff;border-radius:0.75rem;padding:0.75rem 1rem;box-shadow:0 10px 25px rgba(0,0,0,0.25);font-family:inherit;font-size:0.8rem;display:flex;gap:0.6rem;align-items:flex-start;opacity:0;transform:translateX(1rem);transition:opacity 0.25s ease,transform 0.25s ease;';

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
                    toast.style.opacity = '1';
                    toast.style.transform = 'translateX(0)';
                });

                setTimeout(() => {
                    toast.style.opacity = '0';
                    toast.style.transform = 'translateX(1rem)';
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
    <title><?= htmlspecialchars($pageTitle ?? 'INNOW — Digital Attendance System') ?></title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: {
                            50: '#fef2f2',
                            100: '#ffe1e1',
                            500: '#ef4444',
                            600: '#dc2626',
                            700: '#b91c1c',
                            900: '#7f1d1d',
                        }
                    }
                }
            }
        }
    </script>
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
    </style>
</head>
<body class="h-full bg-zinc-50 text-zinc-900 antialiased flex flex-col selection:bg-red-500 selection:text-white">
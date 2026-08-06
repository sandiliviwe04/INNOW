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
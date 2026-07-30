<!DOCTYPE html>
<html lang="en" class="h-full bg-zinc-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
            const res = await fetch(url, {
                ...options,
                headers,
                credentials: 'same-origin'
            });
            if (!res.ok) {
                const text = await res.text().catch(() => 'Unknown error');
                throw new Error(`API ${res.status}: ${text}`);
            }
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

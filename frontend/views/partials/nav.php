<?php
$currentPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? '/';
$user = $user ?? null;
$isAdmin = $user && (stripos($user['role'] ?? '', 'admin') !== false || ($user['role'] ?? '') === 'System Administrator');
?>
<header class="bg-white border-b border-zinc-200 sticky top-0 z-40 shadow-xs">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16">
            <!-- Logo & Brand -->
            <div class="flex items-center gap-3">
                <a href="/dashboard" class="flex items-center gap-2.5">
                    <div class="w-10 h-10 rounded-xl bg-red-600 flex items-center justify-center text-white shadow-xs">
                        <i data-lucide="shield-check" class="w-6 h-6"></i>
                    </div>
                    <div>
                        <div class="flex items-center gap-1.5">
                            <span class="font-black text-lg text-zinc-900 tracking-tight">INNOW</span>
                            <span class="px-1.5 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider bg-red-50 text-red-700 border border-red-200">PHP 8.3</span>
                        </div>
                        <p class="text-[11px] text-zinc-500 font-medium">Digital Attendance System</p>
                    </div>
                </a>
            </div>

            <!-- Mobile Menu Button -->
            <button id="mobile-menu-btn" class="md:hidden p-2 hover:bg-zinc-100 rounded-lg text-zinc-600 transition-colors" aria-label="Toggle navigation menu">
                <i data-lucide="menu" class="w-5 h-5" id="menu-icon-open"></i>
                <i data-lucide="x" class="w-5 h-5 hidden" id="menu-icon-close"></i>
            </button>

            <!-- Main Navigation Links -->
            <nav id="main-nav" class="hidden md:flex items-center gap-1">
                 <a href="/checkin" class="px-3 py-2 rounded-lg text-sm font-semibold transition-colors <?= $currentPath === '/checkin' ? 'bg-red-50 text-red-700 font-bold' : 'text-zinc-600 hover:text-zinc-900 hover:bg-zinc-100' ?> flex items-center gap-1.5">
                     <i data-lucide="qr-code" class="w-4 h-4"></i>
                     <span>Check-In</span>
                 </a>

                 <a href="/docs?doc=user-guide" class="px-3 py-2 rounded-lg text-sm font-semibold transition-colors <?= $currentPath === '/docs' ? 'bg-red-50 text-red-700 font-bold' : 'text-zinc-600 hover:text-zinc-900 hover:bg-zinc-100' ?> flex items-center gap-1.5">
                     <i data-lucide="book-open" class="w-4 h-4"></i>
                     <span>Help</span>
                 </a>

                 <a href="/dashboard" class="px-3 py-2 rounded-lg text-sm font-semibold transition-colors <?= $currentPath === '/dashboard' ? 'bg-red-50 text-red-700 font-bold' : 'text-zinc-600 hover:text-zinc-900 hover:bg-zinc-100' ?> flex items-center gap-1.5">
                     <i data-lucide="layout-dashboard" class="w-4 h-4"></i>
                     <span>Live Onsite</span>
                 </a>

                 <?php if ($isAdmin): ?>
                     <a href="/logs" class="px-3 py-2 rounded-lg text-sm font-semibold transition-colors <?= $currentPath === '/logs' ? 'bg-red-50 text-red-700 font-bold' : 'text-zinc-600 hover:text-zinc-900 hover:bg-zinc-100' ?> flex items-center gap-1.5">
                         <i data-lucide="history" class="w-4 h-4"></i>
                         <span>Audit Logs</span>
                     </a>

                     <a href="/docs" class="px-3 py-2 rounded-lg text-sm font-semibold transition-colors <?= $currentPath === '/docs' ? 'bg-red-50 text-red-700 font-bold' : 'text-zinc-600 hover:text-zinc-900 hover:bg-zinc-100' ?> flex items-center gap-1.5">
                         <i data-lucide="file-text" class="w-4 h-4"></i>
                         <span>Docs</span>
                     </a>
                 <?php endif; ?>

                 <a href="/staff" class="px-3 py-2 rounded-lg text-sm font-semibold transition-colors <?= $currentPath === '/staff' ? 'bg-red-50 text-red-700 font-bold' : 'text-zinc-600 hover:text-zinc-900 hover:bg-zinc-100' ?> flex items-center gap-1.5">
                     <i data-lucide="users" class="w-4 h-4"></i>
                     <span>Staff Directory</span>
                 </a>
                 <a href="/leave" class="px-3 py-2 rounded-lg text-sm font-semibold transition-colors <?= $currentPath === '/leave' ? 'bg-red-50 text-red-700 font-bold' : 'text-zinc-600 hover:text-zinc-900 hover:bg-zinc-100' ?> flex items-center gap-1.5">
                     <i data-lucide="calendar" class="w-4 h-4"></i>
                     <span>Leave</span>
                 </a>
                 <a href="/announcements" class="px-3 py-2 rounded-lg text-sm font-semibold transition-colors <?= $currentPath === '/announcements' ? 'bg-red-50 text-red-700 font-bold' : 'text-zinc-600 hover:text-zinc-900 hover:bg-zinc-100' ?> flex items-center gap-1.5">
                     <i data-lucide="megaphone" class="w-4 h-4"></i>
                     <span>Announcements</span>
                 </a>
            </nav>

            <!-- Actions Right -->
            <div class="hidden md:flex items-center gap-3">
                <?php if ($user): ?>
                    <div class="flex items-center gap-2 pl-2 border-l border-zinc-200">
                        <div class="w-8 h-8 rounded-full bg-zinc-900 text-white flex items-center justify-center text-xs font-bold">
                            <?= strtoupper(substr($user['name'] ?? 'U', 0, 1)) ?>
                        </div>
                        <div class="hidden lg:block text-left">
                            <p class="text-xs font-bold text-zinc-900 leading-none"><?= htmlspecialchars($user['name']) ?></p>
                            <p class="text-[10px] text-zinc-500 font-mono leading-tight"><?= htmlspecialchars($user['role']) ?></p>
                        </div>
                        <a href="/logout" class="p-1.5 hover:bg-zinc-100 rounded-lg text-zinc-500 hover:text-zinc-900" title="Sign out">
                            <i data-lucide="log-out" class="w-4 h-4"></i>
                        </a>
                    </div>
                <?php else: ?>
                    <a href="/login" class="px-3 py-1.5 bg-zinc-900 hover:bg-zinc-800 text-white rounded-lg text-xs font-bold transition-colors">
                        Staff Login
                    </a>
                <?php endif; ?>
            </div>
        </div>

        <!-- Mobile Navigation Menu -->
        <div id="mobile-nav" class="hidden md:hidden border-t border-zinc-200 bg-white">
            <nav class="px-4 py-3 space-y-1">
                 <a href="/checkin" class="block px-3 py-2.5 rounded-lg text-sm font-semibold transition-colors <?= $currentPath === '/checkin' ? 'bg-red-50 text-red-700 font-bold' : 'text-zinc-600 hover:text-zinc-900 hover:bg-zinc-100' ?> flex items-center gap-2">
                     <i data-lucide="qr-code" class="w-4 h-4"></i>
                     <span>Check-In</span>
                 </a>

                 <a href="/docs?doc=user-guide" class="block px-3 py-2.5 rounded-lg text-sm font-semibold transition-colors <?= $currentPath === '/docs' ? 'bg-red-50 text-red-700 font-bold' : 'text-zinc-600 hover:text-zinc-900 hover:bg-zinc-100' ?> flex items-center gap-2">
                     <i data-lucide="book-open" class="w-4 h-4"></i>
                     <span>Help</span>
                 </a>

                 <a href="/dashboard" class="block px-3 py-2.5 rounded-lg text-sm font-semibold transition-colors <?= $currentPath === '/dashboard' ? 'bg-red-50 text-red-700 font-bold' : 'text-zinc-600 hover:text-zinc-900 hover:bg-zinc-100' ?> flex items-center gap-2">
                     <i data-lucide="layout-dashboard" class="w-4 h-4"></i>
                     <span>Live Onsite</span>
                 </a>

                 <?php if ($isAdmin): ?>
                     <a href="/logs" class="block px-3 py-2.5 rounded-lg text-sm font-semibold transition-colors <?= $currentPath === '/logs' ? 'bg-red-50 text-red-700 font-bold' : 'text-zinc-600 hover:text-zinc-900 hover:bg-zinc-100' ?> flex items-center gap-2">
                         <i data-lucide="history" class="w-4 h-4"></i>
                         <span>Audit Logs</span>
                     </a>

                     <a href="/docs" class="block px-3 py-2.5 rounded-lg text-sm font-semibold transition-colors <?= $currentPath === '/docs' ? 'bg-red-50 text-red-700 font-bold' : 'text-zinc-600 hover:text-zinc-900 hover:bg-zinc-100' ?> flex items-center gap-2">
                         <i data-lucide="file-text" class="w-4 h-4"></i>
                         <span>Docs</span>
                     </a>
                 <?php endif; ?>

                 <a href="/staff" class="block px-3 py-2.5 rounded-lg text-sm font-semibold transition-colors <?= $currentPath === '/staff' ? 'bg-red-50 text-red-700 font-bold' : 'text-zinc-600 hover:text-zinc-900 hover:bg-zinc-100' ?> flex items-center gap-2">
                     <i data-lucide="users" class="w-4 h-4"></i>
                     <span>Staff Directory</span>
                 </a>
                 <a href="/leave" class="block px-3 py-2.5 rounded-lg text-sm font-semibold transition-colors <?= $currentPath === '/leave' ? 'bg-red-50 text-red-700 font-bold' : 'text-zinc-600 hover:text-zinc-900 hover:bg-zinc-100' ?> flex items-center gap-2">
                     <i data-lucide="calendar" class="w-4 h-4"></i>
                     <span>Leave</span>
                 </a>
                 <a href="/announcements" class="block px-3 py-2.5 rounded-lg text-sm font-semibold transition-colors <?= $currentPath === '/announcements' ? 'bg-red-50 text-red-700 font-bold' : 'text-zinc-600 hover:text-zinc-900 hover:bg-zinc-100' ?> flex items-center gap-2">
                     <i data-lucide="megaphone" class="w-4 h-4"></i>
                     <span>Announcements</span>
                 </a>
            </nav>
            <div class="px-4 pb-4 border-t border-zinc-100 pt-3">
                <?php if ($user): ?>
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-full bg-zinc-900 text-white flex items-center justify-center text-xs font-bold">
                            <?= strtoupper(substr($user['name'] ?? 'U', 0, 1)) ?>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-bold text-zinc-900 truncate"><?= htmlspecialchars($user['name']) ?></p>
                            <p class="text-[10px] text-zinc-500 font-mono truncate"><?= htmlspecialchars($user['role']) ?></p>
                        </div>
                        <a href="/logout" class="px-3 py-2 bg-zinc-900 text-white rounded-lg text-xs font-bold flex items-center gap-1.5 hover:bg-zinc-800 transition-colors">
                            <i data-lucide="log-out" class="w-3.5 h-3.5"></i>
                            <span>Sign out</span>
                        </a>
                    </div>
                <?php else: ?>
                    <a href="/login" class="block w-full px-3 py-2.5 bg-zinc-900 text-white rounded-lg text-sm font-bold text-center hover:bg-zinc-800 transition-colors">
                        Staff Login
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        (function() {
            const btn = document.getElementById('mobile-menu-btn');
            const menu = document.getElementById('mobile-nav');
            const openIcon = document.getElementById('menu-icon-open');
            const closeIcon = document.getElementById('menu-icon-close');
            if (!btn || !menu) return;

            btn.addEventListener('click', function() {
                const isOpen = !menu.classList.contains('hidden');
                if (isOpen) {
                    menu.classList.add('hidden');
                    if (openIcon) openIcon.classList.remove('hidden');
                    if (closeIcon) closeIcon.classList.add('hidden');
                } else {
                    menu.classList.remove('hidden');
                    if (openIcon) openIcon.classList.add('hidden');
                    if (closeIcon) closeIcon.classList.remove('hidden');
                }
            });
        })();
    </script>
</header>

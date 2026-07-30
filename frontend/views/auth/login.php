<?php
$pageTitle = "Staff Login — INNOW";
require __DIR__ . '/../partials/header.php';
?>

<div class="min-h-full flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8 bg-zinc-50 my-auto">
    <div class="max-w-md w-full space-y-8 bg-white p-8 rounded-2xl border border-zinc-200 shadow-xl">
        <div class="text-center">
            <div class="mx-auto w-12 h-12 rounded-xl bg-red-600 flex items-center justify-center text-white shadow-md mb-3">
                <i data-lucide="shield-check" class="w-7 h-7"></i>
            </div>
            <h2 class="text-2xl font-extrabold text-zinc-900 tracking-tight">Staff Authenticator</h2>
            <p class="mt-1 text-xs text-zinc-500">Digital Attendance & Access Control</p>
        </div>

        <div id="login-alert" class="hidden p-3 rounded-xl bg-red-50 border border-red-200 text-xs text-red-800 font-medium"></div>

        <form id="login-form" onsubmit="handleLogin(event)" class="mt-6 space-y-4">
            <div>
                <label for="email" class="block text-xs font-bold text-zinc-700 uppercase tracking-wider mb-1">Email Address</label>
                <input type="email" id="email" name="email" required placeholder="thabo.m@innow.com" class="w-full px-4 py-2.5 border border-zinc-300 rounded-xl text-sm focus:ring-2 focus:ring-red-500 focus:border-red-500 outline-none">
            </div>
            <div>
                <label for="pin" class="block text-xs font-bold text-zinc-700 uppercase tracking-wider mb-1">4-Digit PIN</label>
                <input type="password" id="pin" name="pin" maxlength="4" required placeholder="••••" class="w-full px-4 py-3 border border-zinc-300 rounded-xl text-center text-2xl font-mono tracking-widest font-bold focus:ring-2 focus:ring-red-500 focus:border-red-500 outline-none">
                <p class="mt-1 text-[11px] text-zinc-500 text-center">Sample PINs: <strong class="font-mono text-zinc-800">1001</strong> (Admin) | <strong class="font-mono text-zinc-800">1005</strong> (Staff)</p>
            </div>

            <button type="submit" id="login-submit-btn" class="w-full py-3 bg-red-600 hover:bg-red-700 text-white font-bold rounded-xl transition-all shadow-md flex items-center justify-center gap-2 text-sm cursor-pointer">
                <i data-lucide="key-round" class="w-4 h-4"></i>
                <span>Sign In</span>
            </button>
        </form>

        <div class="border-t border-zinc-200 pt-4 text-center space-y-1">
            <span class="text-xs text-zinc-400">Need to access the QR terminal?</span>
            <p class="text-[11px] text-zinc-500 mt-1">Log in first, then open <strong>Check-In</strong> from the navigation menu.</p>
            <p class="text-[11px] text-zinc-500 mt-2">No admin account yet? <a href="/setup" class="text-red-600 font-bold hover:underline">Run initial setup</a></p>
        </div>
    </div>
</div>

<script>
    async function handleLogin(e) {
        e.preventDefault();
        const alertBox = document.getElementById('login-alert');
        alertBox.classList.add('hidden');

        const emailVal = document.getElementById('email').value;
        const pinVal = document.getElementById('pin').value;

        try {
            const res = await fetch('/api/login', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ email: emailVal, pin: pinVal })
            });
            const data = await res.json();

            if (data.success) {
                window.location.href = '/dashboard';
            } else {
                alertBox.innerText = data.message || 'Login failed.';
                alertBox.classList.remove('hidden');
            }
        } catch (err) {
            alertBox.innerText = 'Connection error. Please try again.';
            alertBox.classList.remove('hidden');
        }
    }
</script>

<?php require __DIR__ . '/../partials/footer.php'; ?>

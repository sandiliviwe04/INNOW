<?php
$pageTitle = "Staff Directory — INNOW";
$user = $user ?? null;
$isAdmin = $user && (stripos($user['role'] ?? '', 'admin') !== false || ($user['role'] ?? '') === 'System Administrator');
require __DIR__ . '/../partials/header.php';
require __DIR__ . '/../partials/nav.php';
?>

<main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 flex-1 w-full space-y-8">
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-extrabold text-zinc-900 tracking-tight">Staff Directory & Pass Badges</h1>
            <p class="text-xs text-zinc-500">Manage registered INNOW personnel, PINs, and digital ID passes</p>
        </div>

        <?php if ($isAdmin): ?>
        <button onclick="openAddStaffModal()" class="px-4 py-2.5 bg-red-600 hover:bg-red-700 text-white rounded-xl text-xs font-bold transition-all shadow-md flex items-center gap-2 cursor-pointer">
            <i data-lucide="user-plus" class="w-4 h-4"></i>
            <span>Add New Staff Member</span>
        </button>
        <?php endif; ?>
    </div>

    <!-- Staff Cards Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <?php foreach ($allStaff as $stf): ?>
            <div class="bg-white rounded-2xl border border-zinc-200 p-6 shadow-xs flex flex-col justify-between space-y-4 hover:shadow-md transition-all">
                <div class="flex items-start justify-between">
                    <div class="flex items-center gap-3.5">
                        <div class="w-12 h-12 rounded-xl bg-zinc-900 text-white flex items-center justify-center text-sm font-bold shrink-0">
                            <?= strtoupper(substr($stf['name'], 0, 1)) ?>
                        </div>
                        <div>
                            <h3 class="font-extrabold text-base text-zinc-900 leading-tight"><?= htmlspecialchars($stf['name']) ?></h3>
                            <p class="text-xs text-red-600 font-bold"><?= htmlspecialchars($stf['role']) ?></p>
                            <p class="text-[11px] text-zinc-500"><?= htmlspecialchars($stf['department']) ?></p>
                        </div>
                    </div>
                </div>

                <div class="bg-zinc-50 p-3 rounded-xl border border-zinc-200 space-y-1 text-xs font-mono">
                    <div class="flex justify-between text-zinc-600">
                        <span>Staff ID:</span>
                        <strong class="text-zinc-900"><?= htmlspecialchars($stf['id']) ?></strong>
                    </div>
                    <?php if ($isAdmin): ?>
                    <div class="flex justify-between text-zinc-600">
                        <span>Access PIN:</span>
                        <strong class="text-emerald-700 font-bold"><?= htmlspecialchars($stf['pin']) ?></strong>
                    </div>
                    <?php else: ?>
                    <div class="flex justify-between text-zinc-600">
                        <span>Access PIN:</span>
                        <strong class="text-zinc-400">&#8226;&#8226;&#8226;&#8226;</strong>
                    </div>
                    <?php endif; ?>
                    <div class="flex justify-between text-zinc-600">
                        <span>Contact:</span>
                        <span class="text-zinc-800 font-bold"><?= $isAdmin ? htmlspecialchars($stf['phone']) : 'Restricted' ?></span>
                    </div>
                </div>

                <div class="flex items-center justify-between pt-2 border-t border-zinc-100">
                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase <?= $stf['status'] === 'ONSITE' ? 'bg-emerald-100 text-emerald-800' : 'bg-zinc-100 text-zinc-600' ?>">
                        <?= $stf['status'] ?>
                    </span>

                    <?php if ($isAdmin): ?>
                    <div class="flex items-center gap-2">
                        <button onclick="viewBadge('<?= htmlspecialchars(json_encode($stf)) ?>')" class="px-3 py-1.5 bg-zinc-900 hover:bg-zinc-800 text-white rounded-lg text-xs font-bold transition-colors flex items-center gap-1.5 cursor-pointer shadow-xs">
                            <i data-lucide="qr-code" class="w-3.5 h-3.5"></i>
                            <span>Digital Badge Pass</span>
                        </button>
                        <button data-staff-id="<?= htmlspecialchars($stf['id']) ?>" data-staff-name="<?= htmlspecialchars($stf['name']) ?>" class="reset-pin-btn px-3 py-1.5 bg-amber-600 hover:bg-amber-700 text-white rounded-lg text-xs font-bold transition-colors flex items-center gap-1.5 cursor-pointer shadow-xs">
                            <i data-lucide="key-round" class="w-3.5 h-3.5"></i>
                            <span>Reset PIN</span>
                        </button>
                        <button data-staff-id="<?= htmlspecialchars($stf['id']) ?>" data-staff-name="<?= htmlspecialchars($stf['name']) ?>" class="remove-staff-btn px-3 py-1.5 bg-red-600 hover:bg-red-700 text-white rounded-lg text-xs font-bold transition-colors flex items-center gap-1.5 cursor-pointer shadow-xs">
                            <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                            <span>Remove</span>
                        </button>
                    </div>
                    <?php else: ?>
                    <span class="text-[10px] text-zinc-400 font-medium">Admin managed</span>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</main>

<!-- Add Staff Modal -->
<div id="add-staff-modal" class="fixed inset-0 z-50 bg-black/50 backdrop-blur-xs hidden items-center justify-center p-4">
    <div class="bg-white border border-zinc-200 rounded-2xl max-w-md w-full p-6 shadow-xl space-y-6">
        <div class="flex items-center justify-between border-b border-zinc-100 pb-3">
            <h3 class="text-lg font-bold text-zinc-900">Register Staff Member</h3>
            <button onclick="closeAddStaffModal()" class="p-1 hover:bg-zinc-100 rounded-lg text-zinc-500">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>

        <form onsubmit="handleCreateStaff(event)" class="space-y-4 text-xs">
            <div>
                <label class="block font-bold text-zinc-700 uppercase mb-1">Full Name</label>
                <input type="text" id="new-name" required placeholder="e.g. Sipho Nkosi" class="w-full px-3 py-2 border border-zinc-300 rounded-xl outline-none focus:ring-2 focus:ring-red-500">
            </div>
            <div>
                <label class="block font-bold text-zinc-700 uppercase mb-1">Email Address</label>
                <input type="email" id="new-email" required placeholder="sipho.n@innow.com" class="w-full px-3 py-2 border border-zinc-300 rounded-xl outline-none focus:ring-2 focus:ring-red-500">
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block font-bold text-zinc-700 uppercase mb-1">4-Digit PIN</label>
                    <input type="password" id="new-pin" maxlength="4" required placeholder="1234" class="w-full px-3 py-2 border border-zinc-300 rounded-xl outline-none focus:ring-2 focus:ring-red-500 text-center font-mono">
                </div>
                <div>
                    <label class="block font-bold text-zinc-700 uppercase mb-1">Department</label>
                    <select id="new-dept" class="w-full px-3 py-2 border border-zinc-300 rounded-xl outline-none focus:ring-2 focus:ring-red-500">
                        <option>Software Engineering</option>
                        <option>Design & UX</option>
                        <option>Infrastructure</option>
                        <option>Analytics & AI</option>
                        <option>Quality Assurance</option>
                    </select>
                </div>
            </div>
            <div>
                <label class="block font-bold text-zinc-700 uppercase mb-1">Role Title</label>
                <input type="text" id="new-role" placeholder="Junior Developer" class="w-full px-3 py-2 border border-zinc-300 rounded-xl outline-none focus:ring-2 focus:ring-red-500">
            </div>

            <button type="submit" class="w-full py-3 bg-red-600 hover:bg-red-700 text-white font-bold rounded-xl transition-all cursor-pointer">
                Save Staff Record
            </button>
        </form>
    </div>
</div>

<!-- Digital Badge Modal -->
<div id="badge-modal" class="fixed inset-0 z-50 bg-black/50 backdrop-blur-xs hidden items-center justify-center p-4">
    <div class="bg-white border border-zinc-200 rounded-2xl max-w-sm w-full p-6 text-zinc-900 shadow-xl relative space-y-6">
        <button onclick="closeBadgeModal()" class="absolute top-4 right-4 p-1.5 bg-zinc-100 hover:bg-zinc-200 rounded-lg text-zinc-500 cursor-pointer">
            <i data-lucide="x" class="w-4 h-4"></i>
        </button>

        <div id="badge-card-printable" class="bg-white border-2 border-zinc-900 rounded-2xl p-6 text-center space-y-4 relative overflow-hidden">
            <div class="bg-zinc-900 text-white text-[11px] font-extrabold uppercase tracking-widest py-1 px-3 -mx-6 -mt-6 mb-4 flex items-center justify-center gap-2">
                <i data-lucide="shield-check" class="w-4 h-4 text-emerald-400"></i>
                <span>LC STUDIO • OFFICIAL ONSITE PASS</span>
            </div>

            <div id="badge-img-placeholder" class="w-24 h-24 rounded-2xl bg-zinc-900 text-white flex items-center justify-center text-3xl font-bold mx-auto border-2 border-zinc-200">
                <?= strtoupper(substr('', 0, 1)) ?>
            </div>
            <div>
                <h3 id="badge-name" class="text-lg font-extrabold text-zinc-900 tracking-tight">Staff Name</h3>
                <p id="badge-role" class="text-xs font-bold text-zinc-700">Role Title</p>
                <p id="badge-dept" class="text-[11px] text-zinc-500">Department</p>
            </div>

            <div class="bg-zinc-50 p-2.5 rounded-xl border border-zinc-200 flex justify-between items-center text-xs font-mono">
                <span class="text-zinc-500">STAFF ID:</span>
                <span id="badge-id" class="font-bold text-zinc-900">ID</span>
                <span class="text-zinc-500 ml-2">PIN:</span>
                <span id="badge-pin" class="font-bold text-emerald-700">PIN</span>
            </div>
        </div>

        <button onclick="window.print()" class="w-full py-3 bg-zinc-900 hover:bg-zinc-800 text-white font-bold rounded-xl transition-all shadow-xs flex items-center justify-center gap-2 cursor-pointer">
            <i data-lucide="printer" class="w-4 h-4"></i>
            <span>PRINT PASS BADGE</span>
        </button>
    </div>
</div>

<!-- Reset PIN Modal -->
<div id="reset-pin-modal" class="fixed inset-0 z-50 bg-black/50 backdrop-blur-xs hidden items-center justify-center p-4">
    <div class="bg-white border border-zinc-200 rounded-2xl max-w-md w-full p-6 shadow-xl space-y-6">
        <div class="flex items-center justify-between border-b border-zinc-100 pb-3">
            <h3 class="text-lg font-bold text-zinc-900">Reset PIN</h3>
            <button onclick="closeResetPinModal()" class="p-1 hover:bg-zinc-100 rounded-lg text-zinc-500">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>
        <p class="text-xs text-zinc-600">Set a new 4-digit PIN for <strong id="reset-pin-staff-name" class="text-zinc-900"></strong>. Leave blank to auto-generate a random PIN.</p>
        <form onsubmit="handleResetPin(event)" class="space-y-4 text-xs">
            <input type="hidden" id="reset-pin-staff-id" value="">
            <div>
                <label class="block font-bold text-zinc-700 uppercase mb-1">New 4-Digit PIN (optional)</label>
                <input type="text" id="reset-pin-value" maxlength="4" placeholder="Leave blank for random PIN" class="w-full px-3 py-2 border border-zinc-300 rounded-xl outline-none focus:ring-2 focus:ring-red-500 text-center font-mono">
            </div>
            <button type="submit" class="w-full py-3 bg-amber-600 hover:bg-amber-700 text-white font-bold rounded-xl transition-all cursor-pointer">
                Reset PIN
            </button>
        </form>
    </div>
</div>

<script>
    function openAddStaffModal() { document.getElementById('add-staff-modal').classList.replace('hidden', 'flex'); }
    function closeAddStaffModal() { document.getElementById('add-staff-modal').classList.replace('flex', 'hidden'); }
    function closeBadgeModal() { document.getElementById('badge-modal').classList.replace('flex', 'hidden'); }

    function viewBadge(jsonStr) {
        const stf = JSON.parse(jsonStr);
        const imgEl = document.getElementById('badge-img-placeholder');
        imgEl.innerHTML = stf.name.charAt(0).toUpperCase();
        document.getElementById('badge-name').innerText = stf.name;
        document.getElementById('badge-role').innerText = stf.role;
        document.getElementById('badge-dept').innerText = stf.department;
        document.getElementById('badge-id').innerText = stf.id;
        document.getElementById('badge-pin').innerText = stf.pin;
        document.getElementById('badge-modal').classList.replace('hidden', 'flex');
    }

    async function handleCreateStaff(e) {
        e.preventDefault();
        const body = {
            name: document.getElementById('new-name').value,
            email: document.getElementById('new-email').value,
            pin: document.getElementById('new-pin').value,
            department: document.getElementById('new-dept').value,
            role: document.getElementById('new-role').value,
        };

        try {
            const res = await authFetch('/api/staff/add', {
                method: 'POST',
                body: JSON.stringify(body)
            });
            const data = await res.json();
            if (data.success) {
                window.location.reload();
            } else {
                alert(data.message || 'Failed to add staff.');
            }
        } catch (e) {
            alert('Error adding staff.');
        }
    }

    async function removeStaff(id, name) {
        if (!confirm(`Remove ${name} from the system? This cannot be undone.`)) {
            return;
        }
        try {
            const res = await authFetch('/api/staff/remove', {
                method: 'POST',
                body: JSON.stringify({ id })
            });
            const data = await res.json();
            if (data.success) {
                window.location.reload();
            } else {
                alert(data.message || 'Failed to remove staff.');
            }
        } catch (e) {
            alert('Error removing staff.');
        }
    }

    function openResetPinModal(id, name) {
        document.getElementById('reset-pin-staff-id').value = id;
        document.getElementById('reset-pin-staff-name').innerText = name;
        document.getElementById('reset-pin-value').value = '';
        document.getElementById('reset-pin-modal').classList.replace('hidden', 'flex');
    }

    function closeResetPinModal() {
        document.getElementById('reset-pin-modal').classList.replace('flex', 'hidden');
    }

    async function handleResetPin(e) {
        e.preventDefault();
        const id = document.getElementById('reset-pin-staff-id').value;
        const pin = document.getElementById('reset-pin-value').value;
        if (!id) {
            alert('Missing staff ID.');
            return;
        }

        try {
            const res = await authFetch('/api/staff/reset-pin', {
                method: 'POST',
                body: JSON.stringify({ id, pin: pin || undefined })
            });
            const data = await res.json();
            if (data.success) {
                const newPin = data.data?.pin || '(random)';
                alert(`PIN reset successfully. New PIN: ${newPin}`);
                closeResetPinModal();
                window.location.reload();
            } else {
                alert(data.message || 'Failed to reset PIN.');
            }
        } catch (e) {
            alert('Error resetting PIN: ' + (e.message || 'Unknown error'));
        }
    }

    document.addEventListener('click', function(e) {
        const removeBtn = e.target.closest('.remove-staff-btn');
        if (removeBtn) {
            const id = removeBtn.getAttribute('data-staff-id');
            const name = removeBtn.getAttribute('data-staff-name');
            removeStaff(id, name);
            return;
        }

        const resetBtn = e.target.closest('.reset-pin-btn');
        if (resetBtn) {
            const id = resetBtn.getAttribute('data-staff-id');
            const name = resetBtn.getAttribute('data-staff-name');
            openResetPinModal(id, name);
        }
    });
</script>

<?php require __DIR__ . '/../partials/footer.php'; ?>

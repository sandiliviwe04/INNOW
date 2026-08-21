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

    <!-- Search -->
    <div class="relative max-w-md">
        <i data-lucide="search" class="w-4 h-4 text-zinc-400 absolute left-3.5 top-1/2 -translate-y-1/2"></i>
        <input
            type="text"
            id="staff-search-input"
            oninput="filterStaffDirectory()"
            placeholder="Search staff by name..."
            class="w-full pl-10 pr-4 py-2.5 border border-zinc-300 rounded-xl text-sm text-zinc-900 bg-white focus:ring-2 focus:ring-red-500 focus:border-red-500 outline-none"
        >
    </div>

    <p id="staff-search-empty" class="hidden text-sm text-zinc-500 text-center py-10">No staff members match your search.</p>

    <!-- Staff Cards Grid -->
    <div id="staff-cards-grid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <?php foreach ($allStaff as $stf): ?>
            <div class="staff-card bg-white rounded-2xl border border-zinc-200 p-6 shadow-xs flex flex-col justify-between space-y-4 hover:shadow-md transition-all" data-staff-name="<?= htmlspecialchars(strtolower($stf['name'])) ?>">
                <div class="flex items-start justify-between">
                    <div class="flex items-center gap-3.5">
                        <div class="w-12 h-12 rounded-xl bg-zinc-900 text-white flex items-center justify-center text-sm font-bold shrink-0 overflow-hidden">
                            <?php if (!empty($stf['avatar_url'])): ?>
                                <img src="<?= htmlspecialchars($stf['avatar_url']) ?>" alt="" class="w-full h-full object-cover">
                            <?php else: ?>
                                <?= strtoupper(substr($stf['name'], 0, 1)) ?>
                            <?php endif; ?>
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
                    <div class="flex justify-between text-zinc-600">
                        <span>Contact:</span>
                        <span class="text-zinc-800 font-bold"><?= $isAdmin ? htmlspecialchars($stf['phone']) : 'Restricted' ?></span>
                    </div>
                    <?php if ($isAdmin): ?>
                    <div class="flex justify-between text-zinc-600">
                        <span>Emergency:</span>
                        <span class="text-zinc-800 font-bold"><?= !empty($stf['emergency_contact']) ? htmlspecialchars($stf['emergency_contact']) : '—' ?></span>
                    </div>
                    <div class="flex justify-between text-zinc-600 gap-3">
                        <span class="shrink-0">Address:</span>
                        <span class="text-zinc-800 font-bold text-right"><?= !empty($stf['address']) ? htmlspecialchars($stf['address']) : '—' ?></span>
                    </div>
                    <?php endif; ?>
                </div>

                <div class="pt-2 border-t border-zinc-100 space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase <?= $stf['status'] === 'ONSITE' ? 'bg-emerald-100 text-emerald-800' : 'bg-zinc-100 text-zinc-600' ?>">
                            <?= $stf['status'] ?>
                        </span>
                        <?php if (!$isAdmin && !($user && $stf['id'] === ($user['user_id'] ?? null))): ?>
                        <span class="text-[10px] text-zinc-400 font-medium">Admin managed</span>
                        <?php endif; ?>
                    </div>

                    <?php if ($isAdmin): ?>
                    <div class="grid grid-cols-2 gap-2">
                        <button onclick="openEditModal('<?= htmlspecialchars(json_encode($stf)) ?>')" class="px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-xs font-bold transition-colors flex items-center justify-center gap-1.5 cursor-pointer shadow-xs">
                            <i data-lucide="pencil" class="w-3.5 h-3.5"></i>
                            <span>Edit</span>
                        </button>
                        <button onclick="viewBadge('<?= htmlspecialchars(json_encode($stf)) ?>')" class="px-3 py-1.5 bg-zinc-900 hover:bg-zinc-800 text-white rounded-lg text-xs font-bold transition-colors flex items-center justify-center gap-1.5 cursor-pointer shadow-xs">
                            <i data-lucide="qr-code" class="w-3.5 h-3.5"></i>
                            <span>Badge Pass</span>
                        </button>
                        <button data-staff-id="<?= htmlspecialchars($stf['id']) ?>" data-staff-name="<?= htmlspecialchars($stf['name']) ?>" class="reset-pin-btn px-3 py-1.5 bg-amber-600 hover:bg-amber-700 text-white rounded-lg text-xs font-bold transition-colors flex items-center justify-center gap-1.5 cursor-pointer shadow-xs">
                            <i data-lucide="key-round" class="w-3.5 h-3.5"></i>
                            <span>Reset PIN</span>
                        </button>
                        <button data-staff-id="<?= htmlspecialchars($stf['id']) ?>" data-staff-name="<?= htmlspecialchars($stf['name']) ?>" class="remove-staff-btn px-3 py-1.5 bg-red-600 hover:bg-red-700 text-white rounded-lg text-xs font-bold transition-colors flex items-center justify-center gap-1.5 cursor-pointer shadow-xs">
                            <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                            <span>Remove</span>
                        </button>
                    </div>
                    <?php elseif ($user && $stf['id'] === ($user['user_id'] ?? null)): ?>
                    <button onclick="openEditModal('<?= htmlspecialchars(json_encode($stf)) ?>')" class="w-full px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-xs font-bold transition-colors flex items-center justify-center gap-1.5 cursor-pointer shadow-xs">
                        <i data-lucide="pencil" class="w-3.5 h-3.5"></i>
                        <span>Edit My Info</span>
                    </button>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</main>

<!-- Add Staff Modal -->
<div id="add-staff-modal" class="modal-backdrop fixed inset-0 z-50 bg-black/50 backdrop-blur-xs hidden items-center justify-center p-4">
    <div class="modal-content bg-white border border-zinc-200 rounded-2xl max-w-md w-full p-6 shadow-xl space-y-6">
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
            <div>
                <label class="block font-bold text-zinc-700 uppercase mb-1">Phone Number</label>
                <input type="text" id="new-phone" placeholder="+27 82 000 0000" class="w-full px-3 py-2 border border-zinc-300 rounded-xl outline-none focus:ring-2 focus:ring-red-500">
            </div>
            <div>
                <label class="block font-bold text-zinc-700 uppercase mb-1">Emergency Contact Number</label>
                <input type="text" id="new-emergency" placeholder="+27 82 000 0000" class="w-full px-3 py-2 border border-zinc-300 rounded-xl outline-none focus:ring-2 focus:ring-red-500">
            </div>
            <div>
                <label class="block font-bold text-zinc-700 uppercase mb-1">Address</label>
                <input type="text" id="new-address" placeholder="123 Main Rd, Cape Town" class="w-full px-3 py-2 border border-zinc-300 rounded-xl outline-none focus:ring-2 focus:ring-red-500">
            </div>

            <button type="submit" class="w-full py-3 bg-red-600 hover:bg-red-700 text-white font-bold rounded-xl transition-all cursor-pointer">
                Save Staff Record
            </button>
        </form>
    </div>
</div>

<!-- Digital Badge Modal -->
<div id="badge-modal" class="modal-backdrop fixed inset-0 z-50 bg-black/50 backdrop-blur-xs hidden items-center justify-center p-4">
    <div class="modal-content bg-white border border-zinc-200 rounded-2xl max-w-sm w-full p-6 text-zinc-900 shadow-xl relative space-y-6">
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

            <div class="bg-zinc-50 p-3 rounded-xl border border-zinc-200 space-y-1 text-xs font-mono text-left">
                <div class="flex justify-between text-zinc-600">
                    <span>Staff ID:</span>
                    <span id="badge-id" class="font-bold text-zinc-900">ID</span>
                </div>
                <div class="flex justify-between text-zinc-600">
                    <span>Contact:</span>
                    <span id="badge-phone" class="font-bold text-zinc-900">—</span>
                </div>
                <div class="flex justify-between text-zinc-600 gap-3">
                    <span class="shrink-0">Email:</span>
                    <span id="badge-email" class="font-bold text-zinc-900 text-right break-all">—</span>
                </div>
                <div class="flex justify-between text-zinc-600 gap-3">
                    <span class="shrink-0">Address:</span>
                    <span id="badge-address" class="font-bold text-zinc-900 text-right">—</span>
                </div>
                <div class="flex justify-between text-zinc-600">
                    <span>Emergency:</span>
                    <span id="badge-emergency" class="font-bold text-zinc-900">—</span>
                </div>
            </div>
        </div>

        <button onclick="window.print()" class="w-full py-3 bg-zinc-900 hover:bg-zinc-800 text-white font-bold rounded-xl transition-all shadow-xs flex items-center justify-center gap-2 cursor-pointer">
            <i data-lucide="printer" class="w-4 h-4"></i>
            <span>PRINT PASS BADGE</span>
        </button>
    </div>
</div>

<!-- Reset PIN Modal -->
<div id="reset-pin-modal" class="modal-backdrop fixed inset-0 z-50 bg-black/50 backdrop-blur-xs hidden items-center justify-center p-4">
    <div class="modal-content bg-white border border-zinc-200 rounded-2xl max-w-md w-full p-6 shadow-xl space-y-6">
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

<!-- Edit Staff Modal -->
<div id="edit-staff-modal" class="modal-backdrop fixed inset-0 z-50 bg-black/50 backdrop-blur-xs hidden items-center justify-center p-4">
    <div class="modal-content bg-white border border-zinc-200 rounded-2xl max-w-md w-full p-6 shadow-xl space-y-6">
        <div class="flex items-center justify-between border-b border-zinc-100 pb-3">
            <h3 class="text-lg font-bold text-zinc-900">Edit Staff Details</h3>
            <button onclick="closeEditModal()" class="p-1 hover:bg-zinc-100 rounded-lg text-zinc-500">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>

        <form onsubmit="handleUpdateStaff(event)" class="space-y-4 text-xs" enctype="multipart/form-data">
            <input type="hidden" id="edit-staff-id" value="">

            <div class="flex flex-col items-center gap-2 pb-2">
                <div class="relative">
                    <div id="edit-avatar-preview" class="w-20 h-20 rounded-2xl bg-zinc-900 text-white flex items-center justify-center text-2xl font-bold overflow-hidden border-2 border-zinc-200">?</div>
                    <label for="edit-avatar-input" class="absolute -bottom-1.5 -right-1.5 w-7 h-7 bg-blue-600 hover:bg-blue-700 text-white rounded-full flex items-center justify-center cursor-pointer shadow-md border-2 border-white">
                        <i data-lucide="camera" class="w-3.5 h-3.5"></i>
                    </label>
                </div>
                <input type="file" id="edit-avatar-input" accept="image/png,image/jpeg,image/webp" class="hidden" onchange="previewEditAvatar(event)">
                <p class="text-[10px] text-zinc-400">Click the camera icon to change photo (JPG/PNG/WEBP, max 2MB)</p>
            </div>

            <div>
                <label class="block font-bold text-zinc-700 uppercase mb-1">Full Name</label>
                <input type="text" id="edit-name" required placeholder="e.g. Sipho Nkosi" class="w-full px-3 py-2 border border-zinc-300 rounded-xl outline-none focus:ring-2 focus:ring-red-500">
            </div>
            <div>
                <label class="block font-bold text-zinc-700 uppercase mb-1">Email Address</label>
                <input type="email" id="edit-email" required placeholder="sipho.n@innow.com" class="w-full px-3 py-2 border border-zinc-300 rounded-xl outline-none focus:ring-2 focus:ring-red-500">
            </div>
            <div>
                <label class="block font-bold text-zinc-700 uppercase mb-1">Phone Number</label>
                <input type="text" id="edit-phone" placeholder="+27 82 000 0000" class="w-full px-3 py-2 border border-zinc-300 rounded-xl outline-none focus:ring-2 focus:ring-red-500">
            </div>
            <div>
                <label class="block font-bold text-zinc-700 uppercase mb-1">Emergency Contact Number</label>
                <input type="text" id="edit-emergency" placeholder="+27 82 000 0000" class="w-full px-3 py-2 border border-zinc-300 rounded-xl outline-none focus:ring-2 focus:ring-red-500">
            </div>
            <div>
                <label class="block font-bold text-zinc-700 uppercase mb-1">Address</label>
                <input type="text" id="edit-address" placeholder="123 Main Rd, Cape Town" class="w-full px-3 py-2 border border-zinc-300 rounded-xl outline-none focus:ring-2 focus:ring-red-500">
            </div>

            <button type="submit" class="w-full py-3 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-xl transition-all cursor-pointer">
                Save Changes
            </button>
        </form>
    </div>
</div>

<script>
    function filterStaffDirectory() {
        const query = document.getElementById('staff-search-input').value.trim().toLowerCase();
        const cards = document.querySelectorAll('#staff-cards-grid .staff-card');
        let visibleCount = 0;

        cards.forEach((card) => {
            const name = card.getAttribute('data-staff-name') || '';
            const matches = name.includes(query);
            card.style.display = matches ? '' : 'none';
            if (matches) visibleCount++;
        });

        document.getElementById('staff-search-empty').classList.toggle('hidden', visibleCount !== 0);
    }

    function openModal(id) {
      const modal = document.getElementById(id);
      modal.classList.replace('hidden', 'flex');
      requestAnimationFrame(() => modal.classList.add('modal-visible'));
    }
    function closeModal(id) {
      const modal = document.getElementById(id);
      modal.classList.remove('modal-visible');
      modal.classList.replace('flex', 'hidden');
    }

    function openAddStaffModal() { openModal('add-staff-modal'); }
    function closeAddStaffModal() { closeModal('add-staff-modal'); }
    function closeBadgeModal() { closeModal('badge-modal'); }

    function viewBadge(jsonStr) {
        const stf = JSON.parse(jsonStr);
        const imgEl = document.getElementById('badge-img-placeholder');
        imgEl.innerHTML = stf.avatar_url
            ? `<img src="${stf.avatar_url}" class="w-full h-full object-cover rounded-2xl">`
            : stf.name.charAt(0).toUpperCase();
        document.getElementById('badge-name').innerText = stf.name;
        document.getElementById('badge-role').innerText = stf.role;
        document.getElementById('badge-dept').innerText = stf.department;
        document.getElementById('badge-id').innerText = stf.id;
        document.getElementById('badge-phone').innerText = stf.phone || '—';
        document.getElementById('badge-email').innerText = stf.email || '—';
        document.getElementById('badge-address').innerText = stf.address || '—';
        document.getElementById('badge-emergency').innerText = stf.emergency_contact || '—';
        openModal('badge-modal');
    }

    async function handleCreateStaff(e) {
        e.preventDefault();
        const body = {
            name: document.getElementById('new-name').value,
            email: document.getElementById('new-email').value,
            pin: document.getElementById('new-pin').value,
            department: document.getElementById('new-dept').value,
            role: document.getElementById('new-role').value,
            phone: document.getElementById('new-phone').value,
            emergency_contact: document.getElementById('new-emergency').value,
            address: document.getElementById('new-address').value,
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
        openModal('reset-pin-modal');
    }

    function closeResetPinModal() {
        closeModal('reset-pin-modal');
    }

    function previewEditAvatar(e) {
        const file = e.target.files[0];
        if (!file) return;
        if (file.size > 2 * 1024 * 1024) {
            alert('Image is too large. Maximum size is 2MB.');
            e.target.value = '';
            return;
        }
        const reader = new FileReader();
        reader.onload = function (ev) {
            document.getElementById('edit-avatar-preview').innerHTML = `<img src="${ev.target.result}" class="w-full h-full object-cover">`;
        };
        reader.readAsDataURL(file);
    }

    function openEditModal(jsonStr) {
        const stf = JSON.parse(jsonStr);
        document.getElementById('edit-staff-id').value = stf.id;
        document.getElementById('edit-name').value = stf.name || '';
        document.getElementById('edit-email').value = stf.email || '';
        document.getElementById('edit-phone').value = stf.phone || '';
        document.getElementById('edit-emergency').value = stf.emergency_contact || '';
        document.getElementById('edit-address').value = stf.address || '';
        document.getElementById('edit-avatar-input').value = '';
        const preview = document.getElementById('edit-avatar-preview');
        preview.innerHTML = stf.avatar_url
            ? `<img src="${stf.avatar_url}" class="w-full h-full object-cover">`
            : (stf.name || '?').charAt(0).toUpperCase();
        openModal('edit-staff-modal');
    }

    function closeEditModal() {
        closeModal('edit-staff-modal');
    }

    async function handleUpdateStaff(e) {
        e.preventDefault();
        const formData = new FormData();
        formData.append('id', document.getElementById('edit-staff-id').value);
        formData.append('name', document.getElementById('edit-name').value);
        formData.append('email', document.getElementById('edit-email').value);
        formData.append('phone', document.getElementById('edit-phone').value);
        formData.append('emergency_contact', document.getElementById('edit-emergency').value);
        formData.append('address', document.getElementById('edit-address').value);
        const avatarFile = document.getElementById('edit-avatar-input').files[0];
        if (avatarFile) {
            formData.append('avatar', avatarFile);
        }

        try {
            const res = await authFetch('/api/staff/update', {
                method: 'POST',
                body: formData
            });
            const data = await res.json();
            if (data.success) {
                closeEditModal();
                window.location.reload();
            } else {
                alert(data.message || 'Failed to update staff details.');
            }
        } catch (e) {
            alert('Network error: ' + (e.message || 'Unknown error'));
        }
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
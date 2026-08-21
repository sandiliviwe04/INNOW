<?php
$pageTitle = "Leave Management — INNOW";
$user = $user ?? null;
$isAdmin = $user && (stripos($user['role'] ?? '', 'admin') !== false || ($user['role'] ?? '') === 'System Administrator');
require __DIR__ . '/../partials/header.php';
require __DIR__ . '/../partials/nav.php';
?>

<main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 flex-1 w-full space-y-8">
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-extrabold text-zinc-900 tracking-tight">Leave Management</h1>
            <p class="text-xs text-zinc-500">Submit leave requests and view your history</p>
        </div>
        <button onclick="openLeaveModal()" class="px-4 py-2.5 bg-red-600 hover:bg-red-700 text-white rounded-xl text-xs font-bold transition-all shadow-md flex items-center gap-2 cursor-pointer">
            <i data-lucide="plus-circle" class="w-4 h-4"></i>
            Request Leave
        </button>

    <div class="bg-white rounded-2xl border border-zinc-200 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs" id="leaves-table">
                <thead class="bg-zinc-50 text-zinc-500 uppercase font-mono border-b border-zinc-200">
                <tr>
                    <th class="py-3 px-4">Employee</th>
                    <th class="py-3 px-4">Leave Type</th>
                    <th class="py-3 px-4">Dates</th>
                    <th class="py-3 px-4">Days</th>
                    <th class="py-3 px-4">Reason</th>
                    <th class="py-3 px-4">Attachment</th>
                    <th class="py-3 px-4">Status</th>
                    <th class="py-3 px-4">Reviewed By</th>
                    <?php if ($isAdmin): ?>
                    <th class="py-3 px-4">Actions</th>
                    <?php endif; ?>
                </tr>
                </thead>
                <tbody class="divide-y divide-zinc-200" id="leaves-tbody">
                    <tr>
                        <td colspan="<?= $isAdmin ? 9 : 8 ?>" class="py-8 text-center text-xs text-zinc-400">Loading leave requests...</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</main>

<!-- Leave Request Modal -->
<div id="leave-modal" class="modal-backdrop fixed inset-0 z-50 bg-black/50 backdrop-blur-xs hidden items-center justify-center p-4">
    <div class="modal-content bg-white border border-zinc-200 rounded-2xl max-w-md w-full p-6 shadow-xl space-y-6">
        <div class="flex items-center justify-between border-b border-zinc-100 pb-3">
            <h3 class="text-lg font-bold text-zinc-900">Request Leave</h3>
            <button onclick="closeLeaveModal()" class="p-1 hover:bg-zinc-100 rounded-lg text-zinc-500">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>

        <form onsubmit="handleLeaveSubmit(event)" class="space-y-4 text-xs">
            <div>
                <label class="block font-bold text-zinc-700 uppercase mb-1">Leave Type</label>
                <select id="leave-type" onchange="toggleLeaveReason()" class="w-full px-3 py-2 border border-zinc-300 rounded-xl outline-none focus:ring-2 focus:ring-red-500">
                    <option value="Annual Leave">Annual Leave</option>
                    <option value="Sick Leave">Sick Leave</option>
                    <option value="Unpaid Leave">Unpaid Leave</option>
                    <option value="Maternity Leave">Maternity Leave</option>
                    <option value="Other">Other</option>
                </select>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block font-bold text-zinc-700 uppercase mb-1">Start Date</label>
                    <input type="date" id="leave-start" required class="w-full px-3 py-2 border border-zinc-300 rounded-xl outline-none focus:ring-2 focus:ring-red-500">
                </div>
                <div>
                    <label class="block font-bold text-zinc-700 uppercase mb-1">End Date</label>
                    <input type="date" id="leave-end" required class="w-full px-3 py-2 border border-zinc-300 rounded-xl outline-none focus:ring-2 focus:ring-red-500">
                </div>
            </div>
            <div>
                <label class="block font-bold text-zinc-700 uppercase mb-1">Number of Days</label>
                <input type="number" id="leave-days" min="1" required class="w-full px-3 py-2 border border-zinc-300 rounded-xl outline-none focus:ring-2 focus:ring-red-500">
            </div>
            <div id="leave-reason-container">
                <label class="block font-bold text-zinc-700 uppercase mb-1">Reason</label>
                <textarea id="leave-reason" rows="3" placeholder="Optional" class="w-full px-3 py-2 border border-zinc-300 rounded-xl outline-none focus:ring-2 focus:ring-red-500"></textarea>
            </div>
            <div>
                <label class="block font-bold text-zinc-700 uppercase mb-1">Attach File (Optional)</label>
                <input type="file" id="leave-attachment" accept="image/png, image/jpeg, application/pdf" class="w-full text-zinc-600 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-zinc-100 file:text-zinc-700 hover:file:bg-zinc-200 cursor-pointer">
            </div>

            <button type="submit" class="w-full py-3 bg-red-600 hover:bg-red-700 text-white font-bold rounded-xl transition-all cursor-pointer">
                Submit Request
            </button>
        </form>
    </div>
</div>

<script>
    function openLeaveModal() {
      const modal = document.getElementById('leave-modal');
      modal.classList.replace('hidden', 'flex');
      requestAnimationFrame(() => modal.classList.add('modal-visible'));
    }
    function closeLeaveModal() {
      const modal = document.getElementById('leave-modal');
      modal.classList.remove('modal-visible');
      modal.classList.replace('flex', 'hidden');
    }

    function toggleLeaveReason() {
        const type = document.getElementById('leave-type').value;
        const container = document.getElementById('leave-reason-container');
        const label = container.querySelector('label');
        const textarea = document.getElementById('leave-reason');
        if (type === 'Other') {
            label.classList.add('text-red-600');
            textarea.placeholder = 'Please specify your reason';
            textarea.required = true;
        } else {
            label.classList.remove('text-red-600');
            textarea.placeholder = 'Optional';
            textarea.required = false;
        }
    }

    async function loadLeaves() {
        try {
            const res = await authFetch('/api/leaves');
            const data = await res.json();
            if (!data.success) {
                document.getElementById('leaves-tbody').innerHTML = `<tr><td colspan="<?= $isAdmin ? 9 : 8 ?>" class="py-8 text-center text-xs text-red-600">${data.message || 'Failed to load leave requests.'}</td></tr>`;
                return;
            }
            const tbody = document.getElementById('leaves-tbody');
            const leaves = data.leaves || [];
            tbody.innerHTML = leaves.map(l => `
                <tr class="hover:bg-zinc-50 transition-colors">
                    <td class="py-3.5 px-4 font-bold text-zinc-900">${l.user_name || 'Unknown'}</td>
                    <td class="py-3.5 px-4">${l.leave_type}</td>
                    <td class="py-3.5 px-4 font-mono">${l.start_date} — ${l.end_date}</td>
                    <td class="py-3.5 px-4">${l.days_requested}</td>
                    <td class="py-3.5 px-4 text-zinc-600">${l.reason || '-'}</td>
                    <td class="py-3.5 px-4">
                        ${l.attachment_path ? `
                            <a href="${l.attachment_path}" target="_blank" class="text-red-600 hover:underline font-bold flex items-center gap-1">
                                <i data-lucide="paperclip" class="w-3 h-3"></i> View
                            </a>` : '-'}
                    </td>
                    <td class="py-3.5 px-4">
                        <span class="px-2 py-1 rounded-full text-[10px] font-bold ${l.status === 'PENDING' ? 'bg-amber-100 text-amber-800' : (l.status === 'APPROVED' ? 'bg-emerald-100 text-emerald-800' : 'bg-red-100 text-red-800')}">
                            ${l.status}
                        </span>
                    </td>
                    <td class="py-3.5 px-4 text-zinc-600">${l.reviewed_by_name || '-'}</td>
                    <?php if ($isAdmin): ?>
                    <td class="py-3.5 px-4">
                        ${l.status === 'PENDING' ? `
                        <div class="flex gap-2">
                            <button onclick="updateLeave('${l.id}', 'APPROVED')" class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-bold cursor-pointer">Approve</button>
                            <button onclick="updateLeave('${l.id}', 'DECLINED')" class="px-3 py-1.5 bg-red-600 hover:bg-red-700 text-white rounded-lg text-xs font-bold cursor-pointer">Decline</button>
                        </div>
                        ` : '-'}
                    </td>
                    <?php endif; ?>
                </tr>
            `).join('');
            if (window.lucide) lucide.createIcons();
        } catch (e) {
            document.getElementById('leaves-tbody').innerHTML = `<tr><td colspan="<?= $isAdmin ? 9 : 8 ?>" class="py-8 text-center text-xs text-red-600">Failed to load leave requests: ${e.message}</td></tr>`;
        }
    }

    async function handleLeaveSubmit(e) {
        e.preventDefault();
        const leaveType = document.getElementById('leave-type').value;
        const startDate = document.getElementById('leave-start').value;
        const endDate = document.getElementById('leave-end').value;
        const daysRequested = parseInt(document.getElementById('leave-days').value) || 0;
        const reason = document.getElementById('leave-reason').value;
        const attachmentFile = document.getElementById('leave-attachment').files[0];

        if (leaveType === 'Other' && !reason.trim()) {
            alert('Please provide a reason for your leave request.');
            return;
        }

        const formData = new FormData();
        formData.append('leave_type', leaveType);
        formData.append('start_date', startDate);
        formData.append('end_date', endDate);
        formData.append('days_requested', daysRequested);
        formData.append('reason', reason);
        if (attachmentFile) {
            formData.append('attachment', attachmentFile);
        }

        try {
            // Use fetch directly to avoid authFetch's default JSON content-type
            const res = await fetch('/api/leaves', {
                method: 'POST',
                body: formData,
                headers: { 'X-CSRF-Token': getCsrfToken() }
            });
            const data = await res.json();
            if (data.success) {
                closeLeaveModal();
                loadLeaves();
            } else {
                alert(data.message || 'Failed to submit leave request.');
            }
        } catch (e) {
            alert('Error submitting leave request: ' + e.message);
        }
    }

    async function updateLeave(id, status) {
        if (!confirm(`Are you sure you want to ${status.toLowerCase()} this leave request?`)) return;
        try {
            const res = await authFetch('/api/leaves/update', {
                method: 'POST',
                body: JSON.stringify({ id, status })
            });
            const data = await res.json();
            if (data.success) {
                loadLeaves();
            } else {
                alert(data.message || 'Failed to update leave request.');
            }
        } catch (e) {
            alert('Error updating leave request: ' + e.message);
        }
    }

    loadLeaves();
</script>

<?php require __DIR__ . '/../partials/footer.php'; ?>
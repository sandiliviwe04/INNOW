<?php
$pageTitle = "Announcements — INNOW";
$user = $user ?? null;
$isAdmin = $user && (stripos($user['role'] ?? '', 'admin') !== false || ($user['role'] ?? '') === 'System Administrator');
require __DIR__ . '/../partials/header.php';
require __DIR__ . '/../partials/nav.php';
$currentUserId = $user['user_id'] ?? '';
?>

<main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 flex-1 w-full space-y-8">
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-extrabold text-zinc-900 tracking-tight">Announcements</h1>
            <p class="text-xs text-zinc-500">Company-wide updates and notices</p>
        </div>
        <button onclick="openAnnouncementModal()" class="px-4 py-2.5 bg-red-600 hover:bg-red-700 text-white rounded-xl text-xs font-bold transition-all shadow-md flex items-center gap-2 cursor-pointer">
            <i data-lucide="plus-circle" class="w-4 h-4"></i>
            New Announcement
        </button>
    </div>

    <div class="space-y-4" id="announcements-list">
        <div class="text-center text-xs text-zinc-400 py-8">Loading announcements...</div>
    </div>
</main>

<!-- Announcement Modal -->
<div id="announcement-modal" class="modal-backdrop fixed inset-0 z-50 bg-black/50 backdrop-blur-xs hidden items-center justify-center p-4">
    <div class="modal-content bg-white border border-zinc-200 rounded-2xl max-w-lg w-full p-6 shadow-xl space-y-6">
        <div class="flex items-center justify-between border-b border-zinc-100 pb-3">
            <h3 class="text-lg font-bold text-zinc-900">Create Announcement</h3>
            <button onclick="closeAnnouncementModal()" class="p-1 hover:bg-zinc-100 rounded-lg text-zinc-500">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>

        <form onsubmit="handleAnnouncementSubmit(event)" class="space-y-4 text-xs">
            <div>
                <label class="block font-bold text-zinc-700 uppercase mb-1">Title</label>
                <input type="text" id="ann-title" required placeholder="e.g. Office Closure" class="w-full px-3 py-2 border border-zinc-300 rounded-xl outline-none focus:ring-2 focus:ring-red-500">
            </div>
            <div>
                <label class="block font-bold text-zinc-700 uppercase mb-1">Message</label>
                <textarea id="ann-message" rows="4" required placeholder="Write your announcement here..." class="w-full px-3 py-2 border border-zinc-300 rounded-xl outline-none focus:ring-2 focus:ring-red-500"></textarea>
            </div>

            <button type="submit" class="w-full py-3 bg-red-600 hover:bg-red-700 text-white font-bold rounded-xl transition-all cursor-pointer">
                Post Announcement
            </button>
        </form>
    </div>
</div>

<script>
    const currentUserId = <?= json_encode($currentUserId) ?>;
    const isAdmin = <?= json_encode($isAdmin) ?>;

    function openAnnouncementModal() {
      const modal = document.getElementById('announcement-modal');
      modal.classList.replace('hidden', 'flex');
      requestAnimationFrame(() => modal.classList.add('modal-visible'));
    }
    function closeAnnouncementModal() {
      const modal = document.getElementById('announcement-modal');
      modal.classList.remove('modal-visible');
      modal.classList.replace('flex', 'hidden');
    }

    async function loadAnnouncements() {
        try {
            const res = await authFetch('/api/announcements');
            const data = await res.json();
            if (!data.success) {
                document.getElementById('announcements-list').innerHTML = `<div class="text-center text-xs text-red-600 py-8">${data.message || 'Failed to load announcements.'}</div>`;
                return;
            }
            const list = document.getElementById('announcements-list');
            const announcements = data.announcements || [];
            if (announcements.length === 0) {
                list.innerHTML = '<div class="text-center text-xs text-zinc-400 py-8">No announcements yet.</div>';
                return;
            }
            list.innerHTML = announcements.map(a => {
                const canDelete = isAdmin || currentUserId === a.user_id;
                return `
                <div class="bg-white rounded-2xl border border-zinc-200 p-6 shadow-xs space-y-3">
                    <div class="flex items-start justify-between">
                        <div>
                            <h3 class="text-base font-extrabold text-zinc-900">${a.title}</h3>
                            <p class="text-[11px] text-zinc-500">By ${a.user_name} • ${new Date(a.created_at).toLocaleString()}</p>
                        </div>
                        ${canDelete ? `
                        <button onclick="deleteAnnouncement('${a.id}')" class="p-1.5 hover:bg-red-50 rounded-lg text-red-600 cursor-pointer">
                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                        </button>` : ''}
                    </div>
                    <p class="text-sm text-zinc-700 whitespace-pre-wrap">${a.message}</p>
                </div>
            `}).join('');
            if (window.lucide) lucide.createIcons();
        } catch (e) {
            document.getElementById('announcements-list').innerHTML = `<div class="text-center text-xs text-red-600 py-8">Failed to load announcements: ${e.message}</div>`;
        }
    }

    async function handleAnnouncementSubmit(e) {
        e.preventDefault();
        const title = document.getElementById('ann-title').value;
        const message = document.getElementById('ann-message').value;

        try {
            const res = await authFetch('/api/announcements', { method: 'POST', body: JSON.stringify({ title, message }) });
            const data = await res.json();
            if (data.success) {
                closeAnnouncementModal();
                loadAnnouncements();
            } else {
                alert(data.message || 'Failed to post announcement.');
            }
        } catch (e) {
            alert('Error posting announcement.');
        }
    }

    async function deleteAnnouncement(id) {
        if (!confirm('Delete this announcement?')) return;
        try {
            const res = await authFetch('/api/announcements/delete', { method: 'POST', body: JSON.stringify({ id }) });
            const data = await res.json();
            if (data.success) {
                loadAnnouncements();
            } else {
                alert(data.message || 'Failed to delete announcement.');
            }
        } catch (e) {
            alert('Error deleting announcement: ' + e.message);
        }
    }

    loadAnnouncements();
</script>

<?php require __DIR__ . '/../partials/footer.php'; ?>

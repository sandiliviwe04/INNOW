<?php
require_once __DIR__ . '/../partials/header.php';
/**
 * @var array $user The current logged-in user.
 * @var string $csrfToken The CSRF token.
 * @var array $allLogs All attendance logs.
 * @var array $allStaff All staff members.
 */
?>

<div class="container mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-800">Attendance Audit Trail</h1>
            <p class="mt-1 text-gray-600">Immutable history of check-ins, check-outs, breaks, and database records.</p>
        </div>
    </div>

    <!-- Action Buttons -->
    <div class="flex justify-end items-center space-x-2 mb-6">
        <!-- Import CSV Button -->
        <button id="import-csv-btn" class="bg-gray-700 hover:bg-gray-800 text-white font-bold py-2 px-4 rounded inline-flex items-center">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path></svg>
            <span>Import CSV</span>
        </button>
        <input type="file" id="csv-file-input" class="hidden" accept=".csv">

        <!-- Export CSV Button (assuming it exists) -->
        <button id="export-csv-btn" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded inline-flex items-center">
             <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
            <span>Export CSV</span>
        </button>

        <!-- Manual Entry Button (assuming it exists) -->
        <button id="manual-entry-btn" class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded inline-flex items-center">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
            <span>+ Manual Admin Entry</span>
        </button>
    </div>

    <!-- Notification Area -->
    <div id="notification" class="hidden rounded-md p-4 mb-4"></div>

    <!-- Logs Table -->
    <div class="bg-white shadow-md rounded-lg overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200" id="logs-table">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Log ID</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Staff Member</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Event Action</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Method</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Timestamp (SAST)</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Storage Status</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach ($allLogs as $log): ?>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?= htmlspecialchars($log['id']) ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"><?= htmlspecialchars($log['staff_name']) ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?= htmlspecialchars($log['action']) ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?= htmlspecialchars($log['method']) ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?= htmlspecialchars($log['timestamp']) ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-green-600 font-semibold">STORED</td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const importBtn = document.getElementById('import-csv-btn');
    const fileInput = document.getElementById('csv-file-input');
    const notification = document.getElementById('notification');

    importBtn.addEventListener('click', () => {
        fileInput.click();
    });

    fileInput.addEventListener('change', (event) => {
        const file = event.target.files[0];
        if (!file) {
            return;
        }

        if (file.type !== 'text/csv' && !file.name.endsWith('.csv')) {
            showNotification('Please select a .csv file.', 'error');
            return;
        }

        uploadFile(file);
    });

    function uploadFile(file) {
        const formData = new FormData();
        formData.append('csv_file', file);
        formData.append('csrf_token', '<?= $csrfToken ?>');

        showNotification('Uploading and processing CSV... Please wait.', 'info');
        importBtn.disabled = true;
        importBtn.classList.add('opacity-50', 'cursor-not-allowed');

        fetch('/api/attendance/import-csv', {
            method: 'POST',
            body: formData,
            headers: {
                'Accept': 'application/json',
            },
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showNotification(data.message || 'CSV imported successfully!', 'success');
                // Refresh the page to show new logs after a short delay
                setTimeout(() => window.location.reload(), 2000);
            } else {
                let errorMessage = data.message || 'An error occurred during import.';
                if (data.data && data.data.errors) {
                    errorMessage += '<ul>' + data.data.errors.map(e => `<li>${e}</li>`).join('') + '</ul>';
                }
                showNotification(errorMessage, 'error');
            }
        })
        .catch(error => {
            console.error('Upload error:', error);
            showNotification('A network error occurred. Please try again.', 'error');
        })
        .finally(() => {
            fileInput.value = ''; // Reset file input
            importBtn.disabled = false;
            importBtn.classList.remove('opacity-50', 'cursor-not-allowed');
        });
    }

    function showNotification(message, type) {
        notification.innerHTML = message;
        notification.className = 'p-4 mb-4 rounded-md ';
        if (type === 'success') {
            notification.classList.add('bg-green-100', 'text-green-800');
        } else if (type === 'error') {
            notification.classList.add('bg-red-100', 'text-red-800');
        } else {
            notification.classList.add('bg-blue-100', 'text-blue-800');
        }
        notification.classList.remove('hidden');
    }
});
</script>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
```

These changes introduce the complete workflow for importing attendance records. An admin can now click the "Import CSV" button, select a file, and the system will securely process it and update the database, refreshing the log view upon success.

Let me know if you have any other questions!

<!--
[PROMPT_SUGGESTION]Now, can you implement the "Export CSV" functionality?[/PROMPT_SUGGESTION]
[PROMPT_SUGGESTION]How would I add server-side pagination to the Attendance Audit Trail page?[/PROMPT_SUGGESTION]
-->
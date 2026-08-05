<?php
$pageTitle = "Documentation — INNOW";
require __DIR__ . '/../partials/header.php';
require __DIR__ . '/../partials/nav.php';

$activeDoc = $_GET['doc'] ?? 'user-guide';
$docPath = __DIR__ . '/../../../backend/docs/' . $activeDoc . '.md';
$docContent = file_exists($docPath) ? file_get_contents($docPath) : "# Document not found\nThe file {$activeDoc}.md does not exist in docs/.";
?>

<main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 flex-1 w-full space-y-8">
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-extrabold text-zinc-900 tracking-tight">Project Deliverables & Documentation</h1>
            <p class="text-xs text-zinc-500">Project Brief Case Study, User Guide, API Reference, and Deployment Manual</p>
        </div>

        <!-- Doc Selector Tabs -->
        <div class="flex items-center gap-1 bg-zinc-200/80 p-1 rounded-xl">
            <a href="/docs?doc=local-vscode-setup" class="px-3 py-1.5 rounded-lg text-xs font-bold transition-all <?= $activeDoc === 'local-vscode-setup' ? 'bg-white shadow-xs text-zinc-900' : 'text-zinc-600 hover:text-zinc-900' ?>">VS Code Setup</a>
            <a href="/docs?doc=mysql-workbench-guide" class="px-3 py-1.5 rounded-lg text-xs font-bold transition-all <?= $activeDoc === 'mysql-workbench-guide' ? 'bg-white shadow-xs text-zinc-900' : 'text-zinc-600 hover:text-zinc-900' ?>">MySQL Workbench</a>
            <a href="/docs?doc=user-guide" class="px-3 py-1.5 rounded-lg text-xs font-bold transition-all <?= $activeDoc === 'user-guide' ? 'bg-white shadow-xs text-zinc-900' : 'text-zinc-600 hover:text-zinc-900' ?>">User Guide</a>
            <a href="/docs?doc=case-study" class="px-3 py-1.5 rounded-lg text-xs font-bold transition-all <?= $activeDoc === 'case-study' ? 'bg-white shadow-xs text-zinc-900' : 'text-zinc-600 hover:text-zinc-900' ?>">Case Study</a>
            <a href="/docs?doc=api-documentation" class="px-3 py-1.5 rounded-lg text-xs font-bold transition-all <?= $activeDoc === 'api-documentation' ? 'bg-white shadow-xs text-zinc-900' : 'text-zinc-600 hover:text-zinc-900' ?>">API Reference</a>
            <a href="/docs?doc=deployment" class="px-3 py-1.5 rounded-lg text-xs font-bold transition-all <?= $activeDoc === 'deployment' ? 'bg-white shadow-xs text-zinc-900' : 'text-zinc-600 hover:text-zinc-900' ?>">Deployment</a>
        </div>
    </div>

    <!-- Markdown Content Viewer -->
    <div class="bg-white rounded-2xl border border-zinc-200 p-8 shadow-xs prose max-w-none">
        <div id="markdown-render" class="space-y-4 text-sm text-zinc-800 leading-relaxed font-sans">
            <!-- Rendered via Marked.js -->
        </div>
    </div>
</main>

<!-- Marked JS for Markdown rendering -->
<script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
<script>
    const rawMarkdown = <?= json_encode($docContent) ?>;
    document.getElementById('markdown-render').innerHTML = marked.parse(rawMarkdown);
</script>

<?php require __DIR__ . '/../partials/footer.php'; ?>
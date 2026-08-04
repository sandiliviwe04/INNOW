<footer class="bg-white border-t border-zinc-200 mt-auto py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col md:flex-row items-center justify-between gap-4 text-xs text-zinc-500">
        <div class="flex items-center gap-2">
            <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
            <span class="font-medium text-zinc-700">INNOW Backend Active (PHP WASM Engine)</span>
            <span>•</span>
            <span>INNOW Facility, Cape Town</span>
        </div>
        <div class="flex items-center gap-4">
            <span id="live-time-clock" class="font-mono font-bold text-zinc-900"></span>
            <span>•</span>
            <span>MySQL Database Engine: <strong class="text-emerald-600 font-bold">ACTIVE</strong></span>
        </div>
    </div>
</footer>

<script>
    // Initialize Lucide Icons
    if (window.lucide) {
        lucide.createIcons();
    }

    // Live Clock Update
    function updateLiveClock() {
        const el = document.getElementById('live-time-clock');
        if (el) {
            const now = new Date();
            el.innerText = now.toLocaleTimeString('en-ZA', { hour: '2-digit', minute: '2-digit', second: '2-digit' }) + ' SAST';
        }
    }
    setInterval(updateLiveClock, 1000);
    updateLiveClock();
</script>
</body>
</html>

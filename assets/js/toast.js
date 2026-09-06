// ============================================================
// toast.js
// Shared bubble notification + beep sound, used across every
// admin page. Include this once (via admin_header.php) and
// call showToast(message, type) anywhere - 'error' or 'success'.
// ============================================================

function playBeep(type) {
    try {
        const ctx = new (window.AudioContext || window.webkitAudioContext)();
        const osc = ctx.createOscillator();
        const gain = ctx.createGain();
        osc.connect(gain);
        gain.connect(ctx.destination);
        osc.frequency.value = type === 'error' ? 320 : 880;
        gain.gain.setValueAtTime(0.18, ctx.currentTime);
        gain.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + 0.25);
        osc.start();
        osc.stop(ctx.currentTime + 0.25);
    } catch (e) { /* audio not available - silently skip */ }
}

function showToast(message, type = 'error') {
    playBeep(type);
    const toast = document.createElement('div');
    toast.className = 'toast-bubble toast-' + type;
    toast.innerHTML = '<span class="toast-icon">' + (type === 'error' ? '⚠️' : '✅') + '</span><span>' + message + '</span>';
    document.body.appendChild(toast);
    requestAnimationFrame(() => toast.classList.add('show'));
    setTimeout(() => {
        toast.classList.remove('show');
        setTimeout(() => toast.remove(), 300);
    }, 3200);
}

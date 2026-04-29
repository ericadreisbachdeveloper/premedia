const elem = document.getElementById('us-map');
const isTouchDevice = window.matchMedia('(pointer: coarse)').matches;
const isMac = navigator.userAgent.includes('Mac') && !navigator.userAgent.includes('Mobile');
const step = isTouchDevice ? 0.5 : 0.1;

const instance = Panzoom(elem, {
    maxScale: 2,
    minScale: 1,
    step,
    // Disable Panzoom's built-in touch handling entirely on mobile
    // We will drive pan/zoom manually via touch events below
    disablePan: isTouchDevice,
    disableZoom: isTouchDevice,
});

elem._panzoomInstance = instance;

// --- Overlay ---
const hint = document.createElement('div');
hint.id = 'map-scroll-hint';
Object.assign(hint.style, {
    position: 'absolute',
    inset: '0',
    display: 'flex',
    alignItems: 'center',
    justifyContent: 'center',
    background: 'rgba(0,0,0,0.45)',
    color: '#fff',
    fontSize: '16px',
    fontFamily: 'sans-serif',
    opacity: '0',
    transition: 'opacity 0.2s',
    pointerEvents: 'none',
    borderRadius: 'inherit',
    zIndex: '10',
});
elem.parentElement.style.position = 'relative';
elem.parentElement.appendChild(hint);

let hideTimer;
function showHint(message) {
    hint.textContent = message;
    hint.style.opacity = '1';
    clearTimeout(hideTimer);
    hideTimer = setTimeout(() => {
        hint.style.opacity = '0';
    }, 1500);
}

if (isTouchDevice) {
    let lastTouchX = 0, lastTouchY = 0;
    let lastPinchDist = null;

    elem.addEventListener('touchstart', (e) => {
        if (e.touches.length === 1) {
            // Single finger — record position but don't pan
            lastTouchX = e.touches[0].clientX;
            lastTouchY = e.touches[0].clientY;
            lastPinchDist = null;
        } else if (e.touches.length === 2) {
            // Two fingers — record pinch distance
            lastPinchDist = Math.hypot(
                e.touches[0].clientX - e.touches[1].clientX,
                e.touches[0].clientY - e.touches[1].clientY
            );
            // Also record midpoint for panning during pinch
            lastTouchX = (e.touches[0].clientX + e.touches[1].clientX) / 2;
            lastTouchY = (e.touches[0].clientY + e.touches[1].clientY) / 2;
        }
    }, { passive: true });

    elem.addEventListener('touchmove', (e) => {
        if (e.touches.length === 1) {
            // Single finger — show hint, do nothing else
            showHint('Use two fingers to move the map');
            return;
        }

        if (e.touches.length === 2) {
            e.preventDefault(); // prevent page scroll during two-finger gesture

            const newDist = Math.hypot(
                e.touches[0].clientX - e.touches[1].clientX,
                e.touches[0].clientY - e.touches[1].clientY
            );
            const midX = (e.touches[0].clientX + e.touches[1].clientX) / 2;
            const midY = (e.touches[0].clientY + e.touches[1].clientY) / 2;

            // Pan
            const dx = midX - lastTouchX;
            const dy = midY - lastTouchY;
            if (dx !== 0 || dy !== 0) {
                instance.pan(dx, dy, { relative: true, force: true });
            }

            // Pinch zoom
            if (lastPinchDist) {
                const currentScale = instance.getScale();
                const newScale = Math.min(2, Math.max(1, currentScale * (newDist / lastPinchDist)));
                instance.zoom(newScale, { force: true });
            }

            lastTouchX = midX;
            lastTouchY = midY;
            lastPinchDist = newDist;
        }
    }, { passive: false }); // passive: false needed for e.preventDefault()

    elem.addEventListener('touchend', (e) => {
        lastPinchDist = null;
        if (e.touches.length === 1) {
            lastTouchX = e.touches[0].clientX;
            lastTouchY = e.touches[0].clientY;
        }
    }, { passive: true });

} else {
    elem.parentElement.addEventListener('wheel', (e) => {
        const modifierHeld = isMac ? e.metaKey : e.ctrlKey;
        if (modifierHeld) {
            instance.zoomWithWheel(e);
        } else {
            showHint(isMac ? 'Use ⌘ + scroll to zoom the map' : 'Use Ctrl + scroll to zoom the map');
        }
    });
}
const elem = document.getElementById('us-map');
const isTouchDevice = window.matchMedia('(pointer: coarse)').matches;
const isMac = navigator.userAgent.includes('Mac') && !navigator.userAgent.includes('Mobile');
const step = isTouchDevice ? 0.5 : 0.1;

const instance = Panzoom(elem, {
    maxScale: 2,
    minScale: 1,
    step,
    handleStartEvent: (e) => {
        if (e.touches && e.touches.length < 2) {
            return;
        }
        e.preventDefault();
        e.stopPropagation();
    },
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
    elem.parentElement.addEventListener('touchmove', (e) => {
        if (e.touches.length === 1) {
            showHint('Use two fingers to move the map');
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
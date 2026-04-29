const elem = document.getElementById('us-map');
const isTouchDevice = window.matchMedia('(pointer: coarse)').matches;
const isMac = navigator.userAgent.includes('Mac') && !navigator.userAgent.includes('Mobile');
const step = isTouchDevice ? 0.5 : 0.1;

// Track active touches BEFORE Panzoom initialises
let activePointers = new Set();
let hintTimer;

const instance = Panzoom(elem, {
    maxScale: 2,
    minScale: 1,
    step,
    // Leave touchAction at default ('none') so Panzoom owns touch events
    // We'll gate single-finger moves ourselves via handleStartEvent
    handleStartEvent: (e) => {
        // For touch, only let Panzoom proceed if 2+ fingers are down
        if (e.pointerType === 'touch' && activePointers.size < 2) {
            // Don't preventDefault — let the browser scroll the page
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
    // Maintain pointer count BEFORE Panzoom's own handlers fire (capture phase)
    elem.addEventListener('pointerdown', (e) => {
        activePointers.add(e.pointerId);
    }, { capture: true, passive: true });

    elem.addEventListener('pointerup', (e) => {
        activePointers.delete(e.pointerId);
    }, { capture: true, passive: true });

    elem.addEventListener('pointercancel', (e) => {
        activePointers.delete(e.pointerId);
    }, { capture: true, passive: true });

    // Show hint on single-finger move, debounced to avoid false positives
    // when second finger is just about to land
    elem.addEventListener('pointermove', (e) => {
        if (e.pointerType !== 'touch') return;
        if (activePointers.size === 1) {
            clearTimeout(hintTimer);
            hintTimer = setTimeout(() => {
                if (activePointers.size === 1) {
                    showHint('Use two fingers to move the map');
                }
            }, 80);
        } else {
            clearTimeout(hintTimer);
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
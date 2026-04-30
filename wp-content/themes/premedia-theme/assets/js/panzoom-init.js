const elem = document.getElementById('us-map');
const isTouchDevice = window.matchMedia('(pointer: coarse)').matches;
const isMac = navigator.userAgent.includes('Mac') && !navigator.userAgent.includes('Mobile');
const step = isTouchDevice ? 0.5 : 0.05;

const instance = Panzoom(elem, {
    maxScale: 3,
    minScale: 1,
    step,
    duration: 300,        // milliseconds, default is 200
    easing: 'ease-in-out', // any valid CSS easing, default is 'ease-in-out'
    disablePan: isTouchDevice,
    disableZoom: isTouchDevice,
});

// Override anything Panzoom sets on the parent that blocks page scroll
elem.parentElement.style.overflow = 'visible';
elem.parentElement.style.touchAction = 'auto';

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
let twoFingerCooldown = false;
let cooldownTimer;

function showHint(message) {
    if (twoFingerCooldown) return; // suppress hint briefly after two-finger gesture
    hint.textContent = message;
    hint.style.opacity = '1';
    clearTimeout(hideTimer);
    hideTimer = setTimeout(() => {
        hint.style.opacity = '0';
    }, 1500);
}

function hideHint() {
    clearTimeout(hideTimer);
    hint.style.opacity = '0';
}

// hide hint if it displays when user clicks / touches 
const onPointerDown = () => hideHint();

[elem, elem.parentElement].forEach(el => {
    el.addEventListener('pointerdown', onPointerDown);
});

function startCooldown() {
    twoFingerCooldown = true;
    clearTimeout(cooldownTimer);
    cooldownTimer = setTimeout(() => {
        twoFingerCooldown = false;
    }, 400); // 400ms after last two-finger move, hint is allowed again
}

if (isTouchDevice) {
    let lastTouchX = 0, lastTouchY = 0;
    let lastPinchDist = null;

    elem.addEventListener('touchstart', (e) => {
        if (e.touches.length === 1) {
            lastTouchX = e.touches[0].clientX;
            lastTouchY = e.touches[0].clientY;
            lastPinchDist = null;
        } else if (e.touches.length === 2) {
            lastPinchDist = Math.hypot(
                e.touches[0].clientX - e.touches[1].clientX,
                e.touches[0].clientY - e.touches[1].clientY
            );
            lastTouchX = (e.touches[0].clientX + e.touches[1].clientX) / 2;
            lastTouchY = (e.touches[0].clientY + e.touches[1].clientY) / 2;
        }
    }, { passive: true });

    elem.addEventListener('touchmove', (e) => {
        if (e.touches.length === 1) {
            showHint('Use two fingers to move and zoom the map');
            return;
        }

        if (e.touches.length === 2) {
            e.preventDefault();
            hideHint();
            startCooldown(); // reset cooldown on every two-finger frame

            const newDist = Math.hypot(
                e.touches[0].clientX - e.touches[1].clientX,
                e.touches[0].clientY - e.touches[1].clientY
            );
            const midX = (e.touches[0].clientX + e.touches[1].clientX) / 2;
            const midY = (e.touches[0].clientY + e.touches[1].clientY) / 2;

            const dx = midX - lastTouchX;
            const dy = midY - lastTouchY;
            if (dx !== 0 || dy !== 0) {
                instance.pan(dx, dy, { relative: true, force: true });
            }

            if (lastPinchDist) {
                const currentScale = instance.getScale();
                const newScale = Math.min(2, Math.max(1, currentScale * (newDist / lastPinchDist)));
                instance.zoom(newScale, { force: true });
            }

            lastTouchX = midX;
            lastTouchY = midY;
            lastPinchDist = newDist;
        }
    }, { passive: false });

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
            hideHint();
            instance.zoomWithWheel(e);
        } else {
            showHint(isMac ? 'Use ⌘ + scroll to zoom the map' : 'Use Ctrl + scroll to zoom the map');
        }
    });
}


const PAN_STEP = 100; // pixels per button press

document.getElementById('map-zoom-in').addEventListener('click', () => {
    instance.zoomIn({step: .5});
});

document.getElementById('map-zoom-out').addEventListener('click', () => {
    instance.zoomOut({step: .5});
});

document.getElementById('map-pan-up').addEventListener('click', () => {
    instance.pan(0, PAN_STEP, { relative: true, force: true, animate: true });
});

document.getElementById('map-pan-down').addEventListener('click', () => {
    instance.pan(0, -PAN_STEP, { relative: true, force: true, animate: true });
});

document.getElementById('map-pan-left').addEventListener('click', () => {
    instance.pan(PAN_STEP, 0, { relative: true, force: true, animate: true });
});

document.getElementById('map-pan-right').addEventListener('click', () => {
    instance.pan(-PAN_STEP, 0, { relative: true, force: true, animate: true });
});

document.getElementById('map-reset').addEventListener('click', () => {
    instance.reset();
});
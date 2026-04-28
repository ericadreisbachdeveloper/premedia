
const isTouchDevice = window.matchMedia('(pointer: coarse)').matches;
const step = isTouchDevice ? 0.55 : 0.07;

const elem = document.getElementById('us-map');
const instance = Panzoom(elem, {  // note: capital P in v4
    maxScale: 3,
    minScale: .8,
    step,  // controls how much each wheel tick zooms
});

elem.parentElement.addEventListener('wheel', instance.zoomWithWheel);
elem._panzoomInstance = instance; // more specific key


// Diagnostic
// console.log('panzoom instance:', instance);
// console.log('available methods:', Object.keys(instance));
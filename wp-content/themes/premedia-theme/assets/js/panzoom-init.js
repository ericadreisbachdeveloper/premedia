const elem = document.getElementById('us-map');
const instance = Panzoom(elem, {  // note: capital P in v4
    maxScale: 3,
    minScale: .8,
    step: 0.4,  // controls how much each wheel tick zooms
});

elem.parentElement.addEventListener('wheel', instance.zoomWithWheel);
elem._panzoomInstance = instance; // more specific key


// Diagnostic
// console.log('panzoom instance:', instance);
// console.log('available methods:', Object.keys(instance));
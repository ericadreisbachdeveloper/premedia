const elem = document.getElementById(`us-map`);
const instance = panzoom(elem, {
    maxScale: 5, 
    minScale: .5
});
elem.parentElement.addEventListener(`wheel`, instance.zoomWithWheel);

elem._panzoomInstance = instance;  // more specific key


// Diagnostic
// console.log('panzoom instance:', instance);
// console.log('available methods:', Object.keys(instance));
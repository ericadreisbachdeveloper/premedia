const elem = document.getElementById('us-map');
const instance = panzoom(elem, {
    maxScale: 5
});

elem.parentElement.addEventListener('wheel', instance.zoomWithWheel);
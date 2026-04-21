// ref: https://github.com/bumbu/svg-pan-zoom 

var panZoomTiger = svgPanZoom(`#us-map`, {
    minZoom: 0.5,
    maxZoom: 10
});

panZoomTiger.fit();
panZoomTiger.center();
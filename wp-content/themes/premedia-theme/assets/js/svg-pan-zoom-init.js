// ref: https://github.com/bumbu/svg-pan-zoom 

var panZoomTiger = svgPanZoom(`#us-map`, {
    minZoom: .5,
    maxZoom: 1.5
});

panZoomTiger.fit();
panZoomTiger.center();
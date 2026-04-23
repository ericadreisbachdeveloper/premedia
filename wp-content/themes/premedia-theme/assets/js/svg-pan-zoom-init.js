// ref: https://github.com/bumbu/svg-pan-zoom 

var panZoomTiger = svgPanZoom(`#us-map`, {
    minZoom: .5,
    maxZoom: 3
});

panZoomTiger.fit();
panZoomTiger.center();
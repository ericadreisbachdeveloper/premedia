// ref: https://github.com/bumbu/svg-pan-zoom 

var panZoomTiger = svgPanZoom(`#us-map`, {
    minZoom: .5,
    maxZoom: 4
});

panZoomTiger.fit();
panZoomTiger.center();
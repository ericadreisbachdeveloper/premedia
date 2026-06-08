# PREMEDIA theme 
This is a Gutenberg-native theme modeled from WordPress's Twenty Twenty Five theme. Page templates used: 
- `templates/index.html` - Homepage 
- `templates/locations.html` - Locations page with SVG map
- `templates/page.html` - All other pages 

## Locations page 
The Locations page includes a simple SVG map with minimal information to discourage direct contact with physician investigators. Location text data is loaded from an ACF repeater field in the page editor. 

Map SVG image assets, Panzoom JavaScript helpers for mouse and mousepad control, and customized schema are generated in `inc/shortcode-map.php`. Keyboard-accessible zoom and pan buttons are powered by `assets/js/map-min.js`. 

All clinical sites are included within the parent map SVG to keep Panzoom scope intact. 


## Assets 


### Styles 
Main theme stylesheet is located in `assets/css/style.css` composed from partials in `assets/sass/__style.css` 

Wherever possible, styles are updated from the WordPress admin interface and via settings in `theme.json`. 


### Fonts 
Merriweather and Roboto font files are served from the theme rather than Google Cloud. Font files have been optimized by restricting to Latin character set and compressed into .woff2 format. 




# Changelog 

## 1.0.1 - 
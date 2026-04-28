=== Schema Faq Block ===
Contributors:      Claude, erica dreisbach
Tags:              block
Tested up to:      6.8
Stable tag:        0.1.0
License:           GPL-2.0-or-later
License URI:       https://www.gnu.org/licenses/gpl-2.0.html

(c) 2026 

== Description ==

This is a modification of the native Gutenberg accordion block to add FAQ Schema markup. 

== Installation ==

Normal WordPress plugin installation applies. Enable and disable from the WordPress Plugins section. 

Add the block to a given page or post using the Page edior. 


== Changelog ==

= 0.1.0 =
* Release


== Initial Development + Ongoing Updates ==

Initial command: 

$ npx @wordpress/create-block schema-faq-block --template @wordpress/create-block-tutorial-template


Modifications to the plugin should run while NPM runs in the background before deploying. 

$ npm run build 


Only deploy /build/ and /schema-faq-block.php to Production. 

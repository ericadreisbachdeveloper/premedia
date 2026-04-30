/* jshint esversion: 6, expr: true, -W033 */

// see also: inc/shortcode-map.php - where $clinical_site_info array is built from Advanced Custom Fields 'sites' repeater field 
// access ACF data via clinicData[clinical_site_info][CLINICAL_SITE_SLUG] 

document.addEventListener(`DOMContentLoaded`,function(){    


    // Constants used throughout
    const wpAdminBar = document.getElementById(`wpadminbar`); 
    const modal = document.getElementById(`data-modal`); 


    // Helper function for admin bar 
    // sets #wpadminbar[inert] upon any map interaction 
    function setAdminBarInert(value) {
        if (wpAdminBar) {
            wpAdminBar.inert = value;
        }
    }

    // Special CASE for Case Western and Case MetroHealth
    const metroPath = document.getElementById(`case-western-metrohealth`); 

        const metroSvg = metroPath.closest(`g`); 
    
    const casePath = document.getElementById(`case-western-university`); 

        const caseSvg = casePath.closest(`g`); 
    

    // metro (behind) on hover moves ahead of case
    if(metroPath && casePath) {

        metroPath.addEventListener(`mouseenter`, function() {
            metroSvg.parentNode.insertBefore(caseSvg, metroSvg);
        }); 
   
        casePath.addEventListener(`mouseenter`, function() {
            metroSvg.parentNode.insertBefore(metroSvg, caseSvg);
        }); 
     
    }   


    // Keyboard focus trap for modal
    modal.addEventListener('keydown', function(e) {

        // Only trap focus if modal is visible
        if (this.style.visibility !== 'visible' || e.key !== 'Tab') return;
        
        const focusableElements = this.querySelectorAll(
            'a[href], button:not([disabled]), textarea:not([disabled]), input:not([disabled]), select:not([disabled]), [tabindex]:not([tabindex="-1"])'
        );
        
        const firstFocusable = focusableElements[0];
        const lastFocusable = focusableElements[focusableElements.length - 1];
        
        if (e.shiftKey) { // Shift + Tab
            if (document.activeElement === firstFocusable) {
                e.preventDefault();
                lastFocusable.focus();
            }
        } else { // Tab
            if (document.activeElement === lastFocusable) {
                e.preventDefault();
                firstFocusable.focus();
            }
        }

    });

    

    // Map region click handler
    document.querySelectorAll(`.map-pin-path`).forEach(function(pin) {

        // Region activation
        function handleModal(event) {

            // For keyboard events, only proceed if Enter key was pressed
            if (event.type === `keydown` && event.key !== `Enter` && event.key !== ` `) {
                return;
            }
            
            this.setAttribute(`aria-pressed`, `true`); 
            const siteId = this.id;

            if (!CSS.supports('scrollbar-gutter: stable')) {
                document.body.style.paddingRight = window.innerWidth - document.documentElement.clientWidth + 'px';
            }

            // if navigating via keyboard, set #wpadminbar[inert] 
            // TECH DEBT: allow admins to access #wpadminbar after keyboard interaction with the map
            if (event.type === 'keydown') {
                setAdminBarInert(true);
            }       
            showDataModal(siteId);
            modal.setAttribute(`tabindex`, `0`); 
            modal.focus(); 
        }

        pin.addEventListener(`click`, handleModal); // Mouse click
        pin.addEventListener(`keydown`, handleModal); // Keyboard Enter 


        // Populate modal window with clinic + physicial data built in inc/shortcode-map.php
        function showDataModal(siteId) {            

            let clinic = clinicData.clinical_site_info[siteId]; 

            document.getElementById(`clinic-site-name`).innerText = clinic.site_name;
            document.getElementById(`clinic-site-city-state`).innerText = clinic.city_state;

            if(!clinic.physicians || clinic.physicians.length === 0) { }

            else {

                let physicians = clinic.physicians;
                let physicians_html = ``; 
                
                physicians.forEach(function(phys) {
                    physicians_html += `<div class="physician-div">`;
                    physicians_html += `<img width="86" height="86" alt="Photo of ` + phys.name + `" class="physician-src" src="` + phys.img_src + `">`;

                    physicians_html += `<p class="physician-name has-text-color"><strong>` + phys.name + `</strong></p>`;
                    if(phys.institution) {
                        physicians_html += `<p class="physician-institution has-text-color">` + phys.institution + `</p>`;
                    }
                    physicians_html += `</div>`; 

                }); 

                document.getElementById(`clinic-site-physicians`).innerHTML = physicians_html; 
             
            }
            
            modal.setAttribute(`open`,``); 
            modal.setAttribute(`data-site`, siteId); 
            modal.style.visibility = `visible`; 
            modal.setAttribute(`aria-hidden`, `false`); 

            // Clear nubbin
            let modalNubbin = document.getElementById(`modal-nubbin`); 
            modalNubbin.setAttribute(`class`, ``); 

            // Viewport size? 
            let windowW = window.innerWidth; 
            let windowH = window.innerHeight;

            // If user is zoomed in -OR- on small viewport then no nubbin
            if (windowW < 600 || windowH < 1000) {
                return; 
            }
            
            let clinicOnMap = document.getElementById(siteId); 
            var rect = clinicOnMap.getBoundingClientRect();

            // Get Modal Dimenions 
            let modalInner = document.getElementById(`clinic-data`);
            let modalInnerH = modalInner.offsetHeight;
            let modalInnerW = modalInner.offsetWidth; 
            let topStr = `0px`;
            let rightStr = `0px`; 

            if( (rect.top - modalInnerH).toString() > 64 )  {
                topStr = (rect.top - modalInnerH - 16).toString() + `px`; 
            }
            else {
                topStr = (rect.top + 46).toString() + `px`;                 
            }

            let vw = Math.max(document.documentElement.clientWidth || 0, window.innerWidth || 0); 

            let rightEdge = modalInnerW - vw + 24; 
            let rightFormula = (-1 * rect.right) + ( modalInnerW / 2) + 20; 

            if(rightFormula < rightEdge) {
                rightStr = rightEdge.toString() + `px`;
            }
            else if(rightFormula > -12) {
                rightStr = `-12px`; 
            }
            else {
                rightStr = rightFormula.toString() + `px`; 
            }

            modalInner.style.top =  topStr;
            modalInner.style.right = rightStr;  


            // Zoom? 
            const mapElem = document.getElementById(`us-map`);
            const instance = mapElem._panzoomInstance;
            const scale = instance ? instance.getScale() : 1;
            const isZoomed = scale > 1.05 || scale < .95;;

            if(isZoomed) {
                return;
            }
            
            if( (rect.top - modalInnerH).toString() > 64 )  {
                modalNubbin.classList.add(`points-down`); 
                topStrNubbin = (rect.top - 26).toString() + `px`;
            }
            else {
                modalNubbin.classList.add(`points-up`); 
                topStrNubbin = (rect.top + 38).toString() + `px`;
            }

            modalNubbin.style.top = topStrNubbin;
            modalNubbin.style.left = (rect.left + 3).toString() + `px`; 


        }

    }); 
        

    // Close 1 of 3 - close button
    const closeButton = document.querySelector(`[data-close-modal]`);
    
    closeButton.addEventListener(`click`, function(e) {

        const siteId = modal.getAttribute(`data-site`);
        const mapRegion = document.getElementById(siteId); 

        if (mapRegion) {
            mapRegion.setAttribute(`aria-pressed`, `false`);
        }

        if(modal) {
            modal.removeAttribute(`open`); 
            modal.setAttribute(`data-site`, ``); 
            modal.style.visibility = `hidden`; 
        }

        if (!CSS.supports('scrollbar-gutter: stable')) {
            document.body.style.paddingRight = '0px';
        }

        // If the event wasn't actually a 'click' but instead was a keystroke like Enter
        // add :focus to the relevant map region after dismissing via Close button       
        if (e.detail === 0 && mapRegion) { 
            mapRegion.focus();
            modal.setAttribute(`aria-hidden`, `true`); 
        }

    });


    // Close 2 of 3 - close on outside click - .modal covers entire viewport
    window.addEventListener(`click`, function(e) {

        if (e.target.classList.contains(`modal`)) {

            const siteId = e.target.getAttribute(`data-site`); 
            
            if(siteId) {
                this.document.getElementById(siteId).setAttribute(`aria-pressed`, `false`); 
            }

            if (!CSS.supports('scrollbar-gutter: stable')) {
                document.body.style.paddingRight = '0px';
            }

            if(modal) {
                modal.removeAttribute(`open`); 
                modal.setAttribute(`data-site`, ``); 
                modal.style.visibility = `hidden`; 
            }

        }

    });


    // Close 3 of 3 - close with Escape key 
    window.addEventListener(`keydown`, function(e) {

        if (e.key === `Escape`) {

            const siteId = modal.getAttribute(`data-site`);
            const mapRegion = document.getElementById(siteId); 

            if (mapRegion)  {
                mapRegion.setAttribute(`aria-pressed`, `false`);
                mapRegion.focus();
            }

            if (!CSS.supports('scrollbar-gutter: stable')) {
                document.body.style.paddingRight = '0px';
            }

            if(modal) {
                modal.removeAttribute(`open`); 
                modal.setAttribute(`data-site`, ``); 
                modal.style.visibility = `hidden`;
            }
            
              
        }

    });


}); 






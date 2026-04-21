/* jshint esversion: 6, expr: true, -W033 */

// see also: inc/shortcode-map.php - where $clinical_site_info array is built from Advanced Custom Fields 'sites' repeater field 
// access ACF data via clinicData[clinical_site_info][CLINICAL_SITE_SLUG] 

document.addEventListener(`DOMContentLoaded`,function(){    


    // Constants used throughout
    const bodyContent = document.querySelector(`.wp-site-blocks`); 
    const modal = document.getElementById(`data-modal`); 


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
    document.querySelectorAll('.map-pin-path').forEach(function(pin) {

        // Region activation
        function handleModal(event) {

            // For keyboard events, only proceed if Enter key was pressed
            if (event.type === 'keydown' && event.key !== 'Enter') {
                return;
            }
            
            this.setAttribute(`aria-pressed`, `true`); 
            const siteId = this.id; 
            showDataModal(siteId);
            modal.setAttribute(`tabindex`, `0`); 
            modal.focus(); 
        }


        // Mouse click
        pin.addEventListener(`click`, handleModal);
        // Keyboard Enter 
        pin.addEventListener(`keydown`, handleModal);


        // Note: content is written in inc/shortcode-map.php near top of file
        function showDataModal(siteId) {            

            // Populate modal window
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
            
            modal.open = true; 
            modal.setAttribute(`data-site`, siteId); 
            modal.style.visibility = `visible`;
            modal.setAttribute(`aria-hidden`, `false`); 
            
            
            // Keyboard/screenreader focus trap using [inert]
            bodyContent.inert = true; 
            bodyContent.setAttribute(`aria-hidden`, `true`);

            // Position modal on desktop viewports 
            // width INCLUDES scrollbars
            let windowW = window.innerWidth; 

            if (windowW >= 600) {             

                let clinicOnMap = document.getElementById(siteId); 
                var rect = clinicOnMap.getBoundingClientRect();

                // Get Modal Dimenions 
                let modalInner = document.getElementById(`clinic-data`);
                let modalInnerH = modalInner.offsetHeight;
                let modalInnerW = modalInner.offsetWidth; 
                let topStr = `0px`;
                let rightStr = `0px`; 

                let modalNubbin = document.getElementById(`modal-nubbin`); 
                    modalNubbin.setAttribute(`class`, ``); 

                if( (rect.top - modalInnerH).toString() > 64 )  {
                    topStr = (rect.top - modalInnerH - 16).toString() + `px`; 
                    modalNubbin.classList.add(`points-down`); 
                    topStrNubbin = (rect.top - 26).toString() + `px`;
                }
                else {
                    topStr = (rect.top + 46).toString() + `px`;                 
                    modalNubbin.classList.add(`points-up`); 
                    topStrNubbin = (rect.top + 38).toString() + `px`;
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

                modalNubbin.style.top = topStrNubbin;
                modalNubbin.style.left = (rect.left + 3).toString() + `px`; 
            } // end Position modal on tablet-and-wider >= 600px   

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
            modal.setAttribute(`data-site`, ``); 
            modal.style.visibility = `hidden`; 
        }


        bodyContent.setAttribute(`aria-hidden`, `false`);
        bodyContent.inert = false;

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

            if(modal) {
                modal.setAttribute(`data-site`, ``); 
                modal.style.visibility = `hidden`; 
                bodyContent.inert = false; 
                bodyContent.setAttribute(`aria-hidden`, `false`);
            }

        }

    });



    // Close 3 of 3 - close with Escape key 
    window.addEventListener(`keydown`, function(e) {

        if (e.key === `Escape`) {

            console.log(`case 3 - Escape key`); 

            const siteId = modal.getAttribute(`data-site`);
            const mapRegion = document.getElementById(siteId); 

            if (mapRegion)  {
                mapRegion.setAttribute(`aria-pressed`, `false`);
            }

            if(modal) {
                modal.setAttribute(`data-site`, ``); 
                bodyContent.inert = false; 
                bodyContent.setAttribute(`aria-hidden`, `false`);
                modal.style.visibility = `hidden`;
                modal.setAttribute(`aria-hidden`, `true`); 
            }
       
            mapRegion.focus();
              
        }

    });


}); 






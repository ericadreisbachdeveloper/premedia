/* jshint esversion: 6, expr: true, -W033 */

// see also: inc/shortcode-map.php - where $clinical_site_info array is built from Advanced Custom Fields 'sites' repeater field 
// access ACF data via clinicData[clinical_site_info][CLINICAL_SITE_SLUG] 

document.addEventListener(`DOMContentLoaded`,function(){    
    

    // Map region click handler
    document.querySelectorAll('.map-pin-path').forEach(function(pin) {

        // Region activation
        function handleModal(event) {

            // For keyboard events, only proceed if Enter key was pressed
            if (event.type === 'keydown' && event.key !== 'Enter') {
                return;
            }
                
            const siteId = this.id; 

            this.setAttribute(`aria-pressed`, `true`); 
            
            showDataModal(siteId);
            const modal = document.getElementById(`data-modal`); 
            modal.setAttribute(`tabindex`, `0`); 
            modal.focus(); 
        }


        // Mouse click
        pin.addEventListener(`click`, handleModal);
        // Keyboard Enter 
        pin.addEventListener(`keydown`, handleModal);


        // Note: #form-modal content is written in template-map.php near top of file
        function showDataModal(siteId) {            

            // Position modal on desktop viewports 
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
                
            const modal = document.getElementById('data-modal');

            modal.setAttribute(`data-site`, siteId); 
            modal.style.display = 'block';


            // Position modal
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


        }



    }); 
        


    // Close buttons
    document.querySelectorAll(`[data-close-modal]`).forEach(button => {

        button.addEventListener(`click`, function(event) {
            
            const modalType = this.getAttribute(`data-modal-type`);
            const modal = document.getElementById(modalType);
            const siteId = modal.getAttribute(`data-site`);
            const mapRegion = document.getElementById(siteId); 

            if (siteId) {
                document.getElementById(siteId).setAttribute(`aria-pressed`, `false`);
            }

            modal.setAttribute(`data-site`, ``); 
            modal.style.display = `none`;

            // If the event wasn't actually a 'click' but instead was a keystroke like Enter
            // add :focus to the relevant map region after dismissing via Close button       
            if (event.detail === 0) { 
                console.log(`some keystroke or other`);
                setTimeout(() => {
                    mapRegion.focus();
                }, 2000); // Small delay for the sake of Voiceover 
                
            }

        });

    });
    
    

    // Close modal on outside click - .modal covers entire viewport
    window.addEventListener(`click`, function(e) {

        if (e.target.classList.contains(`modal`)) {

            const siteId = e.target.getAttribute(`data-site`); 
            
            if(siteId) {
                this.document.getElementById(siteId).setAttribute(`aria-pressed`, `false`); 
            }

            const modal = document.querySelector(`.modal[style*="display: block"]`);

            modal.setAttribute(`data-site`, ``); 
            modal.style.display = `none`;

        }

    });



    // Close modal with Escape key
    window.addEventListener(`keydown`, function(e) {
        
        if (e.key === `Escape`) {
            const modal = document.querySelector(`.modal[style*="display: block"]`);
            const siteId = modal.getAttribute(`data-site`); 
            const mapRegion = document.getElementById(siteId); 
            
            if (modal) {
                
                if(mapRegion) {

                    mapRegion.setAttribute(`aria-pressed`, `false`);

                    setTimeout(() => {
                        mapRegion.focus();
                    }, 50); // Small delay for the sake of Voiceover 

                }
 
                modal.setAttribute(`data-site`, ``); 
                modal.style.display = `none`;
            
            }

        }

    });


 


}); 
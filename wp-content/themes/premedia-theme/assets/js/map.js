/* jshint esversion: 6, expr: true, -W033 */
// see also: inc/shortcode-map.php - where $clinical_site_info array is built from Advanced Custom Fields 'sites' repeater field 
// access ACF data via clinicData[clinical_site_info][CLINICAL_SITE_SLUG] 
// ref: https://a11ypath.com/patterns/modal/#code
document.addEventListener(`DOMContentLoaded`,function(){    

    const dialog = document.getElementById(`data-modal`);
    const pins = document.querySelectorAll(`.map-pin-path`);
    const closeBtn = dialog.querySelector(`.modal-close`);
    const wpAdminBar = document.getElementById(`wpadminbar`); 


    // Populate dialog with clinic data
    function populateDialog(siteId) {

        let clinic = clinicData.clinical_site_info[siteId];
        
        dialog.querySelector(`#clinic-site-name`).innerText = clinic.site_name;
        dialog.querySelector(`#clinic-site-city-state`).innerText = clinic.city_state;
        
        if (!clinic.physicians || clinic.physicians.length === 0) {
            dialog.querySelector(`#clinic-site-physicians`).innerHTML = ``;
        } 
        
        else {
            let physicians_html = ``;

            clinic.physicians.forEach(phys => {

                physicians_html += `<div class="physician-div">`;
                physicians_html += `<img width="86" height="86" alt="Photo of ${phys.name}" class="physician-src" src="${phys.img_src}">`;
                physicians_html += `<p class="physician-name"><strong>${phys.name}</strong></p>`;

                if (phys.institution) {

                    physicians_html += `<p class="physician-institution">${phys.institution}</p>`;
                    
                }

                physicians_html += `</div>`;

            });

            dialog.querySelector(`#clinic-site-physicians`).innerHTML = physicians_html;   
        }

        // Position modal
        /* 
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
        */ 

    }

    // Helper function
    function setAdminBarInert(value) {
        if (wpAdminBar) {
            wpAdminBar.inert = value;
        }
    }

    
    // Open: use showModal() for true modal behavior
    pins.forEach(pin => {

        pin.addEventListener(`click`, (e) => {
            setAdminBarInert(true);
            const siteId = pin.id;
            populateDialog(siteId);
            dialog.showModal();
        });

        pin.addEventListener(`keydown`, (e) => {
            if (e.key === `Enter` || e.key === ` `) {
                setAdminBarInert(true);
                e.preventDefault(); // Prevent scrolling on space
                const siteId = pin.id;
                populateDialog(siteId);
                dialog.showModal();
            }
        }); 

    }); 

    
    // Close 1 of 2 - close button 
    // focus to the element that was focused before
    // showModal() was called
    closeBtn.addEventListener(`click`, () => {
        dialog.close();
    });

    // Close 2 of 2 - on backdrop click
    dialog.addEventListener(`click`, (event) => {
        if (event.target === dialog) {
            dialog.close();
        }
    });


    

});
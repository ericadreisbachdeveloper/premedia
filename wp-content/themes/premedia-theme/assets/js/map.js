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

    
    // Close 1 of 3 - close button 
    // focus to the element that was focused before
    // showModal() was called
    closeBtn.addEventListener(`click`, () => {
        //wpAdminBar.inert = false; 
        dialog.close();
    });

    // Close 2 of 3 - on backdrop click
    dialog.addEventListener(`click`, (event) => {
        if (event.target === dialog) {
            //wpAdminBar.inert = false; 
            dialog.close();
        }
    });

    // Close 3 of 3 - close with Escape key 
    window.addEventListener(`keydown`, function(e) {
        if (e.key === `Escape`) {
            setTimeout(() => {
                //wpAdminBar.inert = false;
            }, 2000);
        }
    });

});
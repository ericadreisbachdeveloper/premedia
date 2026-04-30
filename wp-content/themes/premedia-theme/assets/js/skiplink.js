/* jshint esversion: 6, expr: true, -W033 */


document.addEventListener(`DOMContentLoaded`,function(){    

    const skipLinks = document.querySelectorAll(`.skip-link`); 

    // Why use typeof variable !== `undefined` ... 
    // cf: https://sentry.io/answers/javascript-check-if-variable-exists-is-defined-initialized/
    if(typeof skipLinks !== `undefined`) {

        skipLinks.forEach(link => {
            
            // Click / Keyboard / Voiceover 
            link.addEventListener('click', function(e){

                let target = link.getAttribute(`href`); 

                if(target) {
                    document.querySelector(target).setAttribute(`tabindex`, `-1`); 
                }
    
                document.querySelector(target).focus(); 

                e.preventDefault();

            });

        });

    }

}); 







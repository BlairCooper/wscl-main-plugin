/**
 * @ts-check
 */

class PetitioButton {
    constructor(elem) {
        if ('' != elem.data('action')) {
            this.elem = elem;

            elem.click(jQuery.proxy(this.onClick, this));
        }
    }

    // method for click events
    onClick(event)
    {
        let dataObj = this.elem.data();

        // rename the nonce field if present
        if (dataObj.hasOwnProperty('nonce')) {
            delete Object.assign(dataObj, {['_ajax_nonce']: dataObj['nonce'] })['nonce'];
        }

        let params = jQuery.param(dataObj);
        
        fetch(wscl_ajax_url + '?' + params)
            .then(resp => {
                if (resp.ok) {
                    location.reload();
                } else {
                    throw new Error(resp.statusText);
                }
            })
            .catch((e) => {
                console.error(e);
            })
        ;
        
        return false;
    }
}


jQuery(document).ready(function($) {           //wrapper

    // Create a FrmModelReloaded for any modal that needs it    
    $('.petitionButton').each(function () {
        new PetitioButton($(this)); // NOSONAR - ok to not save instance
    });
});
 
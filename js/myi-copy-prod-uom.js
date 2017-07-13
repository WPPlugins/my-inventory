
function cb_change( id ) {      
    if ((jQuery)('#cb' + id).attr('checked') == 'checked') {
        (jQuery)('#copy-client-select' + id).attr('required','required');
        (jQuery)('#copy-client-select' + id).attr('min','1');
        (jQuery)('#copy-client-select' + id).attr('data-msg-min','Please choose an UOM');
    } else {
        (jQuery)('#copy-client-select' + id).removeAttr('required');
        (jQuery)('#copy-client-select' + id).removeAttr('min');
        (jQuery)('#copy-client-select' + id).removeAttr('data-msg-min');
    }
}
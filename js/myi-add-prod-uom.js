
    function add_uom_clicked( row, minus ) {
        var new_level = -999;

        if ( minus ) {
            new_level = parseFloat((jQuery)('#first_set_lvl' +row).val()) - parseFloat(1);
        } else {
            new_level = parseFloat((jQuery)('#last_set_lvl' +row).val()) + parseFloat(1);
        }

        (jQuery)('#box' +row +'_' +new_level).removeClass('no-display');
        
        if ( !minus ) { // bottom uom
            (jQuery)('#qty' +row +'_' +(new_level-1)).removeClass('no-display');
            (jQuery)('#last_set_lvl' +row).val( new_level );
        } else {
            (jQuery)('#qty' +row +'_' +new_level).removeClass('no-display');
            (jQuery)('#first_set_lvl' +row).val( new_level);
        }
        
        decide_add_uom_show( new_level, row, minus );
        show_delete_uom ( row );
        
        return false; // prevent button from submitting form
    }  
    
    function show_delete_uom ( row ) {
        (jQuery)('[id^="btn_del' +row +'"]').addClass('no-display');

        var first_level = parseFloat((jQuery)('#first_set_lvl' +row).val());
        var last_level = (jQuery)('#last_set_lvl' +row).val();
        
        // only show btn_del for first record if not add new record
        if ( row != -1 ) {
            (jQuery)('#btn_del' +row +'_' +first_level).removeClass('no-display');
        }
        
        // only show btn_del for last record if not add new record and last_level < min_level
        if (!( row == -1 && last_level < parseFloat((jQuery)('#min_level').val()) )) {
            (jQuery)('#btn_del' +row +'_' +last_level).removeClass('no-display');
        }
    }
    
    function delete_uom_clicked( row, minus ) {       
        var new_level = -999;

        if ( minus ) {
            new_level = parseFloat((jQuery)('#first_set_lvl' +row).val());
        } else {
            new_level = parseFloat((jQuery)('#last_set_lvl' +row).val());
        }

        (jQuery)('#uom-select' +row +'_' +new_level)[0].selectedIndex = 0;
        (jQuery)('#uom-select' +row +'_' +new_level).change();

        var qty_value = 0;        

        if ( !minus ) { // bottom uom
            (jQuery)('#qty' +row +'_' +(new_level-1)).addClass('no-display');
            (jQuery)('#box' +row +'_' +new_level).addClass('no-display');
            (jQuery)('#last_set_lvl' +row).val( new_level - 1 );
            (jQuery)('#qtyInp' +row +'_' +(new_level-1)).val(qty_value);
        } else {
            (jQuery)('#qty' +row +'_' +new_level).addClass('no-display');
            (jQuery)('#box' +row +'_' +new_level).addClass('no-display');
            (jQuery)('#first_set_lvl' +row).val( new_level + 1);
            (jQuery)('#qtyInp' +row +'_' +new_level).val(qty_value);
        }

        decide_add_uom_show( (jQuery)('#first_set_lvl' +row).val(), row, minus );
        decide_add_uom_show( (jQuery)('#last_set_lvl' +row).val(), row, minus );
        show_delete_uom ( row );

        return false; // prevent button from submitting form
    }  
    
    function decide_add_uom_show ( level, row, minus ) {
        if ( minus ) {
            if (level <= (jQuery)('#min_level').val() ) {
                (jQuery)('#add_btn_up' +row).addClass('no-display');
            } else {
                (jQuery)('#add_btn_up' +row).removeClass('no-display');
            }
        } else {
            if (level >= (jQuery)('#max_level').val() ) {
                (jQuery)('#add_btn_down' +row).addClass('no-display');
            } else {
                (jQuery)('#add_btn_down' +row).removeClass('no-display');
            }            
        }
    }
    
    function set_default_uom ( row, level ) {
        (jQuery)('[id^="box' +row +'"]').removeClass('default');
        (jQuery)('#box' +row +'_' +level).addClass('default');
    }

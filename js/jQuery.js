(function($) {
    $(document).ready(function(){
      // level 2 menu been clicked
      $('.dropdown a.level2').on("click", function(e){
                                                        //$(this).next('ul').toggle();
                                                        $('.dropdown-submenu').addClass('close');
                                                        $(this).next('ul').removeClass('close');
                                                        e.stopPropagation();
                                                        e.preventDefault();
                                                     });
      
      // level 1 menu been clicked
      $('.dropdown a.level1').on("click", function(e){                                                        
                                                        e.stopPropagation();
                                                        e.preventDefault();
                                                     });                                                     
      
      // close the submenu
      $('.dropdown-submenu').addClass('close');

      // if not using mobile, don't collapse the menu
      if ($(window).width() <= 768) {
          $('.nav-collapse').addClass('collapse');
      } else {
          $('.nav-collapse').removeClass('collapse');
      }    
      
      // button on click
      $('[id^="save_btn').on("click", function(e){     
        $('[id^="save_btn').attr('disabled','disabled');       
        
        if ( !$('#my_form').valid() ) {
            $('[id^="save_btn').removeAttr('disabled');
        } else {
            // if have element submit_btn_id, populate it with id
            if ( $('#submit_btn_id').length ) {
                $('#submit_btn_id').val($(this).attr('id'));
            }
            
            $('#my_form').submit();
        }
      });        
    }); //document ready   
})( jQuery );
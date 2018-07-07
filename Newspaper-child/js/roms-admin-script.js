(function($) {
   
   // $( "#submitkkk" ).trigger( "click" );
    
    
    jQuery(document).on('click', '#submitkkk', function (event) {
       alert("hellofffff");
            jQuery('[name="downloadfile"]').submit();
    });
    jQuery(document).ready(function ($) {
      jQuery('#submitkkk').click(function(){
            alert("hellofffff");
            $('[name="downloadfile"]').submit();
        }); 
     });
})(jQuery);

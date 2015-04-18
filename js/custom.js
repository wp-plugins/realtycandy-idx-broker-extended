jQuery(function($) {
	/**
	 * REFRESH IDX WRAPPER
	 *
	 */
	$("form[name='form_clear_wrapper']").submit(function( event ) {

		var postData = $(this).serializeArray();
	    var formURL = $(this).attr("action");
	    $.ajax(
	    {
	        url : formURL,
	        type: "POST",
	        data : postData,
	        success:function(data, textStatus, jqXHR) 
	        {
	            //data: return data from server
	            $('#wpbody .wrap h1').after('<div class="updated"><p><strong>Successfully cleared wrapper cache.</strong></p></div>');
	            $('.updated').delay(5000).fadeOut('slow', function() { $(this).remove(); });
	        },
	        error: function(jqXHR, textStatus, errorThrown) 
	        {
	            //if fails      
	            $('#wpbody .wrap h1').after('<div class="updated"><p><strong>An error occurred, please try again later.</strong></p></div>');
	            $('.updated').delay(5000).fadeOut('slow', function() { $(this).remove(); });
	        }
	    });
	    event.preventDefault(); //STOP default action
	    //event.unbind(); //unbind. to stop multiple form submit.

	});

});
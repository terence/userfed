appcore.validator = {};

//Twitter Bootstrap 2.3.1
appcore.validator.tbErrorPlacement = function(error, element) {
	//	summary: Error placement for twitter bootstrap style form
	//		@see: http://mitchlabrador.com/2010/11/05/how-to-change-the-way-jquery-validate-handles-error-messages/
	
	//remove class="error" of jQuery validator as twitter bootstrap already provide style
	error.removeClass("error");
	
	//create html container for error
	var helpContainer = $(element).closest(".controls").find(".help-inline ul");
	if (helpContainer.length == 0) {
		$('<span class="help-inline"><ul></ul></span>').insertAfter(element);
	}
	
	//clean previous error
	$(element).closest(".controls").find(".help-inline ul").empty();
	
	//add the error message
	if (error.innerHTML) {
		$(element).closest(".controls").find(".help-inline ul").append($("<li/>").append(error));
	} else {
		$(element).closest(".controls").find(".help-inline ul").append($("<li/>").append(error));
		$(element).closest(".controls").find(".help-inline ul li").hide();
	}
	
};

appcore.validator.addTwitterBootstrapStyle = function() {
	jQuery.validator.setDefaults({
		errorPlacement : function (error, element) {
			appcore.validator.tbErrorPlacement(error, element);
	    },
		highlight: function(label) {
			//For inputbox
			$(label).closest('.control-group').removeClass('success');
	    	$(label).closest('.control-group').addClass('error');
	    },
	    success: function(label) {
	    	//For inputbox
	    	$(label).closest('.control-group').removeClass('error');
	    	$(label).closest('.control-group').addClass('success');
	    }	    
	});
};

//@link http://stackoverflow.com/questions/1219860/html-encoding-in-javascript-jquery/7124052#7124052
appcore.htmlEscape = function(str) {
    return String(str)
            .replace(/&/g, '&amp;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;');
}
(function( $ ) {
	$.fn.quickClear = function() {
		return this.filter("[data-clear-btn='true']").each(function() {
			var $input = $(this);
			$input.addClass("clearable");
			$input.wrap("<div class='input-control'></div>");
			var $wrapper = $input.parent();
			$wrapper.append("<a href='#' class='btn-clear hide' tabindex='-1'><i class='glyphicon glyphicon-remove'></i></a>");
			var $btn = $wrapper.find(".btn-clear");
			// clear input when press x button
			$wrapper.delegate(".btn-clear", "click", function(){
				$input.val('').focus();
				$btn.addClass("hide");
				return false;
			});
			$input.focus(function() {
				$wrapper.addClass("input-focus").removeClass("input-hover");
			});
			$input.hover(function() {
				$wrapper.toggleClass("input-hover");
				toggleClear();
			});
			$input.blur(function() {
				$wrapper.removeClass("input-focus").removeClass("input-hover");
			});
			$input.keyup(function(e) {
				// clear input if escape key pressed
				var code = e.charCode || e.keyCode;
				if (code === 27) {
					$input.val('').focus();
				}
			});
			function toggleClear() {
				setTimeout( function() {
					if ($input.val()) {
						$btn.removeClass("hide");
					} else {
						$btn.addClass("hide");
					}
				}, 0 );
			}
			$input.bind( "paste cut keyup input focus change blur", toggleClear );
		});
	};
}( jQuery ));
(function() {
	$("input").quickClear();
})();
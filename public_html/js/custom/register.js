//validate signup form on keyup and submit
$().ready(function() {
	$("#register").validate({
		onkeyup: false,
		rules: {
			email: {
				remote: {
					url: "/internal/validate-unique-field",
					type: "get",
					beforeSend: function () {
						$('[name=email]').closest('.controls').find('label.error').remove();
						$('[name=email]').closest('.controls').append('<label class="icon-spinner icon-spin"></label>');
					},
					complete: function() {
						$('[name=email]').closest('.controls').find('label.icon-spinner').remove();
					},
					data: {
						field: "username",
						value: function () {
							return $('[name=email]').val();
						}
					}
				}
			},
			email_confirm: {
				equalTo: '[name=email]'
			},
			password: {
				required: true,
				minlength: 6,
				maxlength: 32
			},
			password_confirm: {
				equalTo: '[name=password]'
			}
		},
		messages: {	
			email: {
				remote: "This email address was already used by another"
			},
			email_confirm: {
				equalTo: "Your email and confirmation email do not match."
			},
			password: {
				required: "Password can not be empty",
			},
			password_confirm: {
				required: "Confirm password can not be empty.",
				equalTo: "Your password and confirmation password do not match."
			}
		}
	});

	$('#register').on('keyup paste change', function (event) {
		var target = event.target;
		var disableBtn= false;
		var value = $(target).val();
		
		if (event.type == 'paste') {
			value = true;
		}
		
		if (value) {
			disableBtn = true;
		} else {
			var requiredElements = $(this).find('[required]').not('[required=false]');
			requiredElements.each(function (index, element) {
				if ($(element).val()) {
					disableBtn = true;
					return;
				}
			});
			
		}
		var btn = $('.form-register').find('a.btn');
		
		if (disableBtn) {
			btn.addClass('overlay-bg');
			btn.hover(function () {
				$(this).removeClass('overlay-bg');
			}, function () {
				$(this).addClass('overlay-bg');
			});
		} else {
			btn.removeClass('overlay-bg');
			btn.unbind('mouseenter mouseleave');
		}
	});
});
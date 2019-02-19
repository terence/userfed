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
			emailConfirm: {
				equalTo: '[name=email]'
			},
			password: {
				required: true,
				minlength: 6,
				maxlength: 32
			},
			passwordConfirm: {
				equalTo: '[name=password]'
			}
		},
		messages: {	
			email: {
				remote: "This email address was already used by another"
			},
			emailConfirm: {
				equalTo: "Your email and confirmation email do not match."
			},
			password: {
				required: "Password can not be empty",
			},
			passwordConfirm: {
				required: "Confirm password can not be empty.",
				equalTo: "Your password and confirmation password do not match."
			}
		}
	});
});
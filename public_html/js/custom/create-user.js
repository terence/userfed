//validate signup form on keyup and submit
$().ready(function() {
	$("#create-user").validate({
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
			}
		},
		messages: {	
			email: {
				remote: "This email address was already used by another"
			},
			email_confirm: {
				equalTo: "Your email and confirmation email do not match."
			}
		}
	});
});
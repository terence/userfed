$(document).ready(function () {
	handleValidationForm();
});

function handleValidationForm()
{
	$('#invite-activate').validate({
		onkeyup: false,
		rules: {
			firstname: {
				required: true
			},
			lastname: {
				required: true
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
		messages : {
			firstname: {
				required: "Please enter your firstname."
			},
			lastname: {
				required: "Please enter your lastname."
			},
			password: {
				required: "Password can not be empty."
			},
			password_confirm: {
				required: "Confirm password can not be empty.",
				equalTo: "Your password and confirmation password do not match."
			}
		}
	});
}
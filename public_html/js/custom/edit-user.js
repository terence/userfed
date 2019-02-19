function handleGeneratePasswordBtn()
{
	$('#btn-generate-password').click(function() {
		$(this).addClass('disabled');
		$('#generate-password-loading').show();
		var self = this;
		$.ajax({
			url: $(self).data('href'),
			success: function(data) {
				$(self).parent().find('.alert').remove();
				var message = $('<div></div>').addClass('alert');
				if (data.errorCode == 0) {
					message.text('Generate password successfully.').addClass('alert-success');
				} else {
					message.text('Generate password failed.').addClass('alert-error');
				}
				message.prepend('<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>');
				$(self).parent().prepend(message);
				$(self).removeClass('disabled');
				$('#generate-password-loading').hide();
			}
		});
	});
}

$(document).ready(function() {
	handleGeneratePasswordBtn();
});
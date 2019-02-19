function handleConfirm()
{
    /**
     * for some reason the modal works incorrectly with the layout having sidebar, the modal overlay goes on top of everything
     * we have to move the dom of the modal to the end of body to get it work correctly.
     * @todo remove this line.
     */
    $('body').append($('#modal-confirm').detach());
	confirmMessage = '';
	$('[data-confirm-message]').click(function (e) {
		e.preventDefault();
		var strReplace = $(this).data('confirm-message');
		var href = $(this).attr('href');
		if (!confirmMessage) {
			confirmMessage = $('#modal-confirm .modal-body').html();
		}
		var message = confirmMessage.replace('%message%', strReplace);
		$('#modal-confirm .modal-body').html(message);
		$('#modal-confirm #btn-confirm-ok').data('href', href);
		$('#modal-confirm').modal('show');
	});
	
	$('#btn-confirm-ok').click(function () {
		var href = $(this).data('href');
		window.location = href;
	});
}

$(document).ready(function() {
	handleConfirm();
});

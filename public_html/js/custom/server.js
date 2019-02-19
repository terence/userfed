$(document).ready(function() {
	var applicationSelect = $('[name="application_id"]');
	var serverElement = $('[name="server_id"]');
	$('.add-application').click(function() {
		$('#add-application-box').toggle();
	});
	$('[name="application_id"]').change(function() {
		$('#icon-loading').show();
		serverElement.attr('disabled', 'disabled');
		$('#save-btn').attr('disabled', 'disabled');
		$.ajax({
			url: '/admin/user-app/get-server/' + userId,
			data: { 'app-id': applicationSelect.val()},
			success: function (data) {
				serverElement.remove('option');
				if (data.errorCode === 0 ) {
					var html = '';
					var servers = data.server;
					var  count = servers.length;
					if (count) {
						for (key in servers) {
							html += '<option value="' + servers[key].server_id + '">' + servers[key].description +'</option>';
						}
						serverElement.removeAttr('disabled');
						$('#save-btn').removeAttr('disabled');
					} else {
						html = '<option value="0">No server found</option>';
						$('#add-application-btn').enabled = false;
					}
					serverElement.html(html);
					$('#icon-loading').hide();
				}
			}
		});
	});
});
function handleLoadApplication()
{
	$('[name="application_id"]').select2({
		minimumInputLength: 2,
		width: "100%",
		ajax: {
			url: '/admin/app-org/get-application',
			data: function (key, page) {
				return {
					key: key,
					page_limit: 10,
					page: page
				}
			},
			results: function (data, page) {
				var more = (page * 10) < data.total;
				var apps = data.apps;
				var results = [];
				for ( var i in apps) {
					results.push({id: apps[i].application_id, text: apps[i].title});
				}
				return {results: results, more: more};
			}
		}
	});
}

function handleLoadServer(appId)
{
	var serverElement = $('[name="server_id"]');
	$('#icon-loading').show();
	$.ajax({
		url: '/admin/app-org/get-server/' + appId,
		data: {'org-id': organisationId},
		success: function (data) {
			var html = '';
			var servers = data.servers;
			var  count = servers.length;
			if (count) {
				for (key in servers) {
					html += '<option value="' + servers[key].server_id + '">' + servers[key].title +'</option>';
				}
			} else {
				html = '<option>No server found</option>';
			}
			serverElement.html(html);
			$('#icon-loading').hide();
		}
	});
}

$(document).ready(function () {
	handleLoadApplication();
	$('[name="application_id"]').on('change', function (e) {
		var applicationId = e.val;
		handleLoadServer(applicationId);
	});
});

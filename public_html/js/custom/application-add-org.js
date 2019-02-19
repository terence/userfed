function handleLoadOrg()
{
	$('[name="organisation_id"]').select2({
		minimumInputLength: 1,
		width: "100%",
		ajax: {
			url: '/admin/org/get-org',
			data: function (key, page) {
				return {
					search: key,
					page_limit: 10,
					page: page
				}
			},
			results: function (data, page) {
				var more = (page * 10) < data.total;
				var organisations = data.organisations;
				var results = [];
				for ( var i in organisations) {
					var info = '';
					results.push({
						id: organisations[i].organisation_id,
						text: organisations[i].title
					});
				}
				return {results: results, more: more};
			}
		}
	});
}

function handleDisable(input)
{
	input.attr('disabled', 'disabled');
}

function handleEnable(input)
{
	input.removeAttr('disabled');
}

function handleLoadServer(appId, orgId)
{
	var serverInput = $('[name=server_id]');
	$.ajax({
		url: '/admin/app-org/get-server/' + appId,
		data: {
			'org-id': orgId
		},
		success: function (data) {
			var servers = data.servers;
			var count = servers.length;
			var html = '';
			if (count > 0) {
				for (var key  in servers) {
					html += '<option value="' + servers[key].server_id +'">' + servers[key].domain +'</option>';
				}
				handleEnable(serverInput);
			} else {
				html = '<option>No server found.</option>';
				handleEnable(serverInput);
			}
			serverInput.html(html);			
		}
	});
}

$(document).ready(function () {
	handleLoadOrg();
	$('[name="organisation_id"]').on('change', function (e) {
		handleLoadServer(applicationId, e.val);
	});
	
});
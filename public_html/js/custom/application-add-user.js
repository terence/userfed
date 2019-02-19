function handleLoadUser()
{
	var userInput = $('[name=user_id]');
	userInput.select2({
		minimumInputLength: 1,
		width: "100%",
		ajax: {
			url: '/admin/user-app/get-user/' + applicationId,
			data: function (key, page) {
				return {
					search: key,
					page_limit: 10,
					page: page
				}
			},
			results: function (data, page) {
				var more = (page * 10) < data.total;
				var users = data.users;
				var results = [];
				for ( var i in users) {
					var info = '';
					if (users[i].firstname) {
						info += users[i].firstname;
					}
					if (users[i].lastname) {
						info += ' ' + users[i].lastname;
					}
					if (users[i].email) {
						info += ', ' + users[i].email;
					}
					results.push({
						id: users[i].user_id,
						text: info
					});
				}
				return {results: results, more: more};
			}
		}
	});
}

function handleDisabled(input)
{
	input.attr('disabled', 'disabled');
}

function handleEndabled(input)
{
	input.removeAttr('disabled');
}

function handleLoadOrganisation(userId)
{
	var orgInput = $('[name=organisation_id]');
	$.ajax({
		url: '/admin/user-org/get-organisation/' + userId,
		success: function (data) {
			var html = '';
			var organisations = data.organisations;
			var  count = organisations.length;
			if (count) {
				for (key in organisations) {
					html += '<option value="' + organisations[key].organisation_id + '">' + organisations[key].title +'</option>';
				}
				handleEndabled(orgInput);
			} else {
				html = '<option>No organisation found</option>';
				handleDisabled(orgInput);
			}
			orgInput.html(html);
			handleLoadServer(applicationId, orgInput.val());
		}
	});
}

function handleLoadServer(appId, orgId)
{
	var serverInput = $('[name=server_id]');
	
	$.ajax({
		url: '/admin/app-org/get-server-org/' + appId,
		data: {
			'org-id': orgId
		},
		success: function (data) {
			var html = '';
			var servers = data.servers;
			var  count = servers.length;
			if (count) {
				for (key in servers) {
					html += '<option value="' + servers[key].server_id + '">' + servers[key].domain +'</option>';
				}
				handleEndabled(serverInput);
			} else {
				html = '<option>No server found</option>';
				handleDisabled(serverInput);
			}
			serverInput.html(html);
		}
	});
}


$(document).ready(function () {
	handleLoadUser();
	var userInput = $('[name=user_id]');
	var orgInput = $('[name=organisation_id]');
	var serverInput = $('[name=server_id]');
	userInput.on('change', function (e) {
		var userId = e.val;
		handleLoadOrganisation(userId);
	});
	
	$('[name=organisation_id]').on('change', function (e) {
		orgId = $(this).val();
		handleLoadServer(applicationId, orgId);
	});
	
	
//	orgInput.select2({
//		width: '100%',
//		placeholder: 'Select a organisation.'
//	});
//
//	serverInput.select2({
//		width: '100%',
//		placeholder: 'Select a server.'
//	});
});
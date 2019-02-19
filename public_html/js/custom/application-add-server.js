$(document).ready(function () {
    $('[name="server_id"]').select2({
        minimumInputLength: 1,
        width: "100%",
        ajax: {
			url: '/admin/app/server/' + applicationId + '/get-server',
			data: function (keyword, page) {
				return {
					keyword: keyword,
					page_limit: 10,
					page: page
				}
			},
			results: function (data, page) {
				var more = (page * 10) < data.total;
				var servers = data.servers;
				var results = [];
				for ( var i in servers) {
					var info = '';
					results.push({
						id: servers[i].server_id,
						text: servers[i].title
					});
				}
				return {results: results, more: more};
			}
		}
    });
});
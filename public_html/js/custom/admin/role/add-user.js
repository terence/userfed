function handleLoadUser()
{
	$('[name="user_id"]').select2({
		minimumInputLength: 1,
		width: "100%",
		ajax: {
			url: appcore.url('/admin/role/user/' + $('[name="user_id"]').data('roleId') + '/get-user'),
			data: function (key, page) {
				return {
					keyword: key,
					ipp: 10,
					page: page
				}
			},
			results: function (data, page) {
                var itemPerPage = 10;
                if (data.itemPerPage != undefined) {
                    itemPerPage = data.itemPerPage;
                }
				var more = (page * itemPerPage) < data.total;
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
						info += ', email: ' + users[i].email;
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

$(document).ready(function () {
	handleLoadUser();
});

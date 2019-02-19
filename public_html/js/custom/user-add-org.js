function handleLoadOrg(element)
{
	if (!element) {
		var element = $('[name="organisation_id"]');
	}
	element.select2({
		minimumInputLength: 1,
		width: "100%",
		ajax: {
			url: '/admin/org/get-org',
			data: function (key, page) {
				return {
					search: key,
					page_limit: 10,
					page: page,
					not_has_user: userId
				};
			},
			results: function (data, page) {
				var more = (page * 10) < data.total;
				var organisations = data.organisations;
				var results = [];
				for ( var i in organisations) {
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
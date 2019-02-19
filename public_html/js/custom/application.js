function handleLoadApplication(element)
{
	if (!element) {
		var element = $('[name="application_id"]');
	}
	
	element.select2({
		minimumInputLength: 2,
		width: "100%",
		ajax: {
			url: '/admin/app-org/get-application',
			data: function (key, page) {
				return {
					key: key,
					page_limit: 10,
					page: page
				};
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

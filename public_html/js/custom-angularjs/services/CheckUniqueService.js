angular.module('app.services', [])
    .factory('checkUniqueService', ['$http', function ($http) {
        var serviceBase = "/internal/validate-unique-field",
            factory = {};

        factory.check = function (property, value) {
            return $http.get(serviceBase + '?field=' + 
              property + '&value=' + escape(value)).then(
                function (results) {
                    return results.data.unique == true;
                });
        };
        return factory;
    }]);
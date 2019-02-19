angular.module('App').factory('UserFed', ['$resource', function($resource) {
    return {
        organisation: $resource('/api/payroll/uf-org/:id/:action', null, {
            'update': { method:'PUT' }
        })
    };
}]);
angular.module('App').factory('Company', ['$resource', function($resource) {
    return $resource('/api/payroll/company/:id', null, {
        'update': { method:'PUT' }
    });
}]);
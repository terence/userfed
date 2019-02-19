angular.module('App').factory('Dictionary', ['$resource', function($resource) {
    return $resource('/api/payroll/dictionary/:type/:id', null, {
        'update': { method:'PUT' }
    });
}]);
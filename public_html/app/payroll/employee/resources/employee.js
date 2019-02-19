angular.module('App').factory('Employee', ['$resource', function($resource) {
    return $resource('/api/payroll/company/:company_id/employee/:employee_id', {company_id: '@company_id', employee_id: '@employee_id'}, {
        'update': { method:'PUT' }
    });
}]);
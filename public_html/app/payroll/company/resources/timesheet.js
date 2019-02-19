angular.module('App').factory('Timesheet', ['$resource', function($resource) {
    return $resource('/api/payroll/company/:company_id/timesheet/:employee_id', { company_id : '@company_id', employee_id : '@employee_id' }, {
        'update': { method:'PUT' }
    });
}]);
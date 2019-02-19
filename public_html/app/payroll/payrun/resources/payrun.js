angular.module('App').factory('Payrun', ['$resource', function($resource) {
    return $resource('/api/payroll/company/:company_id/payrun/:payrun_id', {company_id: '@company_id', payrun_id: '@payrun_id'}, {
        'update': { method:'PUT' }
    });
}]);
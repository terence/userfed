angular.module('App').controller('EmployeesViewDetailCtrl', [
    '$scope',
    '$stateParams',
    'Employee',
    '$state',
    'Timesheet',
    '$http',
    function($scope, $stateParams, Employee, $state, Timesheet, $http) {
        if ($state.current.name === 'employees.detail') {
            $scope.showBreadcrumb = false;
        }
        if ($state.current.name === 'employee-detail') {
            $scope.showBreadcrumb = true;
        }
        $scope.companyId = $stateParams.companyId;
        $scope.companyName = $stateParams.companyName;
        $scope.employeeId = $scope.active = $stateParams.employeeId;
        $scope.dataLoaded = false;
        $scope.employee = Employee.get({company_id: $scope.companyId, employee_id: $scope.employeeId}, function() {
            $scope.dataLoaded = true;
        });
        Timesheet.get({company_id: $scope.companyId, 'employee_id': $scope.employeeId}, function(result) {
            $scope.timesheets = result.items;
            $scope.noTimesheets = true;
            for (var i in $scope.timesheets) {
                $scope.noTimesheets = false;
            }
        });
        // active tab
        $scope.active = 'general';
        $scope.setActiveTab = function(tab, $event) {
            $scope.active = tab;
            $event.preventDefault();
        };
        
        // payslips
//        $http({
//            method: 'GET',
//            url: '/api/payroll/payslip?employee_id=' + $scope.employeeId
//       }).success(function(result){
//           $scope.payslips = result.payslips;
//       }).error(function(){
//           alert("Server error");
//       });
    }
]);
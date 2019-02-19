angular.module('App').controller('NavCtrl', [
    '$scope',
    '$rootScope',
    '$modal',
    function($scope, $rootScope, $modal) {
        $scope.companyId = null;
        $scope.active = 'company';
        $scope.data = {};
        $rootScope.$on('$stateChangeStart', function(event, toState, toParams, fromState, fromParams){
            var toStateName = toState.name;
            if (toStateName.indexOf('company') !== -1) {
                $scope.active = 'company';
            }
            if (toStateName.indexOf('employees') !== -1) {
                $scope.active = 'employees';
            }
            if (toStateName.indexOf('payruns') !== -1) {
                $scope.active = 'payruns';
            }
            if (toStateName.indexOf('timesheet') !== -1) {
                $scope.active = 'timesheet';
            }
        });
        $scope.$on('currentCompanyChanged', function() {
            if ($rootScope.currentCompany) {
                $scope.companyId = $rootScope.currentCompany.id;
                $scope.companyName = $rootScope.currentCompany.name;
            } else {
                $scope.companyId = null;
            }
        });
        $scope.showWarning = function() {
            var modalInstance = $modal.open({
                templateUrl: '/app/payroll/common/templates/modals/warning.html',
                controller: 'WarningCtrl'
            });
            modalInstance.result.then(function() {
                $rootScope.$broadcast('popupCompanyPicker');
            }, function() {
                $rootScope.$broadcast('popupCompanyPicker');
            });
        };
    }
]);
angular.module('App').controller('WarningCtrl', [
    '$scope',
    '$modalInstance',
    function($scope, $modalInstance) {
        $scope.ok = function() {
            $modalInstance.close();
        };
    }
]);
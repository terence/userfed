angular.module('App').controller('CompanyPickerCtrl', [
    '$scope',
    'Company',
    '$state',
    '$rootScope',
    '$timeout',
    function($scope, Company, $state, $rootScope, $timeout) {
        // need for tracking when userfed and payroll companies loading completed
        if (!$rootScope.loadingCompanyPhases) {
            $rootScope.loadingCompanyPhases = {
                payrollCompaniesLoaded: false
            };
        } else {
            $rootScope.loadingCompanyPhases.payrollCompaniesLoaded = false;
        }
        $scope.data = {selectedCompany : null};
        $rootScope.payrollCompanies = [];
        var loadCompanies = function() {
            Company.get({'page-size' : 50}, function(result) {
                $rootScope.payrollCompanies = result.items;
                $rootScope.loadingCompanyPhases.payrollCompaniesLoaded = true;
            });
        };
        loadCompanies();

        $scope.$watch("data.selectedCompany", function(newVal, oldVal) {
            if (!angular.equals(oldVal, newVal) && newVal && newVal.id) {
                $state.go("company-console", { companyId : newVal.id, companyName: newVal.name });
            }
        }, true);
        
        $scope.$on('companyUpdated', function() {
            loadCompanies();
        });
        
        $scope.$on('currentCompanyChanged', function() {
            $scope.data.selectedCompany = $rootScope.currentCompany;
        });
        
        $scope.$on('popupCompanyPicker', function() {
            // using $timeout to avoid error message: $digest already in progress
            $timeout(function() {
                angular.element(".company-picker-container").find(".select2-choice").trigger("click");
            }, 0);
        });
    }
]);
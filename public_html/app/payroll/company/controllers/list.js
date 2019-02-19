angular.module('App').controller('ListCompanyCtrl', [
    '$scope', '$rootScope', 'UserFed',
    function($scope, $rootScope, UserFed) {
        // reset company picker selected company to empty
        $rootScope.currentCompany = null;
        $rootScope.$broadcast('currentCompanyChanged');
        // need for tracking when userfed and payroll companies loading completed
        if (!$rootScope.loadingCompanyPhases) {
            $rootScope.loadingCompanyPhases = {
                userfedCompaniesLoaded: false
            };
        } else {
            $rootScope.loadingCompanyPhases.userfedCompaniesLoaded = false;
        }
        
        var init = function() {
                var spinner = new Spinner({
                lines: 13, // The number of lines to draw
                length: 20, // The length of each line
                width: 10, // The line thickness
                radius: 30, // The radius of the inner circle
                corners: 1, // Corner roundness (0..1)
                rotate: 0, // The rotation offset
                direction: 1, // 1: clockwise, -1: counterclockwise
                color: '#000', // #rgb or #rrggbb or array of colors
                speed: 1, // Rounds per second
                trail: 60, // Afterglow percentage
                shadow: false, // Whether to render a shadow
                hwaccel: false, // Whether to use hardware acceleration
                className: 'spinner', // The CSS class to assign to the spinner
                zIndex: 2e9, // The z-index (defaults to 2000000000)
                top: '200%', // Top position relative to parent
                left: '50%' // Left position relative to parent
            }).spin(document.getElementById('loading'));
            $scope.loading = true;
            var ufResource = UserFed.organisation.get(function() {
                $rootScope.userfedCompanies = ufResource.companies;
                // fake company logo
                for (var i in $rootScope.userfedCompanies) {
                    $rootScope.userfedCompanies[i].photo = "http://lorempixel.com/150/150/business/?" + Math.random();
                }
                spinner.stop();
                $scope.loading = false;
                $rootScope.loadingCompanyPhases.userfedCompaniesLoaded = true;
            });
        };
        init();
        $scope.$watch('loadingCompanyPhases', function(newValue) {
            if (newValue.userfedCompaniesLoaded && newValue.payrollCompaniesLoaded) {
                $scope.companies = {
                    'ready_for_payroll': [],
                    'not_ready_for_payroll': []
                };
                angular.forEach($rootScope.userfedCompanies, function(ufCompany, index) {
                    var search = $rootScope.payrollCompanies.filter(function(prCompany) {
                        return (parseInt(ufCompany.organisation_id) === parseInt(prCompany.uf_org_id));
                    });
                    if (search.length) {
                        $rootScope.userfedCompanies[index].payroll_setup = true;
                        $rootScope.userfedCompanies[index].payroll_data = search[0];
                        $scope.companies.ready_for_payroll.push($rootScope.userfedCompanies[index]);
                    } else {
                        $rootScope.userfedCompanies[index].payroll_setup = false;
                        $scope.companies.not_ready_for_payroll.push($rootScope.userfedCompanies[index]);
                    }
                });
            }
        }, true);
    }
]);
angular.module('App').controller('SetupCompanyCtrl', [
    '$scope',
    'Dictionary',
    'Company',
    '$state',
    '$stateParams',
    '$rootScope',
    function($scope, Dictionary, Company, $state, $stateParams, $rootScope) {
        if (!$rootScope.userfedCompanies) {
            $state.go("company-list");
            return;
        }
        var userfed_companyId = $stateParams.ufCompanyId;
        $scope.userfed_company = $rootScope.userfedCompanies.filter(function(company) {
            return parseInt(company.organisation_id) === parseInt(userfed_companyId);
        })[0];
        
        window.commonRequestErrorCallbackFunction = function() {
            $scope.saving = false;
            alert("Server error. Please try again later.");
        };
        var initData = function() {
            $scope.data = {
                is_active: true
            };
            $scope.payrun_schedules = { items:[] };
            $scope.saving = false;
        };
        // fetch list of payrun schedule
        var loadPayrunSchedules = function() {
            $scope.payrun_schedules = Dictionary.get({type:'payrun-schedule'});
        };
        var loadData = function() {
            loadPayrunSchedules();
        };
        var init = function() {
            initData();
            loadData();   
        };
        init();

        /**
         * event handler for close button follows selected item in ui-select element
         * @param string prop
         * @param event $event
         * @returns undefined
         */
        $scope.clearProperty = function(prop, $event) {
            $event.stopPropagation();
            $scope.data[prop] = null;
        };
        
        $scope.submit = function() {
            if ($scope.form.$invalid) {
                $timeout(function() {
                    // trigger input event of elements to show validation messages
                    var inputs = $("input").not(".ui-select-search, .ui-select-offscreen");
                    inputs.trigger("input");
                    $("select").trigger("change");
                }, 0);
                return;
            }
            var data = format(angular.copy($scope.data));
            data.uf_org_id = $scope.userfed_company.organisation_id;
            data.name = $scope.userfed_company.title;
            $scope.saving = true;
            
            Company.save(data, function() {
                $scope.saving = false;
                $state.go("company-list");
                $rootScope.$broadcast('companyUpdated');
            }, window.commonRequestErrorCallbackFunction);
        };
        
        /**
         * ensure data has correct format before it is sent to server
         *      - convert boolean value to int (1, 0)
         *      - with object type data, convert to normal type, using value of its id property
         * @param object data
         * @returns object
         */
        var format = function(data) {
            for (var i in data) {
                var prop = data[i];
                if (typeof prop === 'boolean') {
                    data[i] = (prop ? 1 : 0);
                }
                if (typeof prop === 'object' && prop && prop.id !== undefined) {
                    data[i] = prop.id;
                }
            }
            return data;
        };
    }
]);
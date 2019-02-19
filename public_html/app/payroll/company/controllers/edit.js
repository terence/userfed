angular.module('App').controller('EditCompanyCtrl', [
    '$scope',
    'data',
    'Company',
    'Dictionary',
    '$stateParams',
    '$timeout',
    '$state',
    function ($scope, data, Company, Dictionary, $stateParams, $timeout, $state) {
        $scope.companyId = $stateParams.companyId;
        $scope.companyName = $stateParams.companyName;
        
        $scope.data = data;
        
        $scope.clearProperty = function (prop, $event) {
            $event.stopPropagation();
            $scope.data[prop] = null;
        };

        var init = function () {
            $scope.payrun_schedules = Dictionary.get({type: 'payrun-schedule'});
        };

        init();

        /**
         * ensure data has correct format before it is sent to server
         *      - convert boolean value to int (1, 0)
         *      - with object type data, convert to normal type, using value of its id property
         * @param object data
         * @returns object
         */
       var format = function (data) {
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
        $scope.submit = function () {
            if ($scope.form.$invalid) {
                $timeout(function () {
                    // trigger input event of elements to show validation messages
                    var inputs = $("input").not(".ui-select-search, .ui-select-offscreen");
                    inputs.trigger("input");
                    $("select").trigger("change");
                }, 0);
                return;
            }
            var data = format(angular.copy($scope.data));

            $scope.saving = true;
            $scope.success = false;
            Company.update({id: data.id}, data, function () {
                $scope.saving = false;
                $scope.success = true;
                $timeout(function() {
                    $state.go("company-console", {
                        companyId: $scope.companyId,
                        companyName: $scope.companyName
                    });
                }, 1500);
            }, window.commonRequestErrorCallbackFunction);
        };
    }
]);
angular.module('App').controller('CompanyDetailsCtrl', [
    '$scope',
    'Dictionary',
    'Company',
    '$state',
    '$stateParams',
    '$timeout',
    'toaster',
    '$rootScope',
    function($scope, Dictionary, Company, $state, $stateParams, $timeout, toaster, $rootScope) {
        window.commonRequestErrorCallbackFunction = function() {
            $scope.saving = false;
            alert("Server error. Please try again later.");
        };
        var initData = function() {
            $scope.data = {
                isActive: true,
                useMyob: false,
                showPcLeave: false,
                accrueFamilyLeave: false,
                accrueRdo: false,
                payFromGuru: false,
                acirt: false,
                partner:null
            };
            $scope.banks = { items:[] };
            $scope.superPeriodTypes = { items:[] };
            $scope.payrunSchedules = { items:[] };
            $scope.partners = { items:[] };
            $scope.saving = false;
        };
        var isEditing = $scope.action.name === 'edit';
        var getItemObjectById = function(items, id) {
            var keepGoing = true;
            var result = null;
            angular.forEach(items, function(item) {
                if (keepGoing) {
                    if (id == item.id) {
                        keepGoing = false;
                        result = item;
                    }
                }
            });
            return result;
        };
        // fetch list of banks from db
        var loadBanks = function() {
            $scope.banks = Dictionary.get({type:'bank'}, function() {
                // pre-select bank
                if (isEditing) {
                    $scope.data.bank = getItemObjectById($scope.banks.items, $scope.data.bank);
                }
            });
        };
        // fetch list of Super Payment Period
        var loadSuperPeriodTypes = function() {
            $scope.superPeriodTypes = Dictionary.get({type:'super-period-type'});
        };
        // fetch list of payrun schedule
        var loadPayrunSchedules = function() {
            $scope.payrunSchedules = Dictionary.get({type:'payrun-schedule'}, function() {
                // pre-select bank
                if (isEditing) {
                    $scope.data.paySchedule = getItemObjectById($scope.payrunSchedules.items, $scope.data.paySchedule);
                }
            });
        };
        // fetch list of partners
        var loadPartners = function() {
            $scope.partners = Dictionary.get({type:'partner'}, function() {
                // pre-select bank
                if (isEditing) {
                    // partner returned from server is an object with id property, not a normal value as usual
                    $scope.data.partner = getItemObjectById($scope.partners.items, $scope.data.partner.id);
                }
            });
        };
        var loadData = function() {
            loadBanks();
            loadSuperPeriodTypes();
            loadPayrunSchedules();
            loadPartners();
        };
        var init = function() {
            initData();
            if ($scope.action.name === 'edit') {
                $scope.data = Company.get({id:$stateParams.companyId}, function () {
                    $scope.data.lslPercentage = parseFloat($scope.data.lslPercentage);
                    loadData();
                });
            } else {
                loadData();   
            }
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
            // server read deId key instead of abaId, so we copy the value abaId to deId
            data.deId = data.abaId;
            $scope.saving = true;
            
            if ($scope.action.name === 'add') {
                Company.save(data, function() {
                    $scope.saving = false;
                    $state.go("company-list");
                    $rootScope.$broadcast('companyUpdated');
                }, window.commonRequestErrorCallbackFunction);
            }
            if ($scope.action.name === 'edit') {
                data.id = $stateParams.companyId;
                // @todo: updating partner has not been implemented yet on server-side
                // so for now, do not send partner data on update
                delete data.partner;
                Company.update({id:data.id}, data, function() {
                    $scope.saving = false;
                    toaster.pop('success', "Update Company", "Company updated successfully!");
                    $rootScope.$broadcast('companyUpdated');
                }, window.commonRequestErrorCallbackFunction);
            }
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
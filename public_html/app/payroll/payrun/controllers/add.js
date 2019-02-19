angular.module('App').controller('PayrunsAddCtrl', [
    '$scope',
    '$stateParams',
    'Employee',
    function($scope, $stateParams, Employee) {
        $scope.companyId = $stateParams.companyId;
        $scope.companyName = $stateParams.companyName;
        $scope.isProccessDateDatePickerOpened = false;
        $scope.data = {
            proc: null
        };
        $scope.openProcessDateDatePicker = function($event) {
            $event.preventDefault();
            $event.stopPropagation();
            $scope.isProccessDateDatePickerOpened = true;
        };
        $scope.openPeriodEndingDatePicker = function($event) {
            $event.preventDefault();
            $event.stopPropagation();
            $scope.isPeriodEndingDatePickerOpened = true;
        };
        // Disable weekend selection
        $scope.disabledWeekend = function (date, mode) {
            return (mode === 'day' && (date.getDay() === 0 || date.getDay() === 6));
        };
        
        $scope.payslips = [];
        
        var fakeData = {
            rates : [
                {
                    amount: '65',
                    period: 'hour',
                    type: 'Primary',
                    standard: true,
                    ote: true,
                    quantity: 0
                },
                {
                    amount: '65',
                    period: 'hour',
                    type: 'Rec Leave affects Rec Leave',
                    standard: true,
                    ote: true,
                    quantity: 0
                },
                {
                    amount: '65',
                    period: 'hour',
                    type: 'Personal Carers Leave affects PC Leave',
                    standard: true,
                    ote: true,
                    quantity: 0
                },
                {
                    amount: '65',
                    period: 'hour',
                    type: 'RDO affects RDO',
                    standard: true,
                    ote: true,
                    quantity: 0
                },
                {
                    amount: '65',
                    period: 'hour',
                    type: 'Meal',
                    standard: false,
                    ote: true,
                    quantity: 0
                }
            ],
            pre_tax: '500.00',
            payg: '1023.00',
            post_tax: '0.00',
            super: '256.50'
        };
        $scope.employees = Employee.get({company_id: $scope.companyId}, function() {
            for (var i = 0; i < $scope.employees.items.length; i++) {
                angular.extend($scope.employees.items[i], fakeData);
            }
        });
        $scope.addPayslip = function() {
            $scope.isAddPayslipFormShown = true;
        };
        $scope.cancelAddPayslip = function() {
            $scope.isAddPayslipFormShown = false;
            clearAddingPayslipData();
        };
        var calculateGross = function(payslip) {
            var gross = 0;
            for (var i in payslip.rates) {
                var type = payslip.rates[i].type;
                if (type === 'Primary' || type === 'Meal') {
                    gross += payslip.rates[i].amount * payslip.rates[i].quantity;
                }
            }
            return gross;
        };
        var clearAddingPayslipData = function() {
            if ($scope.data.addingPayslip) {
                for (var i in $scope.data.addingPayslip.rates) {
                    $scope.data.addingPayslip.rates[i].quantity = 0;
                }
                $scope.data.addingPayslip.periodEnding = null;
                $scope.data.addingPayslip = null;
            }
        };
        $scope.isAddingEmployeeHasPayslipAddedToPayRun = function() {
            if (!$scope.data.addingPayslip) {
                return false;
            }
            for (var i = 0; i < $scope.payslips.length; i++) {
                var ps = $scope.payslips[i];
                if (ps.id == $scope.data.addingPayslip.id) {
                    return true;
                }
            }
            return false;
        };
        var addPayslipToPayRun = function() {
            var payslip = angular.copy($scope.data.addingPayslip);
            payslip.gross = calculateGross(payslip);
            if ($scope.isAddingEmployeeHasPayslipAddedToPayRun()) {
                for (var i = 0; i < $scope.payslips.length; i++) {
                    if ($scope.payslips[i].id == $scope.data.addingPayslip.id) {
                        $scope.payslips[i] = payslip;
                    }
                }
            } else {
                $scope.payslips.push(payslip);
            }
            clearAddingPayslipData();
        };
        $scope.addToPayRun = function() {
            addPayslipToPayRun();
            $scope.isAddPayslipFormShown = false;
        };
        $scope.addToPayRunAndAddAnother = function () {
            addPayslipToPayRun();
        };
        $scope.removePayslip = function($index) {
            $scope.payslips = $scope.payslips.filter(function(ps, index) {
                return $index != index;
            });
        };
    }
]);
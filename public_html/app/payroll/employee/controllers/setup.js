angular.module('App').controller('EmployeeSetupCtrl', [
    '$scope',
    '$stateParams',
    'Employee',
    'toaster',
    '$state',
    '$timeout',
    function($scope, $stateParams, Employee, toaster, $state, $timeout) {
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
        $scope.companyId = $stateParams.companyId;
        $scope.companyName = $stateParams.companyName;
        $scope.employeeId = $stateParams.employeeId;
        $scope.dataLoaded = false;
        $scope.employee = Employee.get({company_id: $scope.companyId, employee_id: $scope.employeeId}, function() {
            $scope.data = angular.copy($scope.employee);
            if ($scope.data.dob) {
                $scope.data.dob = $scope.data.dob.date.substring(0, 10);
            }
            if ($scope.data.contract) {
                if ($scope.data.contract.start) {
                    $scope.data.start = $scope.data.contract.start.date.substring(0, 10);
                }
                if ($scope.data.contract.duties) {
                    $scope.data.duties = $scope.data.contract.duties;
                }
            }
            if ($scope.data.state && $scope.data.state.id) {
                $scope.data.state = $scope.data.state.id;
            }
            if ($scope.data.postcode && $scope.data.postcode.id) {
                $scope.data.postcode = $scope.data.postcode.id;
            }
            $scope.dataLoaded = true;
        });
        $scope.datepickers = [];
        $scope.data = {};
        $scope.openDatepicker = function(name, $event) {
            $event.preventDefault();
            $event.stopPropagation();
            if (!$scope.datepickers[name]) {
                $scope.datepickers[name] = true;
            }
        };
        $scope.formFields = {
            employeeDetails: {
                // prefix with a1_, a2_, a3_ to tell angular repeat to render follow desired order
                // cos' angular repeat automatically order item in group by name
                // so if do not prefix, bankAccountDetails group will be rendered first, cos' 'b' is before 'p' in alphabet table
                a1_PersonalDetails: {
                    groupLabel: "Start Date",
                    fields: [
                        {
                            name: 'start',
                            type: 'datepicker',
                            label: 'Select start date',
                            placeholder: 'Pick a date',
                            labelWidth: 3,
                            inputWidth: 3
                        }
                    ]
                },
                a2_PersonalDetails: {
                    groupLabel: "Personal Details",
                    fields: [
                        {
                            name: 'firstname',
                            type: 'text',
                            label: 'First Name',
                            placeholder: 'First Name',
                            labelWidth: 3,
                            inputWidth: 5
                        },
                        {
                            name: 'lastname',
                            type: 'text',
                            label: 'Last Name',
                            placeholder: 'Last Name',
                            labelWidth: 3,
                            inputWidth: 5
                        },
                        {
                            name: 'preferred_name',
                            type: 'text',
                            label: 'Preferred Name',
                            placeholder: 'Preferred Name',
                            labelWidth: 3,
                            inputWidth: 5
                        },
                        {
                            name: 'duties',
                            type: 'text',
                            label: 'Job Title',
                            placeholder: 'Job Title',
                            labelWidth: 3,
                            inputWidth: 5
                        },
                        {
                            name: 'tfn',
                            type: 'text',
                            label: 'Tax File Number',
                            placeholder: 'Tax File Number',
                            labelWidth: 3,
                            inputWidth: 5
                        },
                        {
                            name: 'dob',
                            type: 'datepicker',
                            label: 'Date of Birth',
                            placeholder: 'Date of Birth',
                            labelWidth: 3,
                            inputWidth: 3
                        },
                        {
                            name: 'is_active',
                            type: 'switcher',
                            label: 'Status',
                            onLabel: 'Active',
                            offLabel: 'Inactive',
                            labelWidth: 3,
                            inputWidth: 3
                        }
                    ]
                },
                a3_ContactDetails: {
                    groupLabel: "Contact Details",
                    fields: [
                        {
                            name: 'addr_line1',
                            type: 'text',
                            label: 'Address Line 1',
                            placeholder: 'Address Line 1',
                            labelWidth: 3,
                            inputWidth: 5
                        },
                        {
                            name: 'addr_line2',
                            type: 'text',
                            label: 'Address Line 2',
                            placeholder: 'Address Line 2',
                            labelWidth: 3,
                            inputWidth: 5
                        },
                        {
                            name: 'place',
                            type: 'text',
                            label: 'Town/City',
                            placeholder: 'Town/City',
                            labelWidth: 3,
                            inputWidth: 5
                        },
                        {
                            name: 'state',
                            type: 'select',
                            label: 'State',
                            nullOptionText: '',
                            options: [
                                {id: 1, text: 'ACT'},
                                {id: 2, text: 'NSW'},
                                {id: 4, text: 'NT'},
                                {id: 9, text: 'QLD'},
                                {id: 7, text: 'SA'},
                                {id: 6, text: 'TAS'},
                                {id: 3, text: 'VIC'},
                                {id: 8, text: 'WA'}
                            ],
                            labelWidth: 3,
                            inputWidth: 3
                        },
                        {
                            name: 'postcode',
                            type: 'text',
                            label: 'Postcode',
                            placeholder: 'Postcode',
                            labelWidth: 3,
                            inputWidth: 3
                        },
                        {
                            name: 'email',
                            type: 'email',
                            label: 'Email',
                            placeholder: 'john@doe.com',
                            labelWidth: 3,
                            inputWidth: 5
                        },
                        {
                            name: 'home_phone',
                            type: 'text',
                            label: 'Phone',
                            placeholder: 'Phone',
                            labelWidth: 3,
                            inputWidth: 3
                        },
                        {
                            name: 'mobile_phone',
                            type: 'text',
                            label: 'Mobile',
                            placeholder: 'Mobile',
                            labelWidth: 3,
                            inputWidth: 3
                        }
                    ]
                }
            },
            taxSetup: {
                a1_PaygInformation: {
                    groupLabel: "PAYG Information",
                    fields: [
                        {
                            name: 'has_tfn',
                            type: 'switcher',
                            label: 'Has the payee provided a Tax File Number (TFN)?',
                            labelWidth: 7,
                            inputWidth: 3
                        },
                        {
                            name: 'exempt_tfn',
                            type: 'switcher',
                            label: 'If the payee has not provided a TFN, is the payee exempt from needing a TFN?',
                            labelWidth: 7,
                            inputWidth: 3
                        },
                        {
                            name: 'resident',
                            type: 'switcher',
                            label: 'Is the payee an Australian Resident?',
                            labelWidth: 7,
                            inputWidth: 3
                        },
                        {
                            name: 'claim_threshold',
                            type: 'switcher',
                            label: 'Has the payee claimed the Tax Free Threshold for this employment?',
                            labelWidth: 7,
                            inputWidth: 3
                        }
                    ]
                },
                a2_MedicareLevyVariation: {
                    groupLabel: "Medicare Levy Variation",
                    fields: [
                        {
                            name: 'mc_declaration',
                            type: 'switcher',
                            label: 'Has the payee provided a Medicare levy variation declaration?',
                            labelWidth: 7,
                            inputWidth: 3
                        },
                        {
                            name: 'mc_exemption_type',
                            type: 'select',
                            label: 'Payee claiming exemption or variation from the levy?',
                            labelWidth: 7,
                            inputWidth: 3,
                            labelClass: 'left-indent',
                            showOn: {'field':'mc_declaration', 'value' : true},
                            nullOptionText: '',
                            options: [
                                {id: 1, text: 'No'},
                                {id: 2, text: 'Half'},
                                {id: 3, text: 'Full'}
                            ]
                        },
                        {
                            name: 'mc_reduction',
                            type: 'switcher',
                            label: 'Payee claiming reduced amount of levy?',
                            labelWidth: 7,
                            inputWidth: 3,
                            labelClass: 'left-indent',
                            showOn: {'field':'mc_declaration', 'value' : true}
                        },
                        {
                            name: 'mc_spouse',
                            type: 'switcher',
                            label: 'Does the payee have a spouse?',
                            labelWidth: 7,
                            inputWidth: 3,
                            labelClass: 'left-indent',
                            showOn: {'field':'mc_declaration', 'value' : true}
                        },
                        {
                            name: 'mc_income_met',
                            type: 'switcher',
                            label: 'Combined income less than applicable amount? (Question 10 on variation form)',
                            labelWidth: 7,
                            inputWidth: 3,
                            labelClass: 'left-indent',
                            showOn: {'field':'mc_declaration', 'value' : true}
                        },
                        {
                            name: 'mc_num_children',
                            type: 'number',
                            label: 'Number of dependent children claimed?',
                            labelWidth: 7,
                            inputWidth: 2,
                            labelClass: 'left-indent',
                            showOn: {'field':'mc_declaration', 'value' : true}
                        }
                    ]
                },
                a3_Miscellaneous: {
                    groupLabel: "Miscellaneous",
                    fields: [
                        {
                            name: 'leave_loading',
                            type: 'switcher',
                            label: 'Is the payee entitled to annual leave loading?',
                            labelWidth: 7,
                            inputWidth: 3
                        },
                        {
                            name: 'hecs',
                            type: 'switcher',
                            label: 'Does the payee have an accumulated Higher Education Loan Programme (HELP) debt?',
                            labelWidth: 7,
                            inputWidth: 3
                        },
                        {
                            name: 'sfss',
                            type: 'switcher',
                            label: 'Does the payee have an accumulated Financial Supplement (SFSS) debt?',
                            labelWidth: 7,
                            inputWidth: 3
                        }
                    ]
                },
                a4_ExtraPayg: {
                    groupLabel: "Extra PAYG",
                    fields: [
                        {
                            name: 'extraPayg',
                            type: 'number',
                            label: 'Amount of extra PAYG to withhold?:',
                            inputPrefix: '$',
                            labelWidth: 3,
                            inputWidth: 2
                        }
                    ]
                },
                a5_FixedPayScale: {
                    groupLabel: "Fixed PAYG Scale",
                    fields: [
                        {
                            name: 'fixedScale',
                            type: 'select',
                            label: 'Select a fixed scale',
                            // 3 and 5 are number which will be used in column bootstrap classes
                            labelWidth: 3,
                            inputWidth: 3,
                            options: [
                                {id: 33, text: 'Fixed 31%'},
                                {id: 27, text: 'Fixed 45%'},
                                {id: 28, text: 'Fixed 45% + LL'}
                            ]
                        }
                    ]
                }
            }
        };

        $scope.data = {
            pay_rates : [],
            allowances: []
        };
        $scope.addPayRate = function() {
            if (!$scope.data.pay_rates) {
                $scope.data.pay_rates = [];
            }
            $scope.data.pay_rates.push({
                rate_period: 1
            });
        };
        $scope.addAllowance = function() {
            if (!$scope.data.allowances) {
                $scope.data.allowances = [];
            }
            $scope.data.allowances.push({
                rate_period: 1
            });
        };
        $scope.removePayrate = function($index) {
            if ($scope.data.pay_rates.length) {
                $scope.data.pay_rates = $scope.data.pay_rates.filter(function(item, index) {
                    return $index != index;
                });
            }
        };
        $scope.removeAllowance = function($index) {
            if ($scope.data.allowances.length) {
                $scope.data.allowances = $scope.data.allowances.filter(function(item, index) {
                    return $index != index;
                });
            }
        };
        $scope.submit = function() {
            var data = angular.copy($scope.data);
            data.company_id = $scope.companyId;
            $scope.saving = true;
            Employee.update({employee_id: data.id}, data, function(result) {
                $scope.saving = false;
                if (!result.error) {
                    toaster.pop('success', "Setup Employee", "Setup employee completes successfully! Going back to the list.");
                    $timeout(function() {
                        $state.go("employees", {companyName: $scope.companyName, companyId: $scope.companyId});
                    }, 3000);
                } else {
                    toaster.pop('error', "Setup Employee", "Validation failed. Please check your input.");
                }
            }, function() {
                toaster.pop('error', "Server error", "Please try again later.");
                $scope.saving = false;
            });
        };
    }
]);

angular.module('App').controller('EmployeeSetupTabController', [
    '$scope',
    '$state',
    function($scope, $state) {
        $scope.activeTab = 0;
        $scope.tabs = [
            { id: 0, name: 'details', label: 'Employee Details', template: '/app/payroll/employee/templates/partials/details.html' },
            { id: 1, name: 'tax', label: 'Tax Setup', template: '/app/payroll/employee/templates/partials/tax.html' }//,
//            { id: 2, name: 'pay-rates', label: 'Pay Rates', template: '/app/payroll/employee/templates/partials/pay-rates.html' },
//            { id: 3, name: 'leave', label: 'Leave Entitlements', template: '/app/payroll/employee/templates/partials/leave.html' },
//            { id: 4, name: 'super', label: 'Superannuation', template: '/app/payroll/employee/templates/partials/super.html' }
        ];
        $scope.selectTab = function(tabId, $event) {
            $event.preventDefault();
            $scope.activeTab = tabId;
        };
        $scope.isActive = function(tabId) {
            return $scope.activeTab === tabId;
        };
        $scope.next = function() {
            if ($scope.nextable()) {
                $scope.activeTab += 1;
            }
        };
        $scope.previous = function() {
            if ($scope.previousable()) {
                $scope.activeTab -= 1;
            }
        };
        $scope.nextable = function() {
            return $scope.activeTab < $scope.tabs.length - 1;
        };
        $scope.previousable = function() {
            return $scope.activeTab > 0;
        };
        $scope.cancel = function() {
            $state.go("employees", {
                companyId: $scope.companyId,
                companyName: $scope.companyName
            });
        };
    }
]);
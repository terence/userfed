'use strict';

angular.module('App', [
    'ui.bootstrap',
    'toggle-switch',
    'ui.select',
    'ui.router',
    'ngResource',
    'toaster',
    'angularFileUpload',
    'ngSanitize'
]).config(function(uiSelectConfig, $stateProvider, $urlRouterProvider, datepickerConfig, datepickerPopupConfig) {
    // datepicker config
    datepickerConfig.showWeeks = false;
    datepickerPopupConfig.showButtonBar = false;
    // ui select config
    uiSelectConfig.theme = 'select2';
    // For any unmatched url, redirect to /
    $urlRouterProvider.otherwise("");
    // Now set up the states
    $stateProvider
        .state('company-list', {
            url: "",
            templateUrl: "/app/payroll/company/templates/list.html",
            controller: 'ListCompanyCtrl'
        })
        .state('company-add', {
            url: "/add",
            templateUrl: "/app/payroll/company/templates/add.html",
            controller: 'AddCompanyCtrl'
        })
        .state('company-setup', {
            url: "/company/setup/:ufCompanyId",
            templateUrl: "/app/payroll/company/templates/setup.html",
            controller: 'SetupCompanyCtrl'
        })
        .state('company-edit', {
            url: "/company/edit/:companyName/:companyId",
            templateUrl: "/app/payroll/company/templates/edit.html",
            controller: 'EditCompanyCtrl',
            resolve: {
                data: ['Company','$stateParams', function(Company, $stateParams) {
                    return Company.get({ id: $stateParams.companyId });
                }]
            }
        })
        .state('company-console', {
            url: "/company/:companyName/:companyId",
            templateUrl: "/app/payroll/company/templates/console.html",
            controller: 'CompanyConsoleCtrl'
        })
        .state('employees', {
            url: "/:companyName/:companyId/employees",
            templateUrl: "/app/payroll/employee/templates/list.html",
            controller: 'EmployeesListCtrl'
        })
        .state('employees.detail', {
            url: "/:employeeId",
            views: {
                "employee-detail" : {
                    templateUrl: "/app/payroll/employee/templates/view-detail.html",
                    controller: 'EmployeesViewDetailCtrl'
                }
            }
        })
//        .state('employees.setup', {
//            url: "/:employeeId",
//            views: {
//                "employee-detail" : {
//                    templateUrl: "/app/payroll/employee/templates/setup.html",
//                    controller: 'EmployeeSetupCtrl'
//                }
//            }
//        })
//        .state('employees-add', {
//            url: "/:companyName/:companyId/employees/add",
//            templateUrl: "/app/payroll/employee/templates/add.html",
//            controller: 'EmployeesAddCtrl'
//        })
        .state('employee-detail', {
            url: "/:companyName/:companyId/employees/view/:employeeId",
            templateUrl: "/app/payroll/employee/templates/view-detail.html",
            controller: 'EmployeesViewDetailCtrl'
        })
        .state('employee-setup', {
            url: "/:companyName/:companyId/employees/setup/:employeeId",
            templateUrl: "/app/payroll/employee/templates/setup.html",
            controller: 'EmployeeSetupCtrl'
        })
        .state('payruns', {
            url: "/:companyName/:companyId/payruns",
            templateUrl: "/app/payroll/payrun/templates/index.html",
            controller: 'PayrunsIndexCtrl'
        })
        .state('payruns-add', {
            url: "/:companyName/:companyId/payruns/add",
            templateUrl: "/app/payroll/payrun/templates/add.html",
            controller: 'PayrunsAddCtrl'
        })
        .state('payruns-detail', {
            url: "/:companyName/:companyId/payruns/detail/:payrunId",
            templateUrl: "/app/payroll/payrun/templates/detail.html",
            controller: 'PayrunsDetailCtrl'
        })
        .state('company-timesheet', {
            url: "/:companyName/:companyId/timesheet",
            templateUrl: "/app/payroll/company/templates/timesheet.html",
            controller: 'CompanyTimesheetCtrl'
        })
    ;
});
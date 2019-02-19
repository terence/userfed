angular.module('App').controller('EmployeesAddCtrl', [
    '$scope',
    '$stateParams',
    'Employee',
    'toaster',
    '$state',
    '$timeout',
    function($scope, $stateParams, Employee, toaster, $state, $timeout) {
        $scope.companyId = $stateParams.companyId;
        $scope.companyName = $stateParams.companyName;
        $scope.datepickers = [];
        $scope.data = {};
        $scope.openDatepicker = function(name, $event) {
            $event.preventDefault();
            $event.stopPropagation();
            if (!$scope.datepickers[name]) {
                $scope.datepickers[name] = true;
            }
        };
        $scope.payRateOptions = [{id:"16678",text:"Adjustment (Non-Standard)"},{id:"16677",text:"Adjustment (Standard)"},{id:"171",text:"Back Pay (Non Standard)"},{id:"170",text:"Back Pay (Standard)"},{id:"16749",text:"Bonus (non-standard)"},{id:"4",text:"Call Out"},{id:"16668",text:"Christmas Bonus"},{id:"12",text:"Commission"},{id:"111",text:"Compassionate Leave"},{id:"21",text:"Employment Termination Payment"},{id:"19",text:"Family Leave"},{id:"256",text:"Higher Duties (Non Standard)"},{id:"255",text:"Higher Duties (Standard)"},
{id:"548",text:"Holiday Leave Loading"},{id:"15",text:"Leave Loading - Payout"},{id:"20",text:"LSL - Payout"},{id:"3",text:"On Call"},{id:"2",text:"Overtime x 1.5"},{id:"11",text:"Overtime x 2"},{id:"22",text:"Paid Parental Leave"},{id:"112",text:"Payment in lieu"},{id:"1",text:"Primary"},{id:"27",text:"RDO - payout"},{id:"13",text:"Rec Leave - Payout"},{id:"26",text:"Redundancy (Tax Free)"},{id:"10",text:"Secondary"},{id:"24",text:"Workers Comp (non-OTE + non-standard)"},{id:"16852",text:"Workers Comp (non-OTE + standard)"},
{id:"16851",text:"Workers Comp (OTE + non-standard)"},{id:"23",text:"Workers Comp (OTE + standard)"}];
        $scope.allowanceOptions = [{id:"25",text:"Meal allowance - OTE + PAYG"},{id:"16753",text:"Motor Vehicle Allowance - No OTE + PAYG allowance - NO OTE, PAYG"},{id:"16752",text:"Motor Vehicle Allowance - No OTE, No PAYG allowance - NO OTE, NO PAYG"},{id:"16755",text:"Motor Vehicle Allowance - OTE + PAYG allowance - OTE + PAYG"},{id:"16754",text:"Motor Vehicle Allowance - OTE, No PAYG allowance - OTE, no PAYG"},{id:"14",text:"Phone allowance - OTE, no PAYG"},{id:"18",text:"Productivity allowance - OTE + PAYG"},{id:"6",text:"Travel allowance - OTE, no PAYG"},
{id:"17",text:"Travel allowance - OTE + PAYG"}];
        $scope.formFields = {
            employeeDetails: {
                // prefix with a1_, a2_, a3_ to tell angular repeat to render follow desired order
                // cos' angular repeat automatically order item in group by name
                // so if do not prefix, bankAccountDetails group will be rendered first, cos' 'b' is before 'p' in alphabet table
                a1_PersonalDetails: {
                    groupLabel: "Start Date",
                    fields: [
                        {
                            name: 'pre_fy',
                            type: 'switcher',
                            label: 'Did this employment start before 1 July 2014',
                            // 3 is number which will be used in column bootstrap classes
                            labelWidth: 3,
                            inputWidth: 3
                        },
                        {
                            name: 'startDate',
                            type: 'datepicker',
                            label: 'Select start date',
                            placeholder: 'Pick a date',
                            labelWidth: 3,
                            inputWidth: 3
                        },
                        {
                            name: 'e_status',
                            type: 'select',
                            label: 'Employment Status',
                            nullOptionText: '',
                            options: [
                                {id: 1, text: 'Permanent'},
                                {id: 2, text: 'Casual'},
                                {id: 3, text: 'Leave Without Pay'},
                                {id: 4, text: 'Inactive'}
                            ],
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
                            name: 'dateOfBirth',
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
                            name: 'street1',
                            type: 'text',
                            label: 'Address Line 1',
                            placeholder: 'Address Line 1',
                            labelWidth: 3,
                            inputWidth: 5
                        },
                        {
                            name: 'street2',
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
                            name: 'addr_state',
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
                },
                a4_BankAccountDetails: {
                    groupLabel: "Bank Account Details",
                    fields: [
                        {
                            name: 'bank',
                            type: 'text',
                            label: 'Financial Institution',
                            placeholder: 'Financial Institution',
                            labelWidth: 3,
                            inputWidth: 5
                        },
                        {
                            name: 'account_name',
                            type: 'text',
                            label: 'Account Name',
                            placeholder: 'Account Name',
                            labelWidth: 3,
                            inputWidth: 5
                        },
                        {
                            name: 'bsb',
                            type: 'text',
                            label: 'BSB',
                            placeholder: 'BSB',
                            labelWidth: 3,
                            inputWidth: 3
                        },
                        {
                            name: 'account_number',
                            type: 'text',
                            label: 'Account Number',
                            placeholder: 'Account Number',
                            labelWidth: 3,
                            inputWidth: 5
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
            },
            superannuation: {
                a1_Information: {
                    fields: [
                        {
                            name: 'sgc',
                            type: 'switcher',
                            label: 'Is the employee eligible for Superannuation Guarantee contributions?',
                            labelWidth: 7,
                            inputWidth: 3
                        },
                        {
                            name: 'sgc_perc',
                            type: 'number',
                            label: 'Pay a higher SGC rate for this employee (leave blank to use default):?',
                            inputPostfix: '%',
                            labelWidth: 7,
                            inputWidth: 3
                        },
                        {
                            name: 'flat',
                            type: 'number',
                            label: 'Does this employee have a fixed contribution amount per pay period (overrides SGC)?',
                            inputPrefix: '$',
                            labelWidth: 7,
                            inputWidth: 3
                        },
                        {
                            name: 'post_tax',
                            type: 'number',
                            label: 'Post-tax fixed contribution amount per payslip?',
                            inputPrefix: '$',
                            labelWidth: 7,
                            inputWidth: 3
                        }
                    ]
                },
                a2_SalarySacrifice: {
                    groupLabel: "Salary Sacrifice",
                    fields: [
                        {
                            name: 'salsac',
                            type: 'switcher',
                            label: 'Is the employee Salary Sacrificing Superannuation?',
                            labelWidth: 7,
                            inputWidth: 3
                        },
                        {
                            name: 'ss_set',
                            type: 'number',
                            label: 'Set amount per pay:',
                            inputPrefix: '$',
                            labelWidth: 7,
                            inputWidth: 3,
                            labelClass: 'left-indent',
                            showOn: {'field':'salsac', 'value' : true}
                        },
                        {
                            name: 'ss_perc',
                            type: 'number',
                            label: 'Percentage of gross per pay:',
                            inputPostfix: '%',
                            labelWidth: 7,
                            inputWidth: 3,
                            labelClass: 'left-indent',
                            showOn: {'field':'salsac', 'value' : true}
                        }
                    ]
                },
                a3_FundDetails: {
                    groupLabel: "Fund Details",
                    fields: [
                        {
                            name: 'is_new_fund_used',
                            type: 'switcher',
                            label: 'Use a new fund?',
                            labelWidth: 3,
                            inputWidth: 3
                        },
                        {
                            name: 'fund',
                            type: 'ui-select',
                            label: 'Super Fund:',
                            labelWidth: 3,
                            inputWidth: 4,
                            options: [{id:"63",text:"A & S POLLARD SUPER"},{id:"69",text:"A //&// S POLLARD SUPER"},{id:"70",text:"A ///&/// S POLLARD SUPER"},{id:"71",text:"A ////&//// S POLLARD SUPER"},{id:"44",text:"AGEST"},{id:"101",text:"AGEST Super"},{id:"105",text:"AGEST Super Fund"},{id:"123",text:"AGEST SUPER PTY LTD"},{id:"244",text:"Amist Super"},{id:"17",text:"AMP"},{id:"144",text:"AMP - Flexibe Lifetime Super"},{id:"186",text:"AMP - Flexible Lifetime Super"},{id:"72",text:"AMP Custom Super"},{id:"158",text:"AMP Flexible"},
{id:"60",text:"AMP Flexible Lifetime Super"},{id:"167",text:"AMP Flexible Super - Can Bpay"},{id:"175",text:"AMP Life"},{id:"148",text:"AMP Retirement Fund"},{id:"134",text:"AMP retirement savings account"},{id:"183",text:"AMP S.P.I.N. #AMPO195AU"},{id:"226",text:"AMP Signature Super"},{id:"166",text:"AMP Super"},{id:"191",text:"AMP Super Directions Fund"},{id:"277",text:"ANZ Smart Choice"},{id:"238",text:"ANZ Smart Choice Super"},{id:"261",text:"ANZ Smart Choice Super (OnePath Master Fund)"},{id:"215",
text:"Anz Smart Choice Super Fund"},{id:"216",text:"ANZ Super Savings"},{id:"131",text:"ANZ superannuation Savings Account"},{id:"201",text:"Apex Super Fund"},{id:"251",text:"ARF"},{id:"207",text:"ASGARD"},{id:"233",text:"Asgard Super"},{id:"122",text:"Asset Super"},{id:"142",text:"Aust Super"},{id:"203",text:"Australia Super"},{id:"145",text:"Australian"},{id:"75",text:"Australian Catholic Superannuation Fund "},{id:"87",text:"Australian Child Care Super"},{id:"108",text:"Australian Child Care Super Fund"},
{id:"93",text:"Australian Childcare Super"},{id:"109",text:"Australian Childcare Super Fund"},{id:"94",text:"Australian Fund"},{id:"19",text:"Australian Super (STA0100AU)"},{id:"154",text:"Australian Super - in her madien name Sheeden"},{id:"99",text:"Australian Super Fund"},{id:"213",text:"AustralianSuper"},{id:"38",text:"AvSuper"},{id:"43",text:"AXA"},{id:"164",text:"AXA Generations Personal Super"},{id:"116",text:"AXA Super"},{id:"252",text:"Bendigo Smart Start Super"},{id:"235",text:"Birdlyn Super"},
{id:"223",text:"Briggs Super (AB12CD34)"},{id:"98",text:"BT - Financial Group"},{id:"51",text:"BT Business Super  (WFS0112AU)"},{id:"181",text:"BT Financial (BTA0137)"},{id:"268",text:"BT Financial Fund Super"},{id:"41",text:"BT Financial Group"},{id:"257",text:"BT Life Time Super"},{id:"66",text:"BT Lifetime Super"},{id:"132",text:"BT Super"},{id:"37",text:"BT Super for Life"},{id:"112",text:"BT-BUSINESS SUPER"},{id:"28",text:"Care Super (CAR0100AU)"},{id:"128",text:"Caresuper"},{id:"117",text:"Catholic Superannuation & Retirement Fund "},
{id:"42",text:"Catholic Superannuation Fund"},{id:"39",text:"CBUS"},{id:"114",text:"CBUS Super"},{id:"147",text:"CBUS Super Fund"},{id:"168",text:"Child Care Supeer"},{id:"103",text:"Child Care Super"},{id:"124",text:"Childcare Super"},{id:"209",text:"Christian Super"},{id:"32",text:"Club Plus (CLB0100AU)"},{id:"247",text:"Club Plus Super"},{id:"271",text:"Club Plus Superannuation"},{id:"249",text:"Club Plus Superannution"},{id:"59",text:"COLONIAL FIRST FUND"},{id:"50",text:"Colonial First State"},
{id:"250",text:"Colonial First State Super"},{id:"179",text:"Colonial First State Super First Choice Personal Super"},{id:"61",text:"Colonial Select Personal Super"},{id:"169",text:"Colonial Super Retirement Fund"},{id:"56",text:"COMM. BANK RETIREMENT ACC."},{id:"220",text:"Commonwealth Bank"},{id:"236",text:"Commonwealth Bank of Australia"},{id:"210",text:"Commonwealth Bank Superannuation Acc"},{id:"62",text:"Commonwealth personal"},{id:"219",text:"Commonwealth Super Savings Account"},{id:"97",text:"ConCept One"},
{id:"270",text:"Consalting Family Super"},{id:"276",text:"Crescent Wealth Super"},{id:"239",text:"Defence Bank Ltd"},{id:"162",text:"Develin Super"},{id:"222",text:"Develin Super Fund"},{id:"275",text:"DMA Super"},{id:"110",text:"EmPlus"},{id:"92",text:"Emplus Superannuation"},{id:"80",text:"Energy Industries Supperannuation Scheme"},{id:"170",text:"Equity Trustees Emplus Super"},{id:"143",text:"ESS Super"},{id:"192",text:"Essential Super (FSF1332AU)"},{id:"266",text:"Fiducian Superannuation Services"},
{id:"57",text:"First State Super (FSS0100)"},{id:"230",text:"Gabodi"},{id:"245",text:"GESB"},{id:"127",text:"Global AXA Group"},{id:"100",text:"Guild"},{id:"78",text:"Guild Super (GR401)"},{id:"217",text:"Guild Super Fund"},{id:"55",text:"Guild Superannuation"},{id:"107",text:"Guild Superannuation Fund"},{id:"193",text:"GuildSuper"},{id:"173",text:"Halcyon 633 Superannuation Fund"},{id:"172",text:"HEALTH SUPER"},{id:"263",text:"Hest"},{id:"33",text:"HESTA"},{id:"84",text:"Hesta Super"},{id:"152",
text:"Hesta Super Fund"},{id:"95",text:"Hesta Superannuation"},{id:"104",text:"Hesta Superannuation Fund"},{id:"126",text:"Hester"},{id:"224",text:"Honeypot Super Fund"},{id:"259",text:"Host"},{id:"3",text:"Host Plus"},{id:"242",text:"Host Plus Super"},{id:"269",text:"Host Super"},{id:"81",text:"Hostplus"},{id:"125",text:"HostPlus Super"},{id:"198",text:"Hostplus Superannuation Fund"},{id:"133",text:"ING"},{id:"188",text:"ING Direct"},{id:"240",text:"ING Direct Super (TCS0012AU)"},{id:"177",text:"ING Integra"},
{id:"205",text:"ING Life Limited"},{id:"221",text:"ING Living Super"},{id:"47",text:"Integra Super"},{id:"45",text:"Intrust Super"},{id:"254",text:"IOFF Super"},{id:"130",text:"Ioof"},{id:"232",text:"IOOF Pursuit Focus"},{id:"161",text:"JOIN TO REST"},{id:"160",text:"JOIN TO REST IF NEED BE"},{id:"27",text:"Jon Test Fund"},{id:"194",text:"Kelen Super Fund"},{id:"214",text:"kelly superfund"},{id:"211",text:"kellys superfund"},{id:"225",text:"Kinetic"},{id:"199",text:"Kinetic Super"},{id:"208",text:"Kinetic Superannuation"},
{id:"88",text:"Legal Super"},{id:"113",text:"LegalSuper"},{id:"190",text:"Local Government Super"},{id:"185",text:"Local Government Super Fund Id LGDIVA"},{id:"74",text:"Local Government Superannuation Scheme"},{id:"115",text:"LUCRF"},{id:"241",text:"LUCRF Super"},{id:"197",text:"LucrfSuper"},{id:"228",text:"LUCRP super"},{id:"196",text:"Macquarie Wrap"},{id:"4",text:"Macquarie Wrap Superannuation"},{id:"136",text:"Meander Sup"},{id:"258",text:"Meat Industry Employees Super"},{id:"272",text:"Media Super"},
{id:"178",text:"Meikle Family Super Fund"},{id:"237",text:"Mercer Smart Superfund"},{id:"253",text:"Mercer Super Trust"},{id:"120",text:"Mercer Wealth Solutions"},{id:"204",text:"MIC Super"},{id:"79",text:"MLC"},{id:"141",text:"MLC Masterkey"},{id:"129",text:"MLC Masterkey Personal Super"},{id:"267",text:"MLC Masterkey Super"},{id:"260",text:"MLC Super"},{id:"29",text:"MLC Super Fund"},{id:"40",text:"MTAA"},{id:"68",text:"MTAA SUPER FUND"},{id:"77",text:"My Super Fund"},{id:"91",text:"MY SUPER PROVIDER"},
{id:"264",text:"Netwealth"},{id:"202",text:"Netwealth Superannuation"},{id:"58",text:"NGS"},{id:"278",text:"North Personal Super Plan (NMS0001AU)"},{id:"273",text:"North Super"},{id:"111",text:"Oasis Super"},{id:"231",text:"One Path (MMF0146AU)"},{id:"139",text:"One Path  Integra Super"},{id:"53",text:"One Path Master Fund "},{id:"265",text:"One Path Super"},{id:"156",text:"OnePath"},{id:"146",text:"OnePath Masterfund"},{id:"189",text:"Pharmacy Guild"},{id:"234",text:"PLUM (PSN0100AU)"},{id:"182",
text:"portfolio solutions"},{id:"35",text:"Prime Super"},{id:"36",text:"PSSAap"},{id:"153",text:"PSSap"},{id:"200",text:"Public Sector Superannuation accumulation plan (PSSap)"},{id:"119",text:"R.;E.S.T"},{id:"149",text:"R.E,S.T"},{id:"83",text:"R.E.S.T"},{id:"138",text:"R.E.S.T Super"},{id:"118",text:"R.E.S.T Superannuation"},{id:"54",text:"R.E.S.T. Superannuation"},{id:"163",text:"RBF"},{id:"73",text:"Recruitment Super"},{id:"121",text:"Recruitments Super"},{id:"65",text:"REST (RES0103)"},{id:"206",
text:"REST -  Retail Employees"},{id:"174",text:"REST - Retail Employees Superannuation Trust"},{id:"187",text:"Rest Industries Super"},{id:"159",text:"Rest Industry"},{id:"151",text:"Rest Industry Super"},{id:"86",text:"Rest Super"},{id:"106",text:"Rest Super Fund"},{id:"2",text:"Rest Superannuation"},{id:"229",text:"Russel Super"},{id:"64",text:"Russell Future Fund"},{id:"184",text:"Russell Super Sol"},{id:"274",text:"ShakeyPharm Super"},{id:"171",text:"Simple Super Rollover Fund"},{id:"5",text:"Spectrum Super"},
{id:"46",text:"Statewide Super"},{id:"90",text:"Strategy Retirement Fund"},{id:"67",text:"Summit"},{id:"76",text:"Sun Super"},{id:"49",text:"Sunsuper (Sunsuper)"},{id:"227",text:"Sunsuper Pty Ltd"},{id:"137",text:"Super"},{id:"195",text:"Super Directions Fund"},{id:"155",text:"Superwrap - Personal Super Plan"},{id:"262",text:"Synergy Super"},{id:"248",text:"Tasplan"},{id:"82",text:"TBA"},{id:"246",text:"Telstra Super (TLS0100AU)"},{id:"102",text:"Test"},{id:"140",text:"The Develin Super Fund"},{id:"89",
text:"The McDonald Superannuation Fund"},{id:"48",text:"The Meat Industry Employees Superfund"},{id:"218",text:"The Trustee For First State Superannuation Scheme"},{id:"1",text:"To be advised"},{id:"52",text:"Tommerran Super Fund"},{id:"31",text:"Uni Super"},{id:"256",text:"Unisuper"},{id:"96",text:"Vic Super"},{id:"150",text:"Victorian Independant School Superannuation Fund"},{id:"180",text:"Virgin"},{id:"165",text:"Virgin Super"},{id:"85",text:"Vision Super"},{id:"135",text:"Vison Super"},{id:"157",
text:"WealthTrac"},{id:"176",text:"Westpac"},{id:"212",text:"Westpac BT"},{id:"255",text:"Westpack BT Superwrap"},{id:"243",text:"Woolworths Group Super"},{id:"34",text:"Zurich Super Fund"}],
                            showOn: {'field':'is_new_fund_used', 'value' : [false, undefined]}
                        },
                        {
                            name: 'new_fund',
                            type: 'text',
                            label: 'Fund Name',
                            labelWidth: 3,
                            inputWidth: 3,
                            showOn: {'field':'is_new_fund_used', 'value' : true}
                        },
                        {
                            name: 'spin',
                            type: 'text',
                            label: 'SPIN',
                            labelWidth: 3,
                            inputWidth: 3,
                            showOn: {'field':'is_new_fund_used', 'value' : true}
                        },
                        {
                            name: 'fund_account_number',
                            type: 'text',
                            label: 'Account Number',
                            labelWidth: 3,
                            inputWidth: 3,
                            showOn: {'field':'is_new_fund_used', 'value' : true}
                        },
                        {
                            name: 'sp_type',
                            type: 'select',
                            label: 'Payment Method',
                            labelWidth: 3,
                            inputWidth: 3,
                            options: [
                                {id: "1", text: "BPAY"},
                                {id: "4", text: "Cheque"},
                                {id: "2", text: "EFT"},
                                {id: "3", text: "Online"}
                            ],
                            showOn: {'field':'is_new_fund_used', 'value' : true}
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
            $scope.data.pay_rates.push({
                rate_period: 1
            });
        };
        $scope.addAllowance = function() {
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
        String.prototype.toUnderscore = function(){
            return this.replace(/([A-Z])/g, function($1){return "_"+$1.toLowerCase();});
        };
        $scope.submit = function() {
            var data = angular.copy($scope.data);
            // convert key name from camelcase to underscore format
            for (var i in data) {
                data[i.toUnderscore()] = angular.copy(data[i]);
            }
            data.company_id = $scope.companyId;
            data.employee_id = null;
            data.start_day = data.start_date.getDate();
            data.start_month = data.start_date.getMonth() + 1;
            data.start_year = data.start_date.getFullYear();
            data.dob_day = data.date_of_birth.getDate();
            data.dob_month = data.date_of_birth.getMonth() + 1;
            data.dob_year = data.date_of_birth.getFullYear();
            $scope.saving = true;
            data.rates = data.pay_rates.concat(data.allowances);
            for (var i in data.rates) {
                data.rates[i].rate_type = data.rates[i].rate_type.id;
            }
            Employee.save(data, function() {
                $scope.saving = false;
                toaster.pop('success', "Add Employee", "New employee is added successfully! Going back to the list.");
                $timeout(function() {
                    $state.go("employees", {companyName: $scope.companyName, companyId: $scope.companyId});
                }, 3000);
            }, function() {
                $scope.saving = false;
            });
        };
    }
]);

angular.module('App').controller('EmployeeTabController', [
    '$scope',
    '$state',
    function($scope, $state) {
        $scope.activeTab = 0;
        $scope.tabs = [
            { id: 0, name: 'details', label: 'Employee Details', template: '/app/payroll/employee/templates/partials/details.html' },
            { id: 1, name: 'tax', label: 'Tax Setup', template: '/app/payroll/employee/templates/partials/tax.html' },
            { id: 2, name: 'pay-rates', label: 'Pay Rates', template: '/app/payroll/employee/templates/partials/pay-rates.html' },
            { id: 3, name: 'leave', label: 'Leave Entitlements', template: '/app/payroll/employee/templates/partials/leave.html' },
            { id: 4, name: 'super', label: 'Superannuation', template: '/app/payroll/employee/templates/partials/super.html' }
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
/*
* directive to check if input value is match with other specific input
* usage: <input name="input2" data-ng-match="input1" data-ng-model="model.text2" data-msg-match="Value is not match with input 1" />
*/
angular.module('app.directives.ngMatch', [])
.directive("ngMatch", function() {
    return {
        require: "ngModel",
        link: function(scope, elem, attrs, ctrl) {
            var otherInput = elem.inheritedData("$formController")[attrs.ngMatch];

            ctrl.$parsers.push(function(value) {
                if(value === otherInput.$viewValue) {
                    ctrl.$setValidity("match", true);
                    return value;
                }
                ctrl.$setValidity("match", false);
            });

            otherInput.$parsers.push(function(value) {
               //ctrl.$setValidity("match", value === ctrl.$viewValue);
                return value;
            });
        }
    };
});
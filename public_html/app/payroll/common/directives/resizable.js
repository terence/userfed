/**
 * make an element resizable
 * 
 * @link:   - http://plnkr.co/edit/ubWb5DbFeOqwsQ7fqs9j?p=info
 *          - http://stackoverflow.com/questions/18368485/angular-js-resizable-div-directive
 */
angular.module('App').directive('resizable', function () {
    return {
        restrict: 'A',
        scope: {
            callback: '&onResize'
        },
        link: function postLink(scope, elem, attrs) {
            elem.resizable({
                minWidth:320,
                maxWidth:980
            });
            elem.on('resize', function (evt, ui) {
              scope.$apply(function() {
                if (scope.callback) { 
                  scope.callback({$evt: evt, $ui: ui }); 
                }                
              });
            });
        }
    };
  });
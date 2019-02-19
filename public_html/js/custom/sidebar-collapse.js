(function() {
    var sidebar = $('#col-sb');
    var triggerButton = $('#collapse-button');
    var openSidebar = function() {
        sidebar.removeClass("collapse-sidebar");
        triggerButton.removeClass("fa-caret-square-o-right").addClass("fa-caret-square-o-left");
    };
    var collapseSidebar = function() {
        sidebar.addClass("collapse-sidebar");
        triggerButton.removeClass("fa-caret-square-o-left").addClass("fa-caret-square-o-right");
    };
    $('#trig').on('click', function() {
        sidebar.toggleClass("collapse-sidebar");
        triggerButton.toggleClass("fa-caret-square-o-left fa-caret-square-o-right");
        return false;
    });
    var breakPoint = 980;
    $(window).resize(function() {
        var vw = window.innerWidth;
        if (vw < breakPoint) {
            collapseSidebar();
        } else {
            openSidebar();
        }
    });
    $(window).resize();
})();
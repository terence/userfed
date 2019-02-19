(function() {
    var permissionSelect = $('#permissions');
    // @link: http://loudev.com/
    permissionSelect.multiSelect({
        cssClass : 'permissions-select',
        selectableFooter: "<div class='text-right'><a href='#' class='select-all'>Select all</a></div>",
        selectionFooter: "<div class='text-right'><a href='#' class='deselect-all'>Deselect all</a></div>",
        selectableHeader: "<div class='selectable-filter'><strong>Available permissions:</strong><input class='form-control' type='text' autocomplete='off' placeholder='Filter permissions. E.g: user'></div>",
        selectionHeader: "<div class='selection-filter'><strong>Granted permissions:</strong><input class='form-control' type='text' autocomplete='off' placeholder='Filter. E.g: user'></div>",
        afterInit: function(ms){
            var that = this,
            $selectableSearch = that.$container.find(".selectable-filter").find("input"),
            $selectionSearch = that.$container.find(".selection-filter").find("input"),
            selectableSearchString = '#'+that.$container.attr('id')+' .ms-elem-selectable:not(.ms-selected)',
            selectionSearchString = '#'+that.$container.attr('id')+' .ms-elem-selection.ms-selected';
            that.qs1 = $selectableSearch.quicksearch(selectableSearchString).on('keydown', function(e){
                // in filter textbox, press down arrow key to make the selectable list focus
                if (e.which === 40){
                    that.$selectableUl.focus();
                    return false;
                }
            });

            that.qs2 = $selectionSearch.quicksearch(selectionSearchString).on('keydown', function(e){
                // in filter textbox, press down arrow key to make the selection list focus
                if (e.which === 40) {
                    that.$selectionUl.focus();
                    return false;
                }
            });
        },
        afterSelect: function(){
            this.qs1.cache();
            this.qs2.cache();
        },
        afterDeselect: function(){
            this.qs1.cache();
            this.qs2.cache();
        },
        selectableOptgroup: true
    });
    $(".select-all").click(function() {
        permissionSelect.multiSelect('select_all');
    });
    $(".deselect-all").click(function() {
        permissionSelect.multiSelect('deselect_all');
    });
})();
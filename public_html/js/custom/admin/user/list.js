/* 
 * Admin > user list.
 * Use datatable grid
 */


var UserGrid = {
    renderUserLink: function(title, userId) {
        var o = this.options;
        if (title) {
            return '<a href="' + o.editUrl.replace(o.placeHolderId, userId) + '" class="btn btn-link" title="Edit this user info">' +   appcore.htmlEscape(title) + '</a>';
        }
        return '';
    },
    renderAppLink: function(appCount, userId) {
        var o = this.options;
        var title = 'Add app';
        if (appCount > 0) {
            title = appCount + ' app(s)';
        }
        return '<a href="' + o.userAppUrl.replace(o.placeHolderId, userId) + '" class="btn btn-link" title="User\'s application">' +
                '<i class="fa fa-tasks fa-lg"></i> ' + 
                appcore.htmlEscape(title) + 
            '</a>';        
    },
    renderOrgLink: function(orgCount, userId) {
        var o = this.options;
        var title = 'Add org';
        if (orgCount) {
            title = orgCount + ' org(s)';
        }
        return '<a href="' + o.userOrgUrl.replace(o.placeHolderId, userId) + '" class="btn btn-link" title="User\'s organisation">' +
                    '<i class="fa fa-sitemap fa-lg"></i> ' +
                    appcore.htmlEscape(title) + 
                '</a>';
    },
    renderLogLink: function(title, userId) {
        var o = this.options;
        return '<a href="' + o.userLogUrl.replace(o.placeHolderId, userId) + '" class="btn btn-link" title="User\'s log"><i class="fa fa-list-alt fa-lg"></i> Logs</a>';
    },
    init: function () {
        var o = this.options;
        var tableId = o.tableId;
        var self = this;
        var table = this.table = $(tableId).DataTable( {
            dom: '<"dataTables_ex_actions"f>rtp',//@link http://datatables.net/reference/option/dom
            "pageLength": o.pageLength,
            "processing": true,
            "serverSide": true,
            "order": [[9, "desc"]],//@todo: datatable always use first column for order, but first column is not orderable, so we need explicitly set initial order
            "ajax": appcore.url(o.ajax),
            "columnDefs": [
                {
                    "targets": 0,
                    "data": null,
                    "className": "text-center",
                    "defaultContent": '<input type="checkbox"/>'
                },                
                {
                    "targets": 1,
                    "data": "status",
                    "className": "text-center",
                    "render": function ( data, type, full, meta ) {
                        return '<i class="fa fa-circle icon-' + appcore.htmlEscape(data) + '" title="' + appcore.htmlEscape(data) + '"></i>';
                    }
                },
                {
                    "targets": 2,
                    "render": function ( data, type, full, meta ) {
                        return self.renderUserLink(data, full.user_id);
                    }
                },
                {
                    "targets": 3,
                    "render": function ( data, type, full, meta ) {
                        return self.renderUserLink(data, full.user_id);
                    }
                },
                {
                    "targets": 4,
                    "render": function ( data, type, full, meta ) {
                        return self.renderUserLink(data, full.user_id);
                    }
                },
                {
                    "targets": 5,
                    "render": function ( data, type, full, meta ) {
                        return self.renderAppLink(data, full.user_id);
                    }
                },
                {
                    "targets": 6,
                    "render": function ( data, type, full, meta ) {
                        return self.renderOrgLink(data, full.user_id);
                    }
                },
                {
                    "targets": 7,
                    "render": function (data, type, full, meta) {
                        return self.renderLogLink(data, full.user_id);
                    }
                }
            ], 
            "columns": [
                { "data": null, "orderable": false },
                { "data": "status", "orderable": false },
                { "data": "firstname"},
                { "data": "lastname"},
                { "data": "email"},
                { "data": "app_count", "orderable": false },
                { "data": "org_count", "orderable": false },
                { "data": null, "orderable": false },
                { "data": "role", "orderable": false },
                { "data": "last_updated", "orderable": true }
            ]
        } );    
        
        var self = this;
        
        this.initCheckAll();
        
        this.initActions();
    },
    
    initCheckAll: function() {
        //enable toggle select all
        
        var o = this.options;
        var tableId = o.tableId;
        
        //@link http://datatables.net/examples/api/select_row.html
        $(tableId + ' tbody').on( 'click', 'input:checkbox', function () {
            if (this.checked) {
                $(this).closest('tr').addClass('selected');
            } else {
                $(this).closest('tr').removeClass('selected');
            }
        });
        
        $('.datatable-seletall').on('click', function(){
            $(tableId + ' tbody input:checkbox').prop('checked', this.checked);
            if (this.checked) {
                $(tableId + ' tbody tr').addClass('selected');
            } else {
                $(tableId + ' tbody tr').removeClass('selected');
            }
        })        
    },
    
    initActions: function () {
        //init actions UI
        //Action: what we can do when we select row(s)
        
        var o = this.options;
        var tableId = o.tableId;
        var table = this.table;
        
        //build UI for actions + filters
        if (!(o.actions && o.actions.length) && !(o.filters && o.filters.length)) {
            return;
        }
        
        var actionsDom = $(tableId).closest('.dataTables_wrapper').find('.dataTables_ex_actions');
        var actionHtml = "";
        if (o.actions && o.actions.length) {
            var actionHtml = "<div class='action-wrapper'>" + 
                "<select class='action form-control input-sm'>";
            for (var i = 0, len = o.actions.length; i < len; i++) {
                actionHtml += "<option value='" + o.actions[i].value + "'>" + o.actions[i].label + "</option>"
            }
            actionHtml += "</select>";
            actionHtml += "<button class='datatable-apply-btn btn btn-default btn-sm'>Apply</button>";
            actionHtml += "</div>";
        }

        //add filters button
        if (o.filters && o.filters.length) {
            actionHtml += "<div class='filter-wrapper'>";
            for (var i = 0, len = o.filters.length; i < len; i++) {
                actionHtml += o.filters[i].html;
            }
            actionHtml += "<button class='datatable-filter-btn btn btn-default btn-sm'>Filter</button>";
            actionHtml += "</div>";
        }

        $(actionsDom).prepend(actionHtml);
    
        var self = this;
        
        //ajax call to server with action's url and ids
        var actionFunc = function(url) {
            var ids = [];
            var selectedRows = table.rows('.selected').data();
            for(var i = 0, len = selectedRows.length; i < len; i++) {
                ids.push(selectedRows[i][o.idFieldName]);
            }
            $.ajax({
                type: 'POST',
                url: url,
                data: {'ids': ids}                        
            })
            .done(function(){
                //reload grid
                table.ajax.reload();
                //reset check all checkbox
                $('.datatable-seletall').prop('checked', false);
            });            
        };
        
        $(actionsDom).find('.datatable-apply-btn').on('click', function(){
            var action = $(actionsDom).find('.action').val();
            var url, confirmTitle, confirmMessage;
            for(var i = 0, len = o.actions.length; i < len; i++) {
                if (o.actions[i].value == action) {
                    url = o.actions[i].url;
                    if (o.actions[i].confirm) {
                        if (o.actions[i].confirm.title) {
                            confirmTitle = o.actions[i].confirm.title;
                        }
                        confirmMessage = o.actions[i].confirm.message;
                    }
                    break;
                }
            }            
            
            if (confirmTitle || confirmMessage) {
                var modal = self.getModal(confirmTitle, confirmMessage);
                $(modal).find('.confirm-btn').one('click', function(){
                    actionFunc(url);
                    $(modal).modal('hide')
                });
                $(modal).modal('show');                
            } else {
                actionFunc(url);
            }

        });
        
        //filter 
        $(actionsDom).find('.datatable-filter-btn').on('click', function(){
            table.ajax.reload();
        });
        
        table.on('preXhr', function(e, settings, data){
            $(actionsDom).find(".filter-wrapper :input").each(function(index, el){
                data[$(el).attr('name')] = $(el).val();
            });            
        });
    },
    
    getModal: function(title, message) {
        if (!this.modal) {
            var modal = $('<div class="modal fade"  tabindex="-1" role="dialog" aria-hidden="true">' +
                    '<div class="modal-dialog">' +
                        '<div class="modal-content">' +
                            '<div class="modal-header">' +
                                '<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>' +
                                '<h3 class="title">Title</h3>' +
                            '</div>' +
                            '<div class="modal-body">' +
                                '<p class="message">Message</p>' +
                            '</div>' +
                            '<div class="modal-footer">' +
                                '<button class="btn" data-dismiss="modal" aria-hidden="true">Cancel</button>' +
                                '<button class="btn btn-primary confirm-btn">Confirm</button>' +
                            '</div>' +
                        '</div>' +
                    '</div>' +
                '</div>');
            $("body").append(modal);
            this.modal = modal;
        }
        $(modal).find('.title').text(title);
        $(modal).find('.message').text(message);
        return this.modal;
    }
};

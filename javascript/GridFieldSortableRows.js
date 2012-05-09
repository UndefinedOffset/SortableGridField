(function($) {
    $('.ss-gridfield .gridfield-sortablerows input').entwine({
        onmatch: function() {
            var refCheckbox=$(this);
            
            var gridField=$(this).getGridField();
            gridField.find('tbody').sortable({
                                            disabled: ($(this).is(':checked')==false),
                                            helper: function(e, ui) {
                                                        //Maintains width of the columns
                                                        ui.children().each(function() {
                                                            $(this).width($(this).width());
                                                        });
                                                        
                                                        return ui;
                                                    },
                                            update: function(event, ui) {
                                                        var dataRows=[];
                                                        var gridItems=gridField.getItems();
                                                        var button=refCheckbox.parent().find('.sortablerows_toggle');
                                                        
                                                        
                                                        for(var i=0;i<gridItems.length;i++) {
                                                            dataRows[i]=$(gridItems[i]).data('id');
                                                        }
                                                        
                                                        
                                                        gridField.reload({data: [{name: button.attr('name'), value: button.val()}, {name: 'Items', value: dataRows}]});
                                                    }
                                        }).disableSelection();
        },
        
        onchange: function(e) {
            var gridField=$(this).getGridField();
            gridField.find('tbody').sortable('option', 'disabled', ($(this).is(':checked')==false));
            gridField.setState('GridFieldSortableRows', {sortableToggle: $(this).is(':checked')});
            
            
            var button=$(this).parent().find('.sortablerows_disablepagenator');
            gridField.reload({data: [{name: button.attr('name'), value: button.val()}]});
        }
    });
})(jQuery);
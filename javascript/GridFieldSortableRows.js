(function($) {
	$('.ss-gridfield .gridfield-sortablerows input').entwine({
		onmatch: function() {
			var refCheckbox=$(this);
			
			var gridField=$(this).getGridField();
			
			if($(this).is(':checked')) {
				gridField.find('table').addClass('dragSorting');
			}else {
				gridField.find('table').removeClass('dragSorting');
			}
			
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
												var button=refCheckbox.parent().find('.sortablerows-toggle');
												
												
												for(var i=0;i<gridItems.length;i++) {
													dataRows[i]=$(gridItems[i]).data('id');
												}
												
												
												var form = gridField.closest('form'), 
													focusedElName = gridField.find(':input:focus').attr('name'); // Save focused element for restoring after refresh
												var ajaxOpts = {data: [
																		{
																			name: button.attr('name'),
																			value: button.val()},
																		{
																			name: 'Items',
																			value: dataRows
																		}
																	]};
												
												ajaxOpts.data = ajaxOpts.data.concat(form.find(':input').serializeArray());
												
												// Include any GET parameters from the current URL, as the view state might depend on it.
												// For example, a list prefiltered through external search criteria might be passed to GridField.
												if(window.location.search) {
													ajaxOpts.data = window.location.search.replace(/^\?/, '') + '&' + $.param(ajaxOpts.data);
												}
												
												$.ajax($.extend({}, {
													headers: {"X-Pjax" : 'CurrentField'},
													type: "POST",
													url: gridField.data('url'),
													dataType: 'html',
													error: function(e) {
														alert(ss.i18n._t('GRIDFIELD.ERRORINTRANSACTION'));
													}
												}, ajaxOpts));
											}
										}).disableSelection();
		},
		
		onchange: function(e) {
			var gridField=$(this).getGridField();
			gridField.find('tbody').sortable('option', 'disabled', ($(this).is(':checked')==false));
			gridField.setState('GridFieldSortableRows', {sortableToggle: $(this).is(':checked')});
			
			
			var button=$(this).parent().find('.sortablerows-disablepagenator');
			gridField.reload({data: [{name: button.attr('name'), value: button.val()}]});
		}
	});
})(jQuery);
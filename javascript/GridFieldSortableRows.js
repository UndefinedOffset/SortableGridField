(function($) {
	$.entwine('ss', function($) {
		$('.ss-gridfield .gridfield-sortablerows input').entwine({
			PageSort: false,
			
			onmatch: function() {
				var self=this;
				var refCheckbox=$(this);
				var gridField=this.getGridField();
				var form=gridField.closest('form');
				var pageArrows=gridField.find('.gridfield-sortablerows-movepage .sortablerows-psort-arrow');
				
				if($(this).is(':checked')) {
					gridField.find('table').addClass('dragSorting');
				}else {
					gridField.find('table').removeClass('dragSorting');
				}
				
				gridField.find('tbody').sortable({
												opacity: 0.6,
												disabled: ($(this).is(':checked')==false),
												start: function(event, ui) {
													pageArrows.show();
													pageArrows.redraw();
													pageArrows.startMoveTracking();
												},
												stop: function(event, ui) {
													pageArrows.stopMoveTracking();
													pageArrows.hide();
												},
												sort: function(event, ui) {
													pageArrows.moveTracking(event, ui);
												},
												update: function(event, ui) {
													if(self.getPageSort()) {
														self.setPageSort(false);
														return;
													}
													
													var gridItems=gridField.getItems();
													
													gridItems.removeClass('first last odd even');
													gridItems.first().addClass('first');
													gridItems.last().addClass('last');
													gridItems.filter(':even').addClass('odd');
													gridItems.filter(':odd').addClass('even');
													
													var dataRows=[];
													var button=refCheckbox.parent().find('.sortablerows-savesort');
													
													
													for(var i=0;i<gridItems.length;i++) {
														dataRows[i]=$(gridItems[i]).data('id');
													}
													
													
													self._makeRequest({data: [
																				{
																					name: button.attr('name'),
																					value: button.val()
																				},
																				{
																					name: 'ItemIDs',
																					value: dataRows
																				}
																			]},function() {
																				form.removeClass('loading');
																			});
												}
											});
				
				if(refCheckbox.hasClass('gridfield-sortablerows-noselection') || $(this).is(':checked')) {
					gridField.find('tbody').disableSelection();
				}
			},
			
			onchange: function(e) {
				var gridField=this.getGridField();
				gridField.find('tbody').sortable('option', 'disabled', ($(this).is(':checked')==false));
				gridField.setState('GridFieldSortableRows', {sortableToggle: $(this).is(':checked')});
				
				
				var button=$(this).parent().find('.sortablerows-toggle');
				gridField.reload({data: [{name: button.attr('name'), value: button.val()}]});
			},
			
			_makeRequest: function(ajaxOpts, callback) {
				var gridField=this.getGridField();
				var form = gridField.closest('form');
				
				form.addClass('loading');
				
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
					success: callback,
					error: function(e) {
						alert(ss.i18n._t('GRIDFIELD.ERRORINTRANSACTION'));
					}
				}, ajaxOpts));
			}
		});
		
		$('.ss-gridfield .gridfield-sortablerows-movepage .sortablerows-psort-arrow').entwine({
			ArrowIcon: null,
			
			onmatch: function() {
				var gridField=this.getGridField();
				var sortableCheckbox=gridField.find('.gridfield-sortablerows input');
				var self=$(this);
				
				if($(this).hasClass('sortablerows-prev-page') && (gridField.find('.ss-gridfield-previouspage').length==0 || gridField.find('.ss-gridfield-previouspage').is(':disabled'))) {
					$(this).remove();
					return;
				}else if($(this).hasClass('sortablerows-next-page') && (gridField.find('.ss-gridfield-nextpage').length==0 || gridField.find('.ss-gridfield-nextpage').is(':disabled'))) {
					$(this).remove();
					return;
				}
				
				$(this).droppable({
									disabled: $(this).is(':disabled'),
									accept: 'tr.ss-gridfield-item',
									activeClass: 'sortablerows-droptarget',
									tolerance: 'pointer',
									drop: function(event, ui) {
										self.stopMoveTracking();
										
										sortableCheckbox.setPageSort(true);
										
										var button=gridField.find('.gridfield-sortablerows .sortablerows-sorttopage');
										var itemID=$(ui.draggable).data('id');
										var target='';
										
										if($(this).hasClass('sortablerows-prev-page')) {
											target='previouspage';
										}else if($(this).hasClass('sortablerows-next-page')) {
											target='nextpage';
										}
										
										
										//Move and Reload the grid
										gridField.reload({data: [
																	{
																		name: button.attr('name'),
																		value: button.val()
																	},
																	{
																		name: 'ItemID',
																		value: itemID
																	},
																	{
																		name: 'Target',
																		value: target
																	}
																]});
									}
								});
				
				this.redraw();
			},
			redraw: function() {
				var gridField=this.getGridField();
				var tbody=gridField.find('tbody');
				var tbodyPos=tbody.position();
				
				$(this).css('top', tbodyPos.top+'px').height(tbody.height());
			},
			startMoveTracking: function() {
				var self=$(this);
				self.setArrowIcon(self.find('i'));
			},
			stopMoveTracking: function() {
				$(this).setArrowIcon(null);
			},
			moveTracking: function(e, ui) {
				var self=$(this);
				var arrowIcon=self.getArrowIcon();
				if(arrowIcon) {
					var selfOffset=self.offset().top;
					var arrowIconHeight=arrowIcon.width()+10;
					var railHeight=self.height()-arrowIconHeight;
					var helperPos=ui.helper.offset().top;
					
					if(helperPos>selfOffset+10 && helperPos<selfOffset+railHeight) {
						arrowIcon.css('top', ((helperPos-selfOffset)+arrowIconHeight/2)+'px');
					}
				}
			}
		});
	});
	
})(jQuery);

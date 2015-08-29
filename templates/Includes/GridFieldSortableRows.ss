<tr>
	<th class="extra sortablerowsheading" colspan="$Colspan">
		<div class="gridfield-sortablerows">
			<input type="checkbox" id="{$ID}_AllowDragDropCheck" value="1" autocomplete="off" class="no-change-track<% if $DisableSelection %> gridfield-sortablerows-noselection<% end_if %>"$Checked/>
			<label for="{$ID}_AllowDragDropCheck"><%t GridFieldSortableRows.ALLOW_DRAG_DROP "Allow drag and drop re-ordering" %></label>
			$SortableToggle
			$SortOrderSave
			$SortToPage
		</div>
	</th>
</tr>
<?php
/**
 * This component provides a checkbox which when checked enables drag-and-drop re-ordering of elements displayed in a {@link GridField}
 *
 * @package forms
 */
class GridFieldSortableRows implements GridField_HTMLProvider, GridField_ActionProvider, GridField_DataManipulator {
	protected $sortColumn;
	
	/**
	 * @param String $sortColumn Column that should be used to update the sort information
	 */
	public function __construct($sortColumn) {
		$this->sortColumn = $sortColumn;
	}
	
	/**
	 * Returns a map where the keys are fragment names and the values are pieces of HTML to add to these fragments.
	 * @param GridField $gridField Grid Field Reference
	 * @return Array Map where the keys are fragment names and the values are pieces of HTML to add to these fragments.
	 */
	public function getHTMLFragments($gridField) {
		$state = $gridField->State->GridFieldSortableRows;
		if(!is_bool($state->sortableToggle)) {
			$state->sortableToggle = false;
		}
		
		//Ensure user can edit
		if(!singleton($gridField->getModelClass())->canEdit()){
			return array();
		}
		
		
		//Sort order toggle
		$sortOrderToggle = Object::create(
			'GridField_FormAction',
			$gridField,
			'sortablerows-toggle',
			_t('GridFieldSortableRows.ALLOW_DRAG_DROP', '_Allow drag and drop re-ordering'),
			'saveGridRowSort',
			null
		)->addExtraClass('sortablerows-toggle');
		
		
		//Disable Pagenator
		$disablePagenator = Object::create(
			'GridField_FormAction',
			$gridField,
			'sortablerows-disablepagenator',
			_t('GridFieldSortableRows.DISABLE_PAGINATOR', '_Disable Pagenator'),
			'sortableRowsDisablePaginator',
			null
		)->addExtraClass('sortablerows-disablepagenator');
		
		
		//Disable Pagenator
		$sortToPage = Object::create(
			'GridField_FormAction',
			$gridField,
			'sortablerows-sorttopage',
			_t('GridFieldSortableRows.SORT_TO_PAGE', '_Sort To Page'),
			'sortToPage',
			null
		)->addExtraClass('sortablerows-sorttopage');
		
		
		$data = array('SortableToggle' => $sortOrderToggle,
					'PagenatorToggle' => $disablePagenator,
					'SortToPage' => $sortToPage,
					'Checked' => ($state->sortableToggle == true ? ' checked = "checked"':''));
		
		$forTemplate = new ArrayData($data);
		
		
		//Inject Requirements
		Requirements::css('SortableGridField/css/GridFieldSortableRows.css');
		Requirements::javascript('SortableGridField/javascript/GridFieldSortableRows.js');
		
		
		$args = array('Colspan' => count($gridField->getColumns()), 'ID' => $gridField->ID());
		
		return array('header' => $forTemplate->renderWith('GridFieldSortableRows', $args));
	}
	
	/**
	 * Manipulate the datalist as needed by this grid modifier.
	 * @param GridField $gridField Grid Field Reference
	 * @param SS_List $dataList Data List to adjust
	 * @return DataList Modified Data List
	 */
	public function getManipulatedData(GridField $gridField, SS_List $dataList) {
		//Detect and correct items with a sort column value of 0 (push to bottom)
		$this->fixSortColumn($gridField, $dataList);
		
		
		$headerState = $gridField->State->GridFieldSortableHeader;
		$state = $gridField->State->GridFieldSortableRows;
		if ((!is_bool($state->sortableToggle) || $state->sortableToggle==false) && $headerState && !empty($headerState->SortColumn)) {
			return $dataList;
		}
		
		if ($state->sortableToggle == true) {
			$gridField->getConfig()->removeComponentsByType('GridFieldFilterHeader');
			$gridField->getConfig()->removeComponentsByType('GridFieldSortableHeader');
		}
		
		
		return $dataList->sort($this->sortColumn);
	}
	
	/**
	 * Detects and corrects items with a sort column value of 0, by appending them to the bottom of the list
	 * @param GridField $gridField Grid Field Reference
	 * @param SS_List $dataList Data List of items to be checked
	 */
	protected function fixSortColumn($gridField, SS_List $dataList) {
		$list=clone $dataList;
		$list->dataQuery()->limit(array());
		$max = $list->Max($this->sortColumn);
		if($list->where('"'.$this->sortColumn.'"=0')->Count()>0) {
			//Start transaction if supported
			if(DB::getConn()->supportsTransactions()) {
				DB::getConn()->transactionStart();
			}
			
			
			$owner = $gridField->Form->getRecord();
			$sortColumn = $this->sortColumn;
			$i = 1;
			$many_many = ($list instanceof ManyManyList);
			if ($many_many) {
				list($parentClass, $componentClass, $parentField, $componentField, $table) = $owner->many_many($gridField->getName());
			}
			
			
			//@TODO Need to optimize this to eliminate some of the resource load could use raw queries to be more efficient
			foreach($list as $obj) {
				if($many_many) {
					DB::query('UPDATE "' . $table
							. '" SET "' . $sortColumn.'" = ' . ($max + $i)
							. ' WHERE "' . $componentField . '" = ' . $obj->ID . ' AND "' . $parentField . '" = ' . $owner->ID);
				}else {
					$obj->$sortColumn = ($max + $i);
					$obj->write();
				}
				
				$i++;
			}
			
			
			//End transaction if supported
			if(DB::getConn()->supportsTransactions()) {
				DB::getConn()->transactionEnd();
			}
		}
	}
	
	/**
	 * Return a list of the actions handled by this action provider.
	 * @param GridField $gridField Grid Field Reference
	 * @return Array Array with action identifier strings.
	 */
	public function getActions($gridField) {
		return array('saveGridRowSort', 'sortableRowsDisablePaginator', 'sortToPage');
	}
	
	/**
	 * Handle an action on the given grid field.
	 * @param GridField $gridField Grid Field Reference
	 * @param String $actionName Action identifier, see {@link getActions()}.
	 * @param Array $arguments Arguments relevant for this
	 * @param Array $data All form data
	 */
	public function handleAction(GridField $gridField, $actionName, $arguments, $data) {
		$state = $gridField->State->GridFieldSortableRows;
		if (!is_bool($state->sortableToggle)) {
			$state->sortableToggle = false;
		} else if ($state->sortableToggle == true) {
			$gridField->getConfig()->removeComponentsByType('GridFieldFilterHeader');
			$gridField->getConfig()->removeComponentsByType('GridFieldSortableHeader');
		}
		
		
		if ($actionName == 'savegridrowsort') {
			return $this->saveGridRowSort($gridField, $data);
		} else if ($actionName == 'sorttopage') {
			return $this->sortToPage($gridField, $data);
		}
	}
	
	/**
	 * Handles saving of the row sort order
	 * @param GridField $gridField Grid Field Reference
	 * @param Array $data Data submitted in the request
	 */
	protected function saveGridRowSort(GridField $gridField, $data) {
		if(!singleton($gridField->getModelClass())->canEdit()){
			throw new ValidationException(_t('GridFieldSortableRows.EditPermissionsFailure', "No edit permissions"),0);
		}
		
		if (empty($data['ItemIDs'])) {
			user_error('No items to sort', E_USER_ERROR);
		}
		
		$className = $gridField->getModelClass();
		$owner = $gridField->Form->getRecord();
		$items = clone $gridField->getList();
		$many_many = ($items instanceof ManyManyList);
		$sortColumn = $this->sortColumn;
		$pageOffset = 0;
		
		if ($paginator = $gridField->getConfig()->getComponentsByType('GridFieldPaginator')->First()) {
			$pageState = $gridField->State->GridFieldPaginator;
			
			if($pageState->currentPage && is_int($pageState->currentPage) && $pageState->currentPage>1) {
				$pageOffset = $paginator->getItemsPerPage() * ($pageState->currentPage - 1);
			}
		}
		
		
		if ($many_many) {
			list($parentClass, $componentClass, $parentField, $componentField, $table) = $owner->many_many($gridField->getName());
		}
		
		
		//Start transaction if supported
		if(DB::getConn()->supportsTransactions()) {
			DB::getConn()->transactionStart();
		}
		
		
		//@TODO Need to optimize this to eliminate some of the resource load could use raw queries to be more efficient
		$ids = explode(',', $data['ItemIDs']);
		for($sort = 0;$sort<count($ids);$sort++) {
			$id = intval($ids[$sort]);
			if ($many_many) {
				DB::query('UPDATE "' . $table
						. '" SET "' . $sortColumn.'" = ' . (($sort + 1) + $pageOffset)
						. ' WHERE "' . $componentField . '" = ' . $id . ' AND "' . $parentField . '" = ' . $owner->ID);
			} else {
				$obj = $items->byID($ids[$sort]);
				$obj->$sortColumn = ($sort + 1) + $pageOffset;
				$obj->write();
			}
		}
		
		//End transaction if supported
		if(DB::getConn()->supportsTransactions()) {
			DB::getConn()->transactionEnd();
		}
	}
	
	/**
	 * Handles sorting across pages
	 * @param GridField $gridField Grid Field Reference
	 * @param Array $data Data submitted in the request
	 */
	protected function sortToPage(GridField $gridField, $data) {
		if (!$paginator = $gridField->getConfig()->getComponentsByType('GridFieldPaginator')->First()) {
			user_error('Paginator not detected', E_USER_ERROR);
		}
		
		if (empty($data['ItemID'])) {
			user_error('No item to sort', E_USER_ERROR);
		}
		
		if (empty($data['Target'])) {
			user_error('No target page', E_USER_ERROR);
		}
		
		
		$className = $gridField->getModelClass();
		$owner = $gridField->Form->getRecord();
		$items = clone $gridField->getList();
		$many_many = ($items instanceof ManyManyList);
		$sortColumn = $this->sortColumn;
		$targetItem = $items->byID(intval($data['ItemID']));
		
		if (!$targetItem) {
			user_error('Target item not found', E_USER_ERROR);
		}
		
		$currentPage = 1;
		
		
		$pageState = $gridField->State->GridFieldPaginator;
		if($pageState->currentPage && $pageState->currentPage>1) {
			$currentPage = $pageState->currentPage;
		}
		
		
		if ($many_many) {
			list($parentClass, $componentClass, $parentField, $componentField, $table) = $owner->many_many($gridField->getName());
		}
		
		
		if ($data['Target'] == 'previouspage') {
			$sortPositions = $items->limit($paginator->getItemsPerPage() + 1, ($paginator->getItemsPerPage() * ($currentPage - 1)) - 1)->column($sortColumn);
		} else if ($data['Target'] == 'nextpage') {
			$sortPositions = $items->limit($paginator->getItemsPerPage() + 1, $paginator->getItemsPerPage() * ($currentPage - 1))->column($sortColumn);
		} else {
			user_error('Not implemented: '.$data['Target'], E_USER_ERROR);
		}
		
		
		//Start transaction if supported
		if(DB::getConn()->supportsTransactions()) {
			DB::getConn()->transactionStart();
		}
		
		if($data['Target']=='previouspage') {
			if ($many_many) {
				DB::query('UPDATE "' . $table
						. '" SET "' . $sortColumn.'" = ' . $sortPositions[0]
						. ' WHERE "' . $componentField . '" = ' . $targetItem->ID . ' AND "' . $parentField . '" = ' . $owner->ID);
			} else {
				$targetItem->$sortColumn = $sortPositions[0];
				$targetItem->write();
			}
			
			
			$i = 1;
			foreach ($items as $obj) {
				if ($obj->ID == $targetItem->ID) {
					continue;
				}
				
				
				if ($many_many) {
					DB::query('UPDATE "' . $table
							. '" SET "' . $sortColumn.'" = ' . $sortPositions[$i]
							. ' WHERE "' . $componentField . '" = ' . $obj->ID . ' AND "' . $parentField . '" = ' . $owner->ID);
				} else {
					$obj->$sortColumn = $sortPositions[$i];
					$obj->write();
				}
				
				$i++;
			}
		} else {
			if ($many_many) {
				DB::query('UPDATE "' . $table
						. '" SET "' . $sortColumn.'" = ' . $sortPositions[count($sortPositions) - 1]
						. ' WHERE "' . $componentField . '" = ' . $targetItem->ID . ' AND "' . $parentField . '" = ' . $owner->ID);
			} else {
				$targetItem->$sortColumn = $sortPositions[count($sortPositions) - 1];
				$targetItem->write();
			}
			
			
			$i = 0;
			foreach ($items as $obj) {
				if ($obj->ID == $targetItem->ID) {
					continue;
				}
				
				
				if ($many_many) {
					DB::query('UPDATE "' . $table
							. '" SET "' . $sortColumn.'" = ' . $sortPositions[$i]
							. ' WHERE "' . $componentField . '" = ' . $obj->ID . ' AND "' . $parentField . '" = ' . $owner->ID);
				} else {
					$obj->$sortColumn = $sortPositions[$i];
					$obj->write();
				}
				
				$i++;
			}
		}
		
		
		//End transaction if supported
		if(DB::getConn()->supportsTransactions()) {
			DB::getConn()->transactionEnd();
		}
	}
}
?>
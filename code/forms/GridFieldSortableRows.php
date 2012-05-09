<?php
/**
 * @package forms
 */
class GridFieldSortableRows implements GridField_HTMLProvider, GridField_ActionProvider {
    /**
     * Returns a map where the keys are fragment names and the values are pieces of HTML to add to these fragments.
     * @param GridField $gridField Grid Field Reference
     * @return {array} Map where the keys are fragment names and the values are pieces of HTML to add to these fragments.
     */
    public function getHTMLFragments($gridField) {
        $state=$gridField->State->GridFieldSortableRows;
        if(!is_bool($state->sortableToggle)) {
            $state->sortableToggle=false;
        }
        
        
        
        if(Object::has_extension($gridField->getModelClass(), 'GridFieldSortableObject')) {
            //Sort order toggle
            $sortOrderToggle=new GridField_FormAction($gridField, 'sortablerows_toggle', 'Allow drag and drop re-ordering', 'saveGridRowSort', null);
            $sortOrderToggle->addExtraClass('sortablerows_toggle');
            
            //Disable Pagenator
            if($gridField->getConfig()->getComponentByType('GridFieldPaginator')) {
                $disablePagenator=new GridField_FormAction($gridField, 'sortablerows_disablepagenator', 'Disable Pagenator', 'sortableRowsDisablePaginator', null);
                $disablePagenator->addExtraClass('sortablerows_disablepagenator');
            }else {
                $disablePagenator=null;
            }
            
            $forTemplate=new ArrayData(array(
                                            'SortableToggle'=>$sortOrderToggle,
                                            'PagenatorToggle'=>$disablePagenator,
                                            'Checked'=>($state->sortableToggle==true ? ' checked="checked"':'')
                                        ));
            
            
            //Inject Requirements
            Requirements::css('SortableGridField/css/GridFieldSortableRows.css');
            Requirements::javascript('SortableGridField/javascript/GridFieldSortableRows.js');
            
            
            return array(
                        'header'=>$forTemplate->renderWith('GridFieldSortableRows', array('Colspan'=>count($gridField->getColumns())))
                    );
        }
        
        return array();
    }
    
    /**
     * Return a list of the actions handled by this action provider.
     * @param GridField $gridField Grid Field Reference
     * @return {array} Array with action identifier strings.
     */
    public function getActions($gridField) {
        if(Object::has_extension($gridField->getModelClass(), 'GridFieldSortableObject')) {
            if($gridField->getConfig()->getComponentByType('GridFieldPaginator')) {
                return array('saveGridRowSort', 'sortableRowsDisablePaginator');
            }else {
                return array('saveGridRowSort');
            }
        }
        
        return array();
    }
    
    /**
     * Handle an action on the given grid field.
     * @param {GridField} $gridField Grid Field Reference
     * @param {string} $actionName Action identifier, see {@link getActions()}.
     * @param {array} $arguments Arguments relevant for this
     * @param {array} $data All form data
     */
    public function handleAction(GridField $gridField, $actionName, $arguments, $data) {
        $state=$gridField->State->GridFieldSortableRows;
        if(!is_bool($state->sortableToggle)) {
            $state->sortableToggle=false;
        }else if($state->sortableToggle==true) {
            if($gridField->getConfig()->getComponentsByType('GridFieldPaginator')) {
                $gridField->getConfig()->removeComponentsByType('GridFieldPaginator');
                $gridField->getConfig()->addComponent(new GridFieldFooter());
            }
            
            $gridField->getConfig()->removeComponentsByType('GridFieldFilterHeader');
            $gridField->getConfig()->removeComponentsByType('GridFieldSortableHeader');
        }
        
        
        if(Object::has_extension($gridField->getModelClass(), 'GridFieldSortableObject')) {
            if($actionName=='savegridrowsort') {
                return $this->saveGridRowSort($gridField, $data);
            }
        }
    }
    
    /**
     * Handles saving of the row sort order
     * @param {GridField} $gridField Grid Field Reference
     * @param {array} $data Data submitted in the request
     */
    private function saveGridRowSort(GridField $gridField, $data) {
        if(empty($data['Items'])) {
            user_error('No items to sort', E_USER_ERROR);
        }
        
        $className=$gridField->getModelClass();
        $ownerClass=$gridField->Form->Controller()->class;
        
        $many_many=GridFieldSortableObject::is_sortable_many_many($className);
        if($many_many) {
            $candidates=singleton($ownerClass)->many_many();
            if(is_array($candidates)) {
                foreach($candidates as $name => $class)
                    if($class==$className) {
                    $relationName=$name;
                    break;
                }
            }
            
            if(!isset($relationName)) {
                return false;
            }
            
            list($parentClass, $componentClass, $parentField, $componentField, $table)=singleton($ownerClass)->many_many($relationName);
        }
        
        
        $data['Items']=explode(',', $data['Items']);
        for($sort=0;$sort<count($data['Items']);$sort++) {
            $id=intval($data['Items'][$sort]);
            if($many_many) {
                DB::query("UPDATE \"$table\" SET \"SortOrder\" = $sort WHERE \"{$className}ID\" = $id");
            }else {
                $obj=DataObject::get_by_id($className, $id);
                $obj->SortOrder=$sort;
                $obj->write();
            }
        }
    }
}
?>
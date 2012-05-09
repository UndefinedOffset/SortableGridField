<?php
/**
 * @package extensions
 */
class GridFieldSortableObject extends DataExtension {
    public static $db=array(
                            'SortOrder'=>'Int'
                        );
    
    protected static $sortable_classes = array();
    protected static $many_many_sortable_relations = array();
    protected static $sort_dir = "ASC";
    
    /**
     * Sets the direction of the sort, by default it is ASC
     * @param {string} $dir Sort direction ASC or DESC
     */
    public static function set_sort_dir($dir) {
        if(strtoupper($dir)!='ASC' && strtoupper($dir)!='DESC') {
            user_error('Sort direction must be ASC or DESC', E_USER_ERROR);
        }
        
        self::$sort_dir=$dir;
    }
    
    /**
     * Makes a class sortable
     * @param {string} $className Name of the DataObject to extend
     */
    public static function add_sortable_class($className) {
        if(!self::is_sortable_class($className)) {
            Object::add_extension($className, 'GridFieldSortableObject');
            
            self::$sortable_classes[]=$className;
        }
    }
    
    /**
     * Makes a many_many relationship sortable
     * @param {string} $ownerClass Name of the owner class of the relationship
     * @param {string} $componentName Name of the relationship
     */
    public static function add_sortable_many_many_relation($ownerClass, $componentName) {
        list($parentClass, $componentClass, $parentField, $componentField, $table)=singleton($ownerClass)->many_many($componentName);
        
        Object::add_static_var($ownerClass, 'many_many_extraFields', array(
                                                                        $componentName=>array(
                                                                                'SortOrder'=>'Int'
                                                                    )));
        
        
        if(!isset(self::$many_many_sortable_relations[$componentClass])) {
            self::$many_many_sortable_relations[$componentClass] = array();
        }
        
        
        self::$many_many_sortable_relations[$componentClass][$parentClass]=$table;
        self::add_sortable_class($componentClass);
    }
    
    /**
     * Checks to see if a given DataObject class is sortable or not
     * @param {string} $className Name of the DataObject to check
     */
    public static function is_sortable_class($className) {
        if(in_array($classname, self::$sortable_classes)) {
            return true;
        }
        
        foreach(self::$sortable_classes as $class) {
            if(is_subclass_of($className, $class)) {
                return true;
            }
        }
        
        
        return Object::has_extension($className, 'GridFieldSortableObject');
    }
    
    /**
     * Checks to see if a given many_many relationship is sortable or not
     * @param {string} $componentClass Name of the component's class
     * @param {string} $parentClass Name of the owner class of the relationship
     * @return {bool} Returns boolean true if the many_many relationship is sortable
     */
    public static function is_sortable_many_many($componentClass, $parentClass=null) {
        $map=self::$many_many_sortable_relations;
        if($parentClass===null) {
            return isset($map[$componentClass]);
        }else {
      	     if(isset($map[$componentClass])) {
      	         return isset($map[$componentClass][$parentClass]);
      	     }
      	     
      	     return false;
        }

    }
    
    /**
     * Gets the join tables for the given class name
     * @param {string} $className Name of the DataObject to fetch for
     */
    public static function get_join_tables($className) {
        if(isset(self::$many_many_sortable_relations[$className])) {
            return self::$many_many_sortable_relations[$className];
        }
        
        return false;
    }
    
    /**
     * Modifies the SQL appending the SortOrder with the direction to the orderby statement
     * @param {SQLQuery} $query SQL Query to adjust
     */
    public function augmentSQL(SQLQuery &$query) {
        if(empty($query->select) || $query->delete || in_array("COUNT(*)", $query->select) || in_array("count(*)", $query->select)) {
            return;
        }
        
        
        $sort_field=false;
        if($join_tables=self::get_join_tables($this->owner->class)) {
            foreach($query->from as $from) {
                if($sort_field) {
                    break;
                }
                
                foreach($join_tables as $join_table) {
                    if(stristr($from,$join_table)) {
                        $sort_field="\"$join_table\".\"SortOrder\"";
                        
                        if(isset($query->select['SortOrder'])) {
                            $query->select['SortOrder']="\"{$this->owner->class}\".SortOrder AS LocalSort";
                        }
                        
                        break;
                    }
                }
            }
        }
        
        
        if(!$sort_field) {
            $sort_field="\"SortOrder\"";
        }
        
        if(!$query->orderby || ($query->orderby==$this->owner->stat('default_sort'))) {
            $query->orderby="$sort_field ".self::$sort_dir;
        }
    }
    
    /**
     * Sets the sort order on the DataObject when its being written to the database for the first time
     */
    public function onBeforeWrite() {
        if(!$this->owner->ID) {
            if($peers=DataList::create($this->owner->class)) {
                $this->owner->SortOrder=$peers->Count()+1;
            }
        }
    }
}
?>
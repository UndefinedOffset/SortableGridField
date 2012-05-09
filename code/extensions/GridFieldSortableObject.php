<?php
class GridFieldSortableObject extends DataExtension {
    public static $db=array(
                            'SortOrder'=>'Int'
                        );
    
    protected static $sortable_classes = array();
    protected static $many_many_sortable_relations = array();
    protected static $sort_dir = "ASC";
    
    
    public static function set_sort_dir($dir) {
        self::$sort_dir=$dir;
    }


    public static function add_sortable_class($className) {
        if(!self::is_sortable_class($className)) {
            Object::add_extension($className, 'GridFieldSortableObject');
            
            self::$sortable_classes[]=$className;
        }
    }

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
    
    public static function remove_sortable_class($class) {
        Object::remove_extension($class, 'GridFieldSortableObject');
    }
    
    public static function is_sortable_class($classname) {
        if(in_array($classname, self::$sortable_classes)) {
            return true;
        }
        
        foreach(self::$sortable_classes as $class) {
            if(is_subclass_of($classname, $class)) {
                return true;
            }
        }
        
        
        return Object::has_extension($classname, 'GridFieldSortableObject');
    }
    
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
    
    public static function get_join_tables($classname) {
        if(isset(self::$many_many_sortable_relations[$classname])) {
            return self::$many_many_sortable_relations[$classname];
        }
        
        return false;
    }
    
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
    
    public function onBeforeWrite() {
        if(!$this->owner->ID) {
            if($peers=DataList::create($this->owner->class)) {
                $this->owner->SortOrder=$peers->Count()+1;
            }
        }
    }
}
?>
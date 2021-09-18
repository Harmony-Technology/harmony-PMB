<?php
// +-------------------------------------------------+
// � 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: Orm.php,v 1.23 2021/03/26 08:51:56 qvarin Exp $
namespace Pmb\Common\Orm;

use Pmb\Common\Helper\Helper;

/**
 *
 * @author arenou
 *        
 */
abstract class Orm
{

    // Declaration des proprietes obligatoires
    /**
     *
     * @var string
     */
    public static $tableName = "";

    /**
     *
     * @var string
     */
    public static $idTableName = "";

    /**
     * Cl� primaire suppl�mentaire
     * @var array
     */
    public static $primaryKeyAditional = [];
    
    private $structure = [];

    protected static $relations = [];

    /**
     *
     * @var \ReflectionClass
     */
    protected static $reflectionClass = null;

    /**
     */
    public function __construct(int $id = 0)
    {
        $this->initDataStructure();
        $this->initRelationsDefinition();
        if ($id > 0) {
            $this->fetchData($id);
        }
    }

    /**
     *
     * @param int $id
     * @throws \Exception
     * @return \Pmb\Common\Orm\Orm
     */
    protected function fetchData(int $id)
    {
        $query = 'select * from ' . static::$tableName . ' where ' . static::$idTableName . ' = ' . $id;
        $result = pmb_mysql_query($query);
        if (! pmb_mysql_num_rows($result)) {
            throw new \Exception("No record found");
        }
        $row = pmb_mysql_fetch_assoc($result);
        return $this->feedObject($row);
    }

    /**
     *
     * @param array $data
     * @return \Pmb\Common\Orm\Orm
     */
    public function feedObject(array $data)
    {
        foreach ($data as $key => $value) {
            $this->{$key} = $this->formatValue($key, $value);
        }
        return $this;
    }
    
    private function formatValue($prop, $value)
    {
        switch (gettype($this->{$prop}))
        {
            case 'boolean':
                return boolval($value);
            case 'integer':
                return intval($value);
            case 'double' ;
            case 'float' ;
                return floatval($value);
        }
        return $value;
    }

    public function save()
    {
        $query = "replace into " . static::$tableName . " (" . implode(',', array_keys($this->structure)) . ") values ('";
        $values = [];
        foreach ($this->structure as $key => $val) {
            // TODO Addslashes plus fin...
            $values[] = addslashes($this->{$key});
        }
        $query .= implode("','", $values) . "')";
        pmb_mysql_query($query);
        $this->{static::$idTableName} = pmb_mysql_insert_id();
    }

    public function delete()
    {
        $query = "delete from " . static::$tableName . " where " . static::$idTableName . " = " . $this->{static::$idTableName};
        pmb_mysql_query($query);
        $defaultProperties = static::$reflectionClass->getDefaultProperties();
        foreach ($this->structure as $key => $val) {
            $this->{$key} = $defaultProperties[$key];
        }
    }

    public static function deleteWhere($field, $value)
    {
        $query = "delete from " . static::$tableName . " where " . $field . " = " . $value;
        pmb_mysql_query($query);
    }

    public function setId(int $id)
    {
        return $this->fetchData($id);
    }

    public function __set($label, $value)
    {
        
        if (static::$reflectionClass->hasMethod(Helper::camelize("set " . $label))) {
            return $this->{Helper::camelize("set " . $label)}($value);
        }
        if (static::$reflectionClass->hasProperty($label)) {
            $this->{$label} = $value;
            return $this;
        }
        throw new \Exception("Unknown property");
    }

    public function __get($label)
    {
        if (static::$reflectionClass->hasMethod(Helper::camelize("get " . $label))) {
            return $this->{Helper::camelize("get " . $label)}();
        }

        if (in_array($label, array_keys(static::$relations))) {
            return $this->getRelated($label);
        }
        if (static::$reflectionClass->hasProperty($label)) {
            return $this->{$label};
        }
        throw new \Exception("Unknown property");
    }

    private function initDataStructure()
    {
        static::$reflectionClass = new \ReflectionClass($this);
        $query = "show columns from " . static::$tableName;
        $result = pmb_mysql_query($query);
        if (pmb_mysql_num_rows($result)) {
            while ($row = pmb_mysql_fetch_object($result)) {
                $this->structure[$row->Field] = $row;
                
                // On v�rifie que la propri�t� existe sur l'ORM
                $rowField = $row->Field;
                if (! empty(static::$tablePrefix)) {
                    $rowField = str_replace(static::$tablePrefix . '_custom_', '', $row->Field);
                }
                
                if (false === static::$reflectionClass->hasProperty($rowField)) {
                    throw new \Exception("$rowField is missing");
                }
                    
                // On v�rifie l'existance de la cl� primaire
                if ('PRI' === $row->Key && (static::$idTableName !== $row->Field && !in_array($row->Field, static::$primaryKeyAditional))) {
                    throw new \Exception("Wrong primary key");
                }
            }
        }
        return $this->structure;
    }

    private function initRelationsDefinition()
    {
        if (! empty(static::$relations)) {
            return static::$relations;
        }
        // On reconstruit la structure d�crivant les relations
        foreach (static::$reflectionClass->getProperties() as $property) {
            $comment = $property->getDocComment();
            $matches = [];
            if (false !== $comment) {

                if (preg_match_all('/@(Relation|RelatedKey|ForeignKey|TableLink|Table|Orm)\s([^\s]+)/', $comment, $matches)) {
                    static::$relations[$property->getName()] = [];
                    for ($i = 0; $i < count($matches[0]); $i ++) {
                        static::$relations[$property->getName()][$matches[1][$i]] = $matches[2][$i];
                    }
                }
            }
        }
        $this->checkRelationsDefinition();
        return static::$relations;
    }

    private function checkRelationsDefinition()
    {
        foreach (static::$relations as $definition) {
            if (empty($definition['Relation'])) {
                throw new \Exception("Relation required");
            }
            if (empty($definition['Orm'])) {
                throw new \Exception("Related ORM required");
            }
            switch ($definition['Relation']) {
                case "0n":
                    if (empty($definition['RelatedKey'])) {
                        throw new \Exception("RelatedKey required");
                    }
                    break;
                case "n0":
                    if (empty($definition['Table'])) {
                        throw new \Exception("Table required");
                    }
                    if (empty($definition['ForeignKey'])) {
                        throw new \Exception("ForeignKey required");
                    }
                    if (empty($definition['RelatedKey'])) {
                        throw new \Exception("RelatedKey required");
                    }
                    break;
                case "nn":
                    if (empty($definition['RelatedKey'])) {
                        throw new \Exception("RelatedKey required");
                    }
                    if (empty($definition['ForeignKey'])) {
                        throw new \Exception("ForeignKey required");
                    }
                    if (empty($definition['TableLink'])) {
                        throw new \Exception("TableLink required");
                    }
                    break;
            }
        }
    }

    /**
     *
     * @param string $label
     * @return string
     */
    private function getRelated(string $label)
    {
        // Si la property n'est pas � null, on y est d�j� pass�, donc on �vite le recalcul
        if ($this->{$label} !== null) {
            return $this->{$label};
        }
        $relation = static::$relations[$label];
        $result = pmb_mysql_query($this->getRelatedQuery($relation));
        $this->{$label} = false;
        if ("n1" !== $relation["Relation"]) {
            $this->{$label} = [];
        }
        if (pmb_mysql_num_rows($result)) {
            $ormClass = $relation['Orm'];
            while ($row = pmb_mysql_fetch_assoc($result)) {
                if (!empty($row['related_id'])) {
                    $obj = new $ormClass($row['related_id']);
                    if (is_array($this->{$label})) {
                        $this->{$label}[] = $obj;
                    } else {
                        $this->{$label} = $obj;
                    }
                }
            }
        }
        return $this->{$label};
    }

    /**
     *
     * @param array $relation
     * @throws \Exception
     * @return string
     */
    private function getRelatedQuery($relation)
    {
        switch ($relation['Relation']) {
            case "n0":
                return "select {$relation['RelatedKey']} as related_id from {$relation['Table']} where  {$relation['ForeignKey']} = {$this->{static::$idTableName}}";
            case "0n":
                return "select {$relation['RelatedKey']} as related_id from " . static::$tableName . " where " . static::$idTableName . " = {$this->{static::$idTableName}}";
            case "nn":
                return "select {$relation['RelatedKey']} as related_id from {$relation['TableLink']} join " . static::$tableName . " on {$relation['ForeignKey']} = " . static::$idTableName . " where " . static::$idTableName . " = {$this->{static::$idTableName}}";
            default:
                throw new \Exception("Unknown relation");
        }
    }

    /**
     *
     * @param int $id
     * @param boolean $fetchFlag
     * @return object|boolean
     */
    public static function findById(int $id)
    {
        try {
            $className = static::class;
            $instance = new $className($id);
            return $instance;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     *
     * @return array
     */
    public static function findAll()
    {
        $query = "SELECT * FROM " . static::$tableName;
        $result = pmb_mysql_query($query);
        $instances = array();
        if (pmb_mysql_num_rows($result)) {
            foreach ($result as $row) {
                $className = static::class;
                $instance = new $className(intval($row[static::$idTableName]));
                $instances[] = $instance;
            }
        }
        return $instances;
    }
    
    /**
     *
     * @return array
     */
    public static function find($field, $value, $orderby = "")
    {
        $query = "SELECT * FROM " . static::$tableName ." WHERE $field = '$value'" . ($orderby ? " ORDER BY $orderby": '');
        $result = pmb_mysql_query($query);
        $instances = array();
        if (pmb_mysql_num_rows($result)) {
            foreach ($result as $row) {
                $className = static::class;
                $instance = new $className(intval($row[static::$idTableName]));
                $instances[] = $instance;
            }
        }
        return $instances;
    }
    
    public function toArray()
    {
        $object = array();
        static::$reflectionClass = new \ReflectionClass($this);
        foreach (static::$reflectionClass->getProperties() as $property) {
            if (!$property->isStatic()) {
                if (is_array($this->{$property->name})) {
                    foreach ($this->{$property->name} as $property_array) {
                        if (is_object($property_array) && is_a($property_array, "\\Pmb\\Common\\Orm\\Orm")) {
                            $object[$property->name][] = $property_array->toArray();
                        } else {
                            $object[$property->name][] = $property_array;
                        }
                    }
                } elseif (is_object($this->{$property->name}) && is_a($this->{$property->name}, "\\Pmb\\Common\\Orm\\Orm")) {
                    $object[$property->name] = $this->{$property->name}->toArray();
                } else {
                    $object[$property->name] = $this->{$property->name};
                }
            }
        }
        return $object;
    }
    
    public function getInfos()
    {
        $infos = array();
        static::$reflectionClass = new \ReflectionClass($this);
        foreach (static::$reflectionClass->getProperties() as $property) {
            if (!$property->isStatic()) {
                if (!is_a($this->{$property->name}, "\\Pmb\\Common\\Orm\\Orm")) {
                    $infos[$property->name] = $this->{$property->name};
                }
            }
        }
        return $infos;
    }
    
    public function getCmsStructure(string $prefixVar = "", bool $children = false)
    {
        global $msg;
        
        $cmsStructure = array();
        if (!$children) {
            $cmsStructure[0]['var'] = $msg['cms_module_common_datasource_main_fields'];
            $cmsStructure[0]['children'] = array();
        }
        
        foreach ($this->structure as $key => $val) {
            
            $var = addslashes($key);
            $msgVar = addslashes($key);
            if (!empty($prefixVar)) {
                $var = addslashes($prefixVar.".".$key);
                $msgVar = addslashes($prefixVar."_".$key);
            }
            
            if (!$children) {
                $length = count($cmsStructure[0]['children']);
                $cmsStructure[0]['children'][$length]['var'] = $var;
                $cmsStructure[0]['children'][$length]['desc'] = "";
            } else {
                $length = count($cmsStructure);
                $cmsStructure[$length]['var'] = $var;
                $cmsStructure[$length]['desc'] = "";
            }
            
            switch (true) {
                case isset($msg['cms_module_common_datasource_desc_'.$msgVar]):
                    $desc = $msg['cms_module_common_datasource_desc_'.$msgVar];
                    break;
                    
                case isset($msg[$msgVar]):
                    $desc = $msg[$msgVar];
                    break;
                
                default:
                    $desc = addslashes($msgVar);
                    break;
            }
            
            if (!$children) {
                $cmsStructure[0]['children'][$length]['desc'] = $desc;
            } else {
                $cmsStructure[$length]['desc'] = $desc;
            }
        }
        
        if (!empty(static::$relations) && !$children) {
            foreach (static::$relations as $key => $relation) {
                $length = count($cmsStructure[0]['children']);
                $cmsStructure[0]['children'][$length]['var'] = addslashes($key);
                $cmsStructure[0]['children'][$length]['desc'] = "";
                $cmsStructure[0]['children'][$length]['children'] = array();
                if (!empty($relation['Orm'])) {
                    $baseVar = $key;
                    if ($relation['Relation'] == "nn") {
                        $baseVar .= '[i]';
                    }
                    $relation_orm = new $relation['Orm']();
                    $cmsStructure[0]['children'][$length]['children'] = $relation_orm->getCmsStructure($baseVar, true);
                }
            }
        }
        
        return $cmsStructure;
    }
    
    public function getCmsData()
    {
        $data = array();
        
        foreach ($this->structure as $key => $val) {
            $data[addslashes($key)] = $this->{$key};
        }
        
        if (!empty(static::$relations)) {
            foreach (static::$relations as $key => $relation) {
                $data[addslashes($key)] = array();
                
                $relations = $this->getRelated($key);
                if (!empty($relations)) {
                    if ($relation['Relation'] == "nn") {
                        $data[addslashes($key)] = $relations;
                    } else {
                        $data[addslashes($key)] = $relations[0];
                    }
                }
            }
        }

        return $data;
    }
}
<?php
/**
 * @table soycms_data_sets
 */
class DataSets
{
    /**
     * @id
     */
    private $id;

    /**
     * @column class_name
     */
    private $className;

    /**
     * @column object_data
     */
    private $object;

    public function getId()
    {
        return $this->id;
    }
    public function setId($id)
    {
        $this->id = $id;
    }
    public function getClassName()
    {
        return $this->className;
    }
    public function setClassName($className)
    {
        $this->className = $className;
    }
    public function getObject()
    {
        return $this->object;
    }
    public function setObject($object)
    {
        $this->object = $object;
    }

    public static function put($class, $obj)
    {
        $data = new DataSets();
        $data->setClassName($class);
        $data->setObject(serialize($obj));

        $dao = SOY2DAOFactory::create("cms.DataSetsDAO");
        try {
            $dao->clear($class);
        } catch (Exception $e) {
            //throw
        }

        $dao->insert($data);
    }

    public static function get($class, $onNull = false)
    {
        try {
            $dao = SOY2DAOFactory::create("cms.DataSetsDAO");
            $data = $dao->getByClass($class);

            $res = unserialize($data->getObject());
            if ($res === false) {
                throw new Exception();
            }

            return $res;
        } catch (Exception $e) {
            if ($onNull !== false) {
                return $onNull;
            } else {
                return null;
            }
        }
    }
}

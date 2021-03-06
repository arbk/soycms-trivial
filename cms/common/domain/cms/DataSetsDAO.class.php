<?php
/**
 * @entity cms.DataSets
 */
abstract class DataSetsDAO extends SOY2DAO
{
    /**
     * @final
     */
    public function init()
    {
        $sql = <<<SQL
create table soymcs_data_sets(
  id integer primary key,
  class_name varchar unique,
  object_data text
);
SQL;
        $this->executeQuery($sql, array());
    }

    abstract public function insert(DataSets $bean);

    /**
     * @return object
     * @query class_name = :class
     */
    abstract public function getByClass($class);

    /**
     * @sql delete from soycms_data_sets where class_name = :class
     */
    abstract public function clear($class);

    abstract public function get();
}

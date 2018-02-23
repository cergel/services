<?php
namespace sdkService\helper;

/**
 * PDO数据处理类
 */
class PDO
{
    /**
     * pdo对象
     *
     * @var \PDO
     */
    private $pdo;
    /**
     * 数据表名
     *
     * @var string
     */
    private $tableName;

    /**
     * 构造更新字段
     *
     * @param string $field
     * @return string
     */
    private static function updateFieldMap($field)
    {
        return '`' . $field . '`=:' . $field;
    }

    /**
     * 构造更新字段
     *
     * @param string $field
     * @return string
     */
    private static function changeFieldMap($field)
    {
        return '`' . $field . '`=`' . $field . '`+:' . $field;
    }

    /**
     * 构造函数
     *
     * @param string $className
     */
    public function __construct($tableName)
    {
        $this->tableName = $tableName;
    }

    /**
     * 取得表名
     *
     * @return String
     */
    public function getTableName()
    {

        return $this->tableName;
    }

    /**
     * 取得PDO对象
     *
     * @return \PDO
     */
    public function getPdo()
    {
        return $this->pdo;
    }

    public function closePdo()
    {
        $this->pdo = null;
    }

    /**
     * 设置PDO对象
     *
     * @param \PDO $pdo
     */
    public function setPdo($pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * 添加一个对象到数据库
     *
     * @param Object $entity
     * @param array $fields
     * @param string $onDuplicate
     * @return int
     */
    public function add($entity, $fields, $onDuplicate = null)
    {
        $strFields = '`' . implode('`,`', $fields) . '`';
        $strValues = ':' . implode(', :', $fields);

        $query = "INSERT INTO `{$this->getTableName()}` ({$strFields}) VALUES ({$strValues})";

        if (!empty($onDuplicate)) {
            $query .= 'ON DUPLICATE KEY UPDATE ' . $onDuplicate;
        }

        $statement = $this->pdo->prepare($query);
        $params = array();

        foreach ($fields as $field) {
            $params[$field] = $entity->$field;
        }

        $statement->execute($params);
        return $this->pdo->lastInsertId();
    }


    /**
     * @param array $params  insert的数据 eg: ['name'=>'ddd']
     * @param array $fields  数据库字段名 eg:  array_keys(['name'=>'ddd'])
     * @param null $onDuplicate  upsert字符串 eg: name='dddd'
     * @return string
     */
    public function Nadd($params, $fields, $onDuplicate = null)
    {
        $strFields = '`' . implode('`,`', $fields) . '`';
        $strValues = ':' . implode(', :', $fields);
        // $query = "INSERT " .  " INTO `". $this->tableName . "`(" . $strFields . ") VALUES (" . $strValues . ")";
        //修改后
        $query = "INSERT " . " INTO `" . $this->getTableName() . "`(" . $strFields . ") VALUES (" . $strValues . ")";
        if ($onDuplicate != null) {
            $query .= ' ON DUPLICATE KEY UPDATE ' . $onDuplicate;
        }
        $statement = $this->pdo->prepare($query);
        $statement->execute($params);
        return $this->pdo->lastInsertId();
    }

    /**
     * 扩展插入
     *
     * @param array $entitys
     * @param array $fields
     * @return bool
     */
    public function addMulti($entitys, $fields)
    {
        $items = array();
        $params = array();

        foreach ($entitys as $index => $entity) {
            $items[] = '(:' . implode($index . ', :', $fields) . $index . ')';

            foreach ($fields as $field) {
                $params[$field . $index] = $entity->$field;
            }
        }

        $query = "INSERT INTO `{$this->getTableName()}` (`" . implode('`,`', $fields) . "`) VALUES " . implode(',',
                $items);
        $statement = $this->pdo->prepare($query);
        return $statement->execute($params);
    }

    /**
     * REPLACE模式添加一个对象到数据库
     *
     * @param Object $entity
     * @param array $fields
     * @return int
     */
    public function replace($entity, $fields)
    {
        $strFields = '`' . implode('`,`', $fields) . '`';
        $strValues = ':' . implode(', :', $fields);

        $query = "REPLACE INTO `{$this->getTableName()}` ({$strFields}) VALUES ({$strValues})";
        $statement = $this->pdo->prepare($query);
        $params = array();

        foreach ($fields as $field) {
            $params[$field] = $entity->$field;
        }

        $statement->execute($params);
        return $this->pdo->lastInsertId();
    }

    /**
     * 更新所有符合条件的对象
     *
     * @param array $fields  eg: array_keys(['name'=>'ddd'])
     * @param array $params eg:['name'=>'ddd']
     * @param string $where eg: id=1
     * @param bool $change
     * @return bool
     */
    public function update($fields, $params, $where, $change = false)
    {
        if ($change) {
            $updateFields = array_map(__CLASS__ . '::changeFieldMap', $fields);
        } else {
            $updateFields = array_map(__CLASS__ . '::updateFieldMap', $fields);
        }

        $strUpdateFields = implode(',', $updateFields);
        $query = "UPDATE `{$this->getTableName()}` SET {$strUpdateFields} WHERE {$where}";
        $statement = $this->pdo->prepare($query);
        try {
            $statement->execute($params);
            $rowCount = $statement->rowCount();
            return $statement->rowCount();
        } catch (\Exception $ex) {
            //echo $ex->getMessage();
        }

    }


    /**
     * 更新加减专用
     *
     * @param Array $fields 更新的字段
     * @param string $where 更新条件
     * @return integer 此次由于DELETE,INSERT,UPDATE操作受影响的行数.
     */
    public function newupdate($fields, $where)
    {
        $arr = array();
        foreach ($fields as $k => $v) {
            if ($v == 0) {
                continue;
            }
            $arr[] = " $k=$k+$v ";
        }
        if (count($arr) == 0) {
            return 0;
        }
        $strUpdateFields = join(",", $arr);
        $query = "UPDATE `" . $this->getTableName() . "` SET " . $strUpdateFields . " WHERE " . $where;
        $statement = $this->pdo->prepare($query);
        $statement->execute();
        return $statement->rowCount();
    }


    /**
     * 取得符合条件的第一条记录的第一个值
     *
     * @param string $where
     * @param array $params
     * @param string $fields
     * @return mixed
     */
    public function fetchValue($where = '1', $params = null, $fields = '*')
    {
        $query = "SELECT {$fields} FROM `{$this->getTableName()}` WHERE {$where} limit 1";
        $statement = $this->pdo->prepare($query);
        //error_log($query, '3' , '/tmp/chapter.log');
        $statement->execute($params);
        return $statement->fetchColumn();
    }

    /**
     * 取得所有符合条件的数据（数组）
     *
     * @param string $where "id = :id"
     * @param array $params [":id=>1"]
     * @param string $fields
     * @param string $orderBy
     * @param string $limit
     * @return array
     */
    public function fetchArray($where = '1', $params = null, $fields = '*', $orderBy = null, $limit = null)
    {
        $query = "SELECT {$fields} FROM `{$this->getTableName()}` WHERE {$where}";

        if ($orderBy) {
            $query .= " ORDER BY {$orderBy}";
        }

        if ($limit) {
            $query .= " limit {$limit}";
        }

        $statement = $this->pdo->prepare($query);
        $statement->execute($params);
        $statement->setFetchMode(\PDO::FETCH_ASSOC);
        return $statement->fetchAll();
    }



    /**
     * 查询sql
     *
     * @param string $where "id = :id"
     * @param array $params [":id=>1"]
     * @param string $fields
     * @param string $orderBy
     * @param string $limit
     * @return array
     */
    public function fetchArrayBySql($sql , $params = null)
    {
        $query = $sql;
        $statement = $this->pdo->prepare($query);
        $statement->execute($params);
        $statement->setFetchMode(\PDO::FETCH_ASSOC);
        return $statement->fetchAll();
    }

    /**
     * 查询一条sql
     *
     * @param string $where "id = :id"
     * @param array $params [":id=>1"]
     * @param string $fields
     * @param string $orderBy
     * @param string $limit
     * @return array
     */
    public function fetchOneBySql($sql , $params = null)
    {
        $query = $sql;
        $statement = $this->pdo->prepare($query);
        $statement->execute($params);
        $statement->setFetchMode(\PDO::FETCH_ASSOC);
        return $statement->fetch();
    }

    /**
     * 执行sql
     *
     * @param string $where "id = :id"
     * @param array $params [":id=>1"]
     * @param string $fields
     * @param string $orderBy
     * @param string $limit
     * @return array
     */
    public function excuteBySql($sql , $params = null)
    {
        $query = $sql;
        $statement = $this->pdo->prepare($query);
        $statement->execute($params);
        $rowCount = $statement->rowCount();
        return $rowCount;
    }


    /**
     * 获取一条记录，返回一维数组
     * @param string $where "id=:id"
     * @param null $params [":id"=>6]
     * @param string $fields
     * @param null $orderBy
     * @return array
     */
    public function fetchOne($where = '1', $params = null, $fields = '*', $orderBy = null)
    {
        $query = "SELECT {$fields} FROM `{$this->getTableName()}` WHERE {$where}";

        if ($orderBy) {
            $query .= " ORDER BY {$orderBy}";
        }

        $query .= " limit 1";
        $statement = $this->pdo->prepare($query);
        $statement->execute($params);
        $statement->setFetchMode(\PDO::FETCH_ASSOC);
        return $statement->fetch();
    }

    /**
     * 获取所有符合条件的数据的第一列（一维数组）
     *
     * @param string $where
     * @param array $params
     * @param string $fields
     * @param string $orderBy
     * @param string $limit
     * @return array
     */
    public function fetchCol($where = '1', $params = null, $fields = '*', $orderBy = null, $limit = null)
    {
        $results = $this->fetchArray($where, $params, $fields, $orderBy, $limit);
        return empty($results) ? array() : array_map('reset', $results);
    }

    /**
     * 取得所有符合条件的对象
     *
     * @param string $where
     * @param array $params
     * @param string $fields
     * @param string $orderBy
     * @param string $limit
     * @return array
     * @throws \Exception
     */
    public function fetchAll($where = '1', $params = null, $fields = '*', $orderBy = null, $limit = null)
    {
        $query = "SELECT {$fields} FROM `{$this->getTableName()}` WHERE {$where}";

        if ($orderBy) {
            $query .= " order by {$orderBy}";
        }

        if ($limit) {
            $query .= " limit {$limit}";
        }
        $statement = $this->pdo->prepare($query);
        if (!$statement->execute($params)) {
            throw new \Exception('data base error');
        }
        $statement->setFetchMode(\PDO::FETCH_OBJ);
        return $statement->fetchAll();
    }

    /**
     * 根据条件返回一个对象
     *
     * @param string $where
     * @param array $params
     * @param string $fields
     * @return object
     */
    public function fetchObject($where = '1', $params = null, $fields = '*',$orderBy = null)
    {
        $query = "SELECT {$fields} FROM `{$this->getTableName()}` WHERE {$where}";

        if ($orderBy) {
            $query .= " order by {$orderBy}";
        }
        $query .= " limit 1";
        $statement = $this->pdo->prepare($query);
        //error_log($query, '3' , '/tmp/chapter.log');
        $statement->execute($params);
        $statement->setFetchMode(\PDO::FETCH_OBJ);
        return $statement->fetch();
    }


    /**
     * 删除符合条件的记录
     *
     * @param string $where
     * @param array $params
     * @return int 返回执行成功后影响的行数
     */
    public function remove($where, $params = null)
    {
        if (empty($where)) {
            return false;
        }
        $query = "DELETE FROM `{$this->getTableName()}` WHERE {$where}";
        $statement = $this->pdo->prepare($query);
        $statement->execute($params);
        return $statement->rowCount();
    }

    /**
     * 清空表
     */
    public function truncate()
    {
        $query = "delete from `{$this->getTableName()}`";
        $statement = $this->pdo->prepare($query);
        return $statement->execute(null);
    }

    public function multiInsert($inserts, $fields)
    {
        if (is_array($inserts) && is_array($fields)) {
            $query = "INSERT INTO `{$this->getTableName()}` (`" . implode('`,`', $fields) . "`) VALUES " . implode(',',
                    $inserts);
            $statement = $this->pdo->prepare($query);
            return $statement->execute();
        }
    }
}
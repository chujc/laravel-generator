<?php
namespace ChuJC\LaravelGenerator\Support;

use DB;

class Database {

    protected $databaseEngine = 'mysql';

    public function __construct($engine = 'mysql')
    {
        $this->databaseEngine = strtolower($engine);
    }

    /**
     * 获取表对应的字段
     * @param $table
     * @return array
     */
    public function getTableColumns($table)
    {
        switch ($this->databaseEngine) {
            case 'mysql':
                $columns = DB::select("SELECT COLUMN_NAME as `name`, DATA_TYPE as `type` FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = '" . env("DB_DATABASE") . "' AND TABLE_NAME = '{$table}'");
                break;
            case 'sqlsrv':
            case 'dblib':
                $columns = DB::select("SELECT COLUMN_NAME as 'name', DATA_TYPE as `type` FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_CATALOG = '" . env("DB_DATABASE") . "' AND TABLE_NAME = '{$table}'");
                break;
            case 'pgsql':
                $columns = DB::select("SELECT COLUMN_NAME as name, DATA_TYPE as type FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_CATALOG = '" . env("DB_DATABASE") . "' AND TABLE_NAME = '{$table}'");
                break;
        }
        return $columns;
    }

    /**
     * 获取表对应的主键字段
     * @param $table
     * @return array|null
     */
    public function getTablePrimaryKey($table)
    {
        switch ($this->databaseEngine) {
            case 'mysql':
                $primaryKeyResult = DB::select(
                    "SELECT COLUMN_NAME
                  FROM information_schema.COLUMNS 
                  WHERE  TABLE_SCHEMA = '" . env("DB_DATABASE") . "' AND 
                         TABLE_NAME = '{$table}' AND 
                         COLUMN_KEY = 'PRI'");
                break;

            case 'sqlsrv':
            case 'dblib':
                $primaryKeyResult = DB::select(
                    "SELECT ku.COLUMN_NAME
                   FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS AS tc
                   INNER JOIN INFORMATION_SCHEMA.KEY_COLUMN_USAGE AS ku
                   ON tc.CONSTRAINT_TYPE = 'PRIMARY KEY' 
                   AND tc.CONSTRAINT_NAME = ku.CONSTRAINT_NAME
                   WHERE ku.TABLE_CATALOG ='" . env("DB_DATABASE") . "' AND ku.TABLE_NAME='{$table}';");
                break;

            case 'pgsql':
                $primaryKeyResult = DB::select(
                    "SELECT ku.COLUMN_NAME AS \"COLUMN_NAME\"
                   FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS AS tc
                   INNER JOIN INFORMATION_SCHEMA.KEY_COLUMN_USAGE AS ku
                   ON tc.CONSTRAINT_TYPE = 'PRIMARY KEY' 
                   AND tc.CONSTRAINT_NAME = ku.CONSTRAINT_NAME
                   WHERE ku.TABLE_CATALOG ='" . env("DB_DATABASE") . "' AND ku.TABLE_NAME='{$table}';");
                break;

        }

        if (count($primaryKeyResult) == 1) {
            $table = (object) $primaryKeyResult[0];
            return [$table->COLUMN_NAME];
        } elseif (count($primaryKeyResult) > 1) {
            $primaryKey = [];

            foreach ($primaryKeyResult as $value) {
                $primaryKey[] = $value->COLUMN_NAME;
            }

            return $primaryKey;
        }

        return [];
    }

    /**
     * 获取数据库对应表名
     * @return array
     */
    public function getSchemaTables()
    {
        switch ($this->databaseEngine) {
            case 'mysql':
                $tables = DB::select("SELECT table_name AS name FROM information_schema.tables WHERE table_type='BASE TABLE' AND table_schema = '" . env('DB_DATABASE') . "'" );
                break;
            case 'sqlsrv':
            case 'dblib':
                $tables = DB::select("SELECT table_name AS name FROM information_schema.tables WHERE table_type='BASE TABLE' AND table_catalog = '" . env('DB_DATABASE') . "'" );
                break;
            case 'pgsql':
                $tables = DB::select("SELECT table_name AS name FROM information_schema.tables WHERE table_schema = 'public' AND table_type='BASE TABLE' AND table_catalog = '" . env('DB_DATABASE') . "'");
                break;
        }
        return $tables;
    }
}
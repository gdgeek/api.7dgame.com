<?php
namespace common\components;

use yii\db\Command;

class CynosDbCommand extends Command
{
    /**
     * 拦截 INSERT, UPDATE, DELETE 等非查询语句
     */
    public function execute()
    {
        return $this->db->retry(function () {
            return parent::execute();
        });
    }

    /**
     * 拦截 SELECT 等查询语句
     */
    protected function queryInternal($method, $fetchMode = null)
    {
        return $this->db->retry(function () use ($method, $fetchMode) {
            return parent::queryInternal($method, $fetchMode);
        });
    }
}

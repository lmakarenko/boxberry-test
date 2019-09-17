<?php

namespace common\models;

use Yii;
use yii\db\Query;
use common\models\dbQueryTrait;

/**
 * Trait dbQueryMasterTrait
 * Функционал для работы с master-БД
 * @package common\models
 */
trait dbQueryMasterTrait
{
    /**
     * Common trait functionality
     */
    use dbQueryTrait;
    /**
     * Запрос к master-БД, вовращает результата запроса и строку запроса
     * @param Query $query
     * @return array
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\db\Exception
     */
    public function queryMasterWSql(Query $query)
    {
        return $this->queryWSql($query, 'dbMaster');
    }
    /**
     * Запрос к master-БД, вовращает только результата запроса
     * @param Query $query
     * @return array
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\db\Exception
     */
    public function queryMaster(Query $query)
    {
        return $this->query($query, 'dbMaster');
    }
    /**
     * Запрос к master-БД, используя строку SQL-запроса, вовращает только результата запроса
     * @param String $rawSql
     * @return mixed
     * @throws \yii\base\InvalidConfigException
     */
    public function queryMasterRaw(String $rawSql)
    {
        return $this->queryRaw($query, 'dbMaster');
    }
}
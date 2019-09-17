<?php

namespace common\models;

use Yii;
use yii\db\Query;
use common\models\dbQueryTrait;

/**
 * Trait dbQuerySlaveTrait
 * Функционал для работы со слейв-БД
 * @package common\models
 */
trait dbQuerySlaveTrait
{
    /**
     * Common trait functionality
     */
    use dbQueryTrait;
    /**
     * Запрос к слейв-БД, вовращает результата запроса и строку запроса
     * @param Query $query
     * @return array
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\db\Exception
     */
    public function querySlaveWSql(Query $query)
    {
        return $this->queryWSql($query, 'dbSlave');
    }
    /**
     * Запрос к слейв-БД, вовращает только результата запроса
     * @param Query $query
     * @return array
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\db\Exception
     */
    public function querySlave(Query $query)
    {
        return $this->query($query, 'dbSlave');
    }
    /**
     * Запрос к slave-БД, используя строку SQL-запроса, вовращает только результата запроса
     * @param String $rawSql
     * @return mixed
     * @throws \yii\base\InvalidConfigException
     */
    public function querySlaveRaw(String $rawSql)
    {
        return $this->queryRaw($query, 'dbSlave');
    }
}
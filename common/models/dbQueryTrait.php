<?php

namespace common\models;

use Yii;
use yii\db\Query;

/**
 * Trait dbQueryTrait
 * Общий функционал для работы с несколькими БД
 * @package common\models
 */
trait dbQueryTrait
{
    /**
     * Запрос к БД, вовращает результата запроса и строку запроса
     * @param Query $query
     * @param string $connectionId
     * @return array
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\db\Exception
     */
    public function queryWSql(Query $query, $connectionId)
    {
        $command = $query->createCommand(Yii::$app->get($connectionId));
        return [
            $command->queryAll(),
            $command->getRawSql()
        ];
    }
    /**
     * Запрос к БД, вовращает только результата запроса
     * @param Query $query
     * @param string $connectionId
     * @return array
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\db\Exception
     */
    public function query(Query $query, $connectionId)
    {
        return $query->createCommand(Yii::$app->get($connectionId))->queryAll();
    }
    /**
     * Запрос к БД, используя строку SQL-запроса, вовращает только результата запроса
     * @param String $rawSql
     * @param string $connectionId
     * @return mixed
     * @throws \yii\base\InvalidConfigException
     */
    public function queryRaw(String $rawSql, $connectionId)
    {
        return Yii::$app->get($connectionId)->createCommand($rawSql)->queryAll();
    }
}
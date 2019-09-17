<?php

namespace common\models;

use Yii;
use yii\db\Query;

/**
 * Trait dbActiveRecordSlaveTrait
 * @package common\models
 */
trait dbActiveRecordSlaveTrait
{
    public static function getDb()
    {
        // использовать компонент приложения "dbSlave"
        return Yii::$app->dbSlave;
    }
}
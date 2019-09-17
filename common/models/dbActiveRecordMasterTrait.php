<?php

namespace common\models;

use Yii;
use yii\db\Query;

/**
 * Trait dbActiveRecordMasterTrait
 * @package common\models
 */
trait dbActiveRecordMasterTrait
{
    public static function getDb()
    {
        // использовать компонент приложения "dbMaster"
        return Yii::$app->dbMaster;
    }
}
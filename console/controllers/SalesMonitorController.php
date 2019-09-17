<?php

namespace console\controllers;

use Yii;
use yii\console\Controller;
use yii\console\ExitCode;
use yii\helpers\Console;
use console\models\AviaSales;

class SalesMonitorController extends Controller
{
    /**
     * @var AviaSales
     */
    protected $model;

    /**
     * SalesMonitorController constructor.
     * @param $id
     * @param $module
     * @param array $config
     */
    public function __construct($id, $module, $config = [])
    {
        parent::__construct($id, $module, $config);
        $this->model = new AviaSales();
    }

    /**
     * @return int
     */
    public function actionNoSales()
    {
        $this->stdout("SalesMonitor, no avia sales: ", Console::BOLD);
        $data = $this->model->getAviaSalesNoSales();
        if(!empty($data['data'])) {
            Yii::$app->emailAlert->send([
                'tag' => 'sales-monitor',
                'viewName' => 'no-sales',
                'viewData' => $data,
                'subject' => 'Отсуствие продаж авиакомпаний',
            ]);
        }
        $this->stdout("Ok\n", Console::BOLD);
        return ExitCode::OK;
    }

    /**
     * @return int
     */
    public function actionBelow30()
    {
        $this->stdout("SalesMonitor, < 30% avia sales: ", Console::BOLD);
        $data = $this->model->getAviaSalesBelow30();
        if(!empty($data['data'])) {
            Yii::$app->emailAlert->send([
                'tag' => 'sales-monitor',
                'viewName' => 'low-sales',
                'viewData' => $data,
                'subject' => 'Падение продаж авиакомпаний более чем на 30%',
            ]);
        }
        $this->stdout("Ok\n", Console::BOLD);
        return ExitCode::OK;
    }

    /**
     * @return int
     */
    public function actionAbove30()
    {
        $this->stdout("SalesMonitor, > 30% avia sales: ", Console::BOLD);
        $data = $this->model->getAviaSalesAbove30();
        if(!empty($data['data'])) {
            Yii::$app->emailAlert->send([
                'tag' => 'sales-monitor',
                'viewName' => 'high-sales',
                'viewData' => $data,
                'subject' => 'Рост продаж авиакомпаний более чем на 30%',
            ]);
        }
        $this->stdout("Ok\n", Console::BOLD);
        return ExitCode::OK;
    }

    /**
     * Testing all cases and email results to tester
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\db\Exception
     */
    public function actionTest()
    {
        //print_r($this->model->test());
        Yii::$app->emailAlert->send([
            'emailsTo' => ['l.makarenko@biletix.ru' => 'Lev Makarenko'],
            'tag' => 'sales-monitor',
            'viewName' => 'test',
            'viewData' => ['data' => $this->model->test()],
            'subject' => 'Test email',
        ]);
    }
}
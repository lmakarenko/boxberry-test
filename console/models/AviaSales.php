<?php

namespace console\models;

use Yii;
use console\common\models\BaseModel;
use yii\db\Query;
use common\models\dbQuerySlaveTrait;

/**
 * Class AviaSales
 * Модель для работы с данными для статистических отчетов авиакомпаний
 * @package console\models
 */
class AviaSales extends BaseModel
{
    use dbQuerySlaveTrait;
    /**
     * Возвращает обьект запроса выборки сгруппированных кодов авиакомпаний,
     * кол-ва проданных билетов каждой авиакомпанией, за период с даты DATE1 по DATE2,
     * исключая из выборки заказы чартеров CRS = 1Б
     * Пример SQL-запроса формируемого через QueryBuilder:
     * SELECT
            ibe_airseg.AK, COUNT(DISTINCT ibe_order.ORDER_ID) ORDER_COUNT
        FROM
            ibe_airseg
        JOIN ibe_order ON ibe_order.ORDER_ID = ibe_airseg.ORDID
        WHERE
            ibe_order.TKTTIME >= '2019-08-29-00 00:00:00'
            AND ibe_order.TKTTIME <= '2019-08-30-00 00:00:00'
            AND ibe_order.CRS != '1Б'
        GROUP BY
            ibe_airseg.AK
     * @return yii\db\Query
     */
    protected function getAviaSalesQuery(Array $params)
    {
        $query = (new Query)
            ->select(['`ibe_airseg`.`AK`', 'COUNT(DISTINCT `ibe_order`.`ORDER_ID`) `ORDER_COUNT`'])
            ->from(['ibe_airseg'])
            ->innerJoin('ibe_order', '`ibe_airseg`.`ORDID` = `ibe_order`.`ORDER_ID`')
            ->where(['>=', 'ibe_order.TKTTIME', "{$params['DATE1']}"])
            ->andWhere(['<=', 'ibe_order.TKTTIME', "{$params['DATE2']}"])
            ->andWhere(['!=', 'ibe_order.CRS', '1Б'])
            ->groupBy(['ibe_airseg.AK']);
        return $query;
    }
    /**
     * Возвращает обьект запроса выборки сгруппированных кодов авиакомпаний,
     * кол-ва проданных билетов каждой авиакомпанией за два периода:
     *  1 период: начиная с DATE1[0] по DATE1[1]
     *  2 период: начиная с DATE2[0] по DATE2[1]
     * Выборка содержит относительное изменение (долю) кол-ва продаж билетов каждой авиакомпанией относительно двух периодов
     * Заказы чартеров CRS = 1Б исключаются из выборки
     * Пример SQL-запроса формируемого через QueryBuilder:
     * SELECT
            t1.AK,
            t1.ORDER_COUNT ORDER_COUNT_1,
            t2.ORDER_COUNT ORDER_COUNT_2,
            ROUND(t2.ORDER_COUNT / t1.ORDER_COUNT, 2) - 1 SALES_GROWTH_PERCENT
        FROM
            (SELECT
                ibe_airseg.AK, COUNT(DISTINCT ibe_order.ORDER_ID) ORDER_COUNT
            FROM
                ibe_airseg
            JOIN ibe_order ON ibe_order.ORDER_ID = ibe_airseg.ORDID
            WHERE
                ibe_order.TKTTIME >= '2019-08-28 00:00:00'
                AND ibe_order.TKTTIME <= '2019-08-29 00:00:00'
                AND ibe_order.CRS != '1Б'
            GROUP BY
                ibe_airseg.AK) t1,
            (SELECT
                ibe_airseg.AK, COUNT(DISTINCT ibe_order.ORDER_ID) ORDER_COUNT
            FROM
                ibe_airseg
            JOIN ibe_order ON ibe_order.ORDER_ID = ibe_airseg.ORDID
            WHERE
                ibe_order.TKTTIME >= '2019-08-29 00:00:00'
     *          AND ibe_order.TKTTIME <= '2019-08-30 00:00:00'
                AND ibe_order.CRS != '1Б'
            GROUP BY
                ibe_airseg.AK) t2
        WHERE
            t1.AK = t2.AK
     * @return yii\db\Query
     */
    protected function getAviaSalesGrowthQuery(Array $params)
    {
        $subQuery1 = $this->getAviaSalesQuery([
            'DATE1' => $params['DATE1'][0],
            'DATE2' => $params['DATE1'][1],
        ]);
        $subQuery2 = $this->getAviaSalesQuery([
            'DATE1' => $params['DATE2'][0],
            'DATE2' => $params['DATE2'][1],
        ]);
        $query = (new Query)
            ->select([
                'AK' => 't1.AK',
                'ORDER_COUNT_1' => 't1.ORDER_COUNT',
                'ORDER_COUNT_2' => 't2.ORDER_COUNT',
                'SALES_GROWTH_PERCENT' => 'ROUND(t2.ORDER_COUNT / NULLIF(t1.ORDER_COUNT, 0), 2) - 1',
            ])
            ->from([
                't1' => $subQuery1,
                't2' => $subQuery2,
            ])
            ->where('`t1`.`AK` = `t2`.`AK`');
        return $query;
    }

    /**
     * Возвращает обьект запроса аналогично getAviaSalesGrowthQuery,
     * также при выборке учитывается критерий относительного изменения SALES_GROWTH_PERCENT,
     * представляющий собой массив условия [ оператор, операнд ], например :
     *  [ '=', 0 ] - кол-во продаж не изменилось (0%)
     *  [ '<', -0.3 ] - снижение кол-ва продаж (ниже 30%)
     *  [ '>', 0.3 ] - рост кол-ва продаж (свыше 30%)
     *  [ '=', -1 ] - снижение кол-ва продаж на 100%
     *  Конечная выборка сортируется по SALES_GROWTH_PERCENT и по коду авиакомпании
     * @param array $params
     * @return yii\db\Query
     */
    protected function getAviaSalesGrowthQueryWCond(Array $params)
    {
        $growthQuery = $this->getAviaSalesGrowthQuery($params);
        $query = (new Query)
            ->select([])
            ->from([
                't' => $growthQuery
            ])
            ->where([
                $params['SALES_GROWTH_CONDITION'][0], '`t`.`SALES_GROWTH_PERCENT`', $params['SALES_GROWTH_CONDITION'][1]
            ])
            ->orderBy([
                '`t`.`SALES_GROWTH_PERCENT`' => SORT_DESC,
                '`t`.`AK`' => SORT_ASC,
            ]);
        return $query;
    }

    /**
     * Возвращает true, если число заказов для периода с $date1 по $date2 равно 0,
     * иначе возвращает false
     * @param $date1
     * @param $date2
     * @return boolean
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\db\Exception
     */
    public function isAviaSalesEmpty($date1, $date2)
    {
        $query = $this->getAviaSalesQuery([
            'DATE1' => $date1,
            'DATE2' => $date2,
        ])->count();
        return !count($this->querySlave($query));
    }
    /**
     * Возвращает данные кол-ва продаж авиакомпаний, в соответствии с запросом из getAviaSalesGrowthQueryWCond
     * и критерием SALES_GROWTH_CONDITION
     * @param array $params
     * @return array
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\db\Exception
     */
    public function getAviaSalesGrowthWCond(Array $params)
    {
        $query = $this->getAviaSalesGrowthQueryWCond([
            'DATE1' => $params['DATE1'],
            'DATE2' => $params['DATE2'],
            'SALES_GROWTH_CONDITION' => $params['SALES_GROWTH_CONDITION'],
        ]);
        return [
            'data' => $this->querySlave($query),
            'date1' => $params['DATE1'],
            'date2' => $params['DATE2'],
            'condition' => $params['SALES_GROWTH_CONDITION'],
        ];
    }
    /**
     * Возвращает данные кол-ва продаж авиакомпаний, в соответствии с запросом из getAviaSalesGrowthQueryWCond
     * и критерием $salesGrowthCondition = ['=', 0], т.е. выбираются только авиакомпании,
     * у которых кол-во продаж не изменилось, за сегодня относительно вчера
     * @return array
     */
    public function getAviaSalesNone()
    {
        $hour = Date('H', strtotime('-1 hour'));
        return $this->getAviaSalesGrowthWCond([
            'DATE1' => [
                Date('Y-m-d', strtotime('-1 day')) . ' 00:00:00',
                Date('Y-m-d', strtotime('-1 day')) . " {$hour}:59:59"
            ],
            'DATE2' => [
                Date('Y-m-d') . ' 00:00:00',
                Date('Y-m-d') . " {$hour}:59:59"
            ],
            'SALES_GROWTH_CONDITION' => ['=', 0]
        ]);
    }
    /**
     * Возвращает данные кол-ва продаж авиакомпаний, в соответствии с запросом из getAviaSalesGrowthQueryWCond
     * и критерием $salesGrowthCondition = ['<', -0.3], т.е. выбираются только авиакомпании,
     * у которых кол-во продаж снизилось более чем на 30%, за сегодня относительно вчера
     * @return array
     */
    public function getAviaSalesBelow30()
    {
        $hour = Date('H', strtotime('-1 hour'));
        return $this->getAviaSalesGrowthWCond([
            'DATE1' => [
                Date('Y-m-d', strtotime('-1 day')) . ' 00:00:00',
                Date('Y-m-d', strtotime('-1 day')) . " {$hour}:59:59"
            ],
            'DATE2' => [
                Date('Y-m-d') . ' 00:00:00',
                Date('Y-m-d') . " {$hour}:59:59"
            ],
            'SALES_GROWTH_CONDITION' => ['<', -0.3]
        ]);
    }
    /**
     * Возвращает данные кол-ва продаж авиакомпаний, в соответствии с запросом из getAviaSalesGrowthQueryWCond
     * и критерием $salesGrowthCondition = ['>', 0.3], т.е. выбираются только авиакомпании,
     * у которых кол-во продаж увеличилось более чем на 30%, за сегодня относительно вчера
     * @return array
     */
    public function getAviaSalesAbove30()
    {
        $hour = Date('H', strtotime('-1 hour'));
        return $this->getAviaSalesGrowthWCond([
            'DATE1' => [
                Date('Y-m-d', strtotime('-1 day')) . ' 00:00:00',
                Date('Y-m-d', strtotime('-1 day')) . " {$hour}:59:59"
            ],
            'DATE2' => [
                Date('Y-m-d') . ' 00:00:00',
                Date('Y-m-d') . " {$hour}:59:59"
            ],
            'SALES_GROWTH_CONDITION' => ['>', 0.3]
        ]);
    }

    /**
     * Возвращает данные кол-ва продаж авиакомпаний, в соответствии с запросом из getAviaSalesGrowthQueryWCond
     * и критерием $salesGrowthCondition = ['=', -1], т.е. выбираются только авиакомпании,
     * у которых кол-во продаж снизилось на 100%, за вчера, относительно дня раньше
     * @return array
     */
    public function getAviaSalesNoSales()
    {
        return $this->getAviaSalesGrowthWCond([
            'DATE1' => [
                Date('Y-m-d', strtotime('-2 day')) . ' 00:00:00',
                Date('Y-m-d', strtotime('-2 day')) . ' 23:59:59'
            ],
            'DATE2' => [
                Date('Y-m-d', strtotime('-1 day')) . ' 00:00:00',
                Date('Y-m-d', strtotime('-1 day')) . ' 23:59:59'
            ],
            'SALES_GROWTH_CONDITION' => ['=', -1]
        ]);
    }
    /**
     * Возвращает коды авиакомпаний, которые продавались за текущий год, исключая дубликаты и чартеры,
     * а также кол-во проданных билетов каждой авиакомпанией
     * @return array
     */
    public function getAviaList()
    {
        $currentYear = Date('Y');
        $nextYear = Date('Y', strtotime('+1 year'));
        $query = $this->getAviaSalesQuery([
            'DATE1' => "{$currentYear}-00-00 00:00:00",
            'DATE2' => "{$nextYear}-00-00 00:00:00",
        ]);
        return $this->querySlave($query);
    }
    /**
     * Results data for testing all cases
     * @return array
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\db\Exception
     */
    public function test()
    {
        $data = [];
        // SALES_GROWTH -100% (before yesterday - yesterday)

        $params['DATE1'] = [
            Date('Y-m-d', strtotime('-2 day')) . ' 00:00:00',
            Date('Y-m-d', strtotime('-2 day')) . ' 23:59:59'
        ];
        $params['DATE2'] = [
            Date('Y-m-d', strtotime('-1 day')) . ' 00:00:00',
            Date('Y-m-d', strtotime('-1 day')) . ' 23:59:59'
        ];

        $params['SALES_GROWTH_CONDITION'] = ['=', -1];
        $key = "SALES_GROWTH_CONDITION {$params['SALES_GROWTH_CONDITION'][0]} {$params['SALES_GROWTH_CONDITION'][1]}";
        $query = $this->getAviaSalesGrowthQueryWCond([
            'DATE1' => $params['DATE1'],
            'DATE2' => $params['DATE2'],
            'SALES_GROWTH_CONDITION' => $params['SALES_GROWTH_CONDITION'],
        ]);
        $data[$key] = [
            'condition' => $params['SALES_GROWTH_CONDITION'],
            'date1' => $params['DATE1'],
            'date2' => $params['DATE2'],
            'table' => $this->querySlaveWSql($query),
        ];

        $hour = Date('H', strtotime('-1 hour'));
        $params['DATE1'] = [
            Date('Y-m-d', strtotime('-1 day')) . ' 00:00:00',
            Date('Y-m-d', strtotime('-1 day')) . " {$hour}:59:59"
        ];
        $params['DATE2'] = [
            Date('Y-m-d') . ' 00:00:00',
            Date('Y-m-d') . " {$hour}:59:59"
        ];

        // SALES_GROWTH -30% (yesterday - today)
        $params['SALES_GROWTH_CONDITION'] = ['<', -0.3];
        $key = "SALES_GROWTH_CONDITION {$params['SALES_GROWTH_CONDITION'][0]} {$params['SALES_GROWTH_CONDITION'][1]}";
        $query = $this->getAviaSalesGrowthQueryWCond([
            'DATE1' => $params['DATE1'],
            'DATE2' => $params['DATE2'],
            'SALES_GROWTH_CONDITION' => $params['SALES_GROWTH_CONDITION'],
        ]);
        $data[$key] = [
            'condition' => $params['SALES_GROWTH_CONDITION'],
            'date1' => $params['DATE1'],
            'date2' => $params['DATE2'],
            'table' => $this->querySlaveWSql($query),
        ];
        // SALES_GROWTH +30% (yesterday - today)
        $params['SALES_GROWTH_CONDITION'] = ['>', 0.3];
        $key = "SALES_GROWTH_CONDITION {$params['SALES_GROWTH_CONDITION'][0]} {$params['SALES_GROWTH_CONDITION'][1]}";
        $query = $this->getAviaSalesGrowthQueryWCond([
            'DATE1' => $params['DATE1'],
            'DATE2' => $params['DATE2'],
            'SALES_GROWTH_CONDITION' => $params['SALES_GROWTH_CONDITION'],
        ]);
        $data[$key] = [
            'condition' => $params['SALES_GROWTH_CONDITION'],
            'date1' => $params['DATE1'],
            'date2' => $params['DATE2'],
            'table' => $this->querySlaveWSql($query),
        ];
        // SALES_GROWTH 0% (yesterday - today)
        $params['SALES_GROWTH_CONDITION'] = ['=', 0];
        $key = "SALES_GROWTH_CONDITION {$params['SALES_GROWTH_CONDITION'][0]} {$params['SALES_GROWTH_CONDITION'][1]}";
        $query = $this->getAviaSalesGrowthQueryWCond([
            'DATE1' => $params['DATE1'],
            'DATE2' => $params['DATE2'],
            'SALES_GROWTH_CONDITION' => $params['SALES_GROWTH_CONDITION'],
        ]);
        $data[$key] = [
            'condition' => $params['SALES_GROWTH_CONDITION'],
            'date1' => $params['DATE1'],
            'date2' => $params['DATE2'],
            'table' => $this->querySlaveWSql($query),
        ];
        return $data;
    }

}

<?php
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $date1 array */
/* @var $date2 array */
/* @var $data array */

list($date1_[0], $time1[0]) = explode(' ', $date1[0]);
list($date1_[1], $time1[1]) = explode(' ', $date1[1]);
list($date2_[0], $time2[0]) = explode(' ', $date2[0]);
list($date2_[1], $time2[1]) = explode(' ', $date2[1]);
?>
    Статистика по отсутствию продаж авиакомпаний
    за <?= $date2[0] ?> - <?= $date2[1] ?> по сравнению с <?= $date1[0] ?> - <?= $date1[1] ?>
    Авиакомпания | Кол-во продаж за <?= $date1_[0] ?> <?= $time1[0] ?> - <?= $time1[1] ?> | Кол-во продаж за <?= $date2_[0] ?> <?= $time2[0] ?> - <?= $time2[1] ?> | Относительное изменение кол-ва продаж (%)
<?php foreach($data as $v) : ?>

    <?= $v['AK'] ?> | <?= $v['ORDER_COUNT_1'] ?> | <?= $v['ORDER_COUNT_2'] ?> | <?= $v['SALES_GROWTH_PERCENT'] * 100 ?>%
<?php endforeach; ?>
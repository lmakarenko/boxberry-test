<?php
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $data array */
?>
<?php foreach($data as $v) :
    $date1 = $v['date1'];
    $date2 = $v['date2'];
    list($date1_[0], $time1[0]) = explode(' ', $date1[0]);
    list($date1_[1], $time1[1]) = explode(' ', $date1[1]);
    list($date2_[0], $time2[0]) = explode(' ', $date2[0]);
    list($date2_[1], $time2[1]) = explode(' ', $date2[1]);
    ?>
    /****************************************************************************************/
    Статистика продаж за <?= $date2[0] ?> - <?= $date2[1] ?> по сравнению с <?= $date1[0] ?> - <?= $date1[1] ?>
    SALES_GROWTH_CONDITION <?= $v['condition'][0] ?> <?= $v['condition'][1] ?>
    Авиакомпания | Кол-во продаж за <?= $date1_[0] ?> <?= $time1[0] ?> - <?= $time1[1] ?> | Кол-во продаж за <?= $date2_[0] ?> <?= $time2[0] ?> - <?= $time2[1] ?> | Относительное изменение кол-ва продаж (%)
    <?php foreach($v['table'][0] as $row) : ?>
        <?= $row['AK'] ?> | <?= $row['ORDER_COUNT_1'] ?> | <?= $row['ORDER_COUNT_2'] ?> | <?= $row['SALES_GROWTH_PERCENT'] * 100 ?>%
    <?php endforeach; ?>
    ------------------------------------------------------------------------------------------
    <?= $v['table'][1] ?>
<?php endforeach; ?>

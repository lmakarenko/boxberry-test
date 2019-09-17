<?php
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $date1 array */
/* @var $date2 array */
/* @var $condition array */
/* @var $data array */

list($date1_[0], $time1[0]) = explode(' ', $date1[0]);
list($date1_[1], $time1[1]) = explode(' ', $date1[1]);
list($date2_[0], $time2[0]) = explode(' ', $date2[0]);
list($date2_[1], $time2[1]) = explode(' ', $date2[1]);
?>
<h1>Статистика по росту продаж авиакомпаний более чем на <?= $condition[1] * 100 ?>%</h1>
<pre>за <i><?= $date2[0] ?> - <?= $date2[1] ?></i> по сравнению с <i><?= $date1[0] ?> - <?= $date1[1] ?></i></pre>
<br/>
<table border="1" cellspacing="0">
    <thead>
    <tr>
        <th style="text-align:center;padding:5px;">Авиакомпания</th>
        <th style="text-align:center;padding:5px;">Кол-во продаж за <?= $date1_[0] ?><br/><?= $time1[0] ?> - <?= $time1[1] ?></th>
        <th style="text-align:center;padding:5px;">Кол-во продаж за <?= $date2_[0] ?><br/><?= $time2[0] ?> - <?= $time2[1] ?></th>
        <th style="text-align:center;padding:5px;">Относительное изменение кол-ва продаж (%)</th>
    </tr>
    </thead>
    <tbody>
    <?php foreach($data as $v) : ?>
        <tr>
            <td style="text-align:center;padding:5px;"><?= $v['AK'] ?></td>
            <td style="text-align:right;padding:5px;"><?= $v['ORDER_COUNT_1'] ?></td>
            <td style="text-align:right;padding:5px;"><?= $v['ORDER_COUNT_2'] ?></td>
            <td style="text-align:right;padding:5px;"><?= $v['SALES_GROWTH_PERCENT'] * 100 ?>%</td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
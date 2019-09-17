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
    <h1>Статистика продаж</h1>
    <pre>за <i><?= $date2[0] ?> - <?= $date2[1] ?></i> по сравнению с <i><?= $date1[0] ?> - <?= $date1[1] ?></i></pre>
    <pre>SALES_GROWTH_CONDITION <?= $v['condition'][0] ?> <?= $v['condition'][1] ?></pre>
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
        <?php foreach($v['table'][0] as $row) : ?>
            <tr>
                <td style="text-align:center;padding:5px;"><?= $row['AK'] ?></td>
                <td style="text-align:right;padding:5px;"><?= $row['ORDER_COUNT_1'] ?></td>
                <td style="text-align:right;padding:5px;"><?= $row['ORDER_COUNT_2'] ?></td>
                <td style="text-align:right;padding:5px;"><?= $row['SALES_GROWTH_PERCENT'] * 100 ?>%</td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <pre><?= $v['table'][1] ?></pre>
<?php endforeach; ?>
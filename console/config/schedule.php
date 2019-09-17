<?php
/**
 * @var \omnilight\scheduling\Schedule $schedule
 */

use \yii\console\Application;

// Place here all of your cron jobs

date_default_timezone_set('Europe/Moscow');

// Отсутствие продаж: 8:00
$schedule->call(function(Application $app) {
    $app->runAction('sales-monitor/no-sales');
})
    //->everyMinute()
    ->cron('0 8 * * *')
    ->description('sales-monitor-no-sales')
    ->withoutOverlapping();

// Снижений продаж ниже 30%: 11:00, 16:00, 20:00
$schedule->call(function(Application $app) {
    $app->runAction('sales-monitor/below30');
})
    ->cron('0 11,16,20 * * *')
    ->description('sales-monitor-below30')
    ->withoutOverlapping();

// Рост продаж свыше 30%: 11:00, 16:00, 20:00
$schedule->call(function(Application $app) {
    $app->runAction('sales-monitor/above30');
})
    ->cron('0 11,16,20 * * *')
    ->description('sales-monitor-above-30')
    ->withoutOverlapping();
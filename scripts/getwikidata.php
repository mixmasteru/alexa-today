<?php
setlocale(LC_ALL, 'de_DE.utf8');
require 'WikiExport.php';

$we = new WikiExport();

$arr_out = [];
$date_start = "1.1.2016";
$total = 366;

for ($i = 0;$i<$total;$i++) {

    $date_act = $date_start." + $i day";
    $date_str = date("j-n", strtotime($date_act)); //date-month
    var_dump($date_str);
    //$arr_out[$date_str] = $we->exportEvents($date_act);
    $arr_out = $we->exportEvents($date_act);
    $json = json_encode($arr_out, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE); // | JSON_FORCE_OBJECT);
    $str_js = 'module.exports = {"EVENTS_'.$date_str.'" : '.$json.'};';
    file_put_contents("data/de-DE_$date_str.js", $str_js);
}
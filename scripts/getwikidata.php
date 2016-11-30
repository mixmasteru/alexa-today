<?php
setlocale(LC_ALL, 'de_DE.utf8');
require 'WikiExport.php';

$we = new WikiExport();

$arr_out = [];
$date_start = "1.1.2016";
$total = 3;

for($i = 0;$i<$total;$i++){

    $date_act = $date_start." + $i day";
    $date_str = date("m-d",strtotime($date_act));
    var_dump($date_str);
    $arr_out[$date_str] = $we->exportEvents($date_act);
}

echo json_encode($arr_out,JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_FORCE_OBJECT);
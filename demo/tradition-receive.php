<?php

$start_time = microtime(true);
$result = array();
$result['wait-1'] = file_get_contents('http://localhost:10001/wait-1.php');
$result['wait-2'] = file_get_contents('http://localhost:10002/wait-2.php');
$result['wait-3'] = file_get_contents('http://localhost:10003/wait-3.php');
print_r($result);
$end_time = microtime(true);
echo 'Run time: ' . ($end_time - $start_time) . PHP_EOL;

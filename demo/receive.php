<?php

require __DIR__ . '/../src/ConcurrentRPC.php';

$start_time = microtime(true);
$ConcurrentRPC = new ConcurrentRPC;
$result = $ConcurrentRPC
    ->get('http://localhost:10001/wait-1.php', 'wait-1')
    ->get('http://localhost:10002/wait-2.php', 'wait-2')
    ->post('http://localhost:10003/wait-3.php', 'wait-3', array('hello' => 'world'))
    ->receive();
print_r($result);
$end_time = microtime(true);
echo 'Run time: ' . ($end_time - $start_time) . PHP_EOL;

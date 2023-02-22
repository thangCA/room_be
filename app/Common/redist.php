<?php
// Kết nối đến Redis server
$redis = new Redis();
$redis->connect('127.0.0.1', 6379);

?>

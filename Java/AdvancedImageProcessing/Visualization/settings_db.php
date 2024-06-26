<?php
include('./visdba0.php');  // backward compatibility

//127.0.0.1 has quicker response than localhost
//http://php.net/manual/en/mysqli.quickstart.connections.php
$host= $r;  // '127.0.0.1';
$user=$n;  // 'root'
$password=$p; // 'root';

$dbnames = array(
    'Config' => 'dcuUserCommConfig',
    'DB' => 'dcuUserCommDB',
    'Hist' => 'DcuHistRecDB_',
    'ArchHist' => 'ArchDcuHistRecDB_',
    'test' => 'testUCC',
);
/*
$user=array(
    'Config' => 'root',
    'DB' => 'root',
    'Hist' => 'root',
    'ArchHist' => 'root',
    'test' => 'root',
);

$password=array(
    'Config' => 'root',
    'DB' => 'root',
    'Hist' => 'root',
    'ArchHist' => 'root',
    'test' => 'root',
);

$host=array(
    'Config' => '127.0.0.1',
    'DB' => '127.0.0.1',
    'Hist' => '127.0.0.1',
    'ArchHist' => '127.0.0.1',
    'test' => '127.0.0.1',
);
*/
?>

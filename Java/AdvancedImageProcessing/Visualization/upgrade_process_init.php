<h1>Upgrade process_init</h1>
<?php
if(file_exists("proc_lock.zip")) die("Another upgrade in progress.");




$archive_file = "http://www.prosystemy.sk/visualization/process_init.zip";
echo ".....download.........";

$archive_res = fopen($archive_file, 'r');
if(!$archive_res) die("Couldn't access the zip file.");

$success = file_put_contents("proc_lock.zip", $archive_res, LOCK_EX);
if(!$success) die("Couldn't download the zip file.");

//no need to backup process_init
//$today = date("y_m_d", time());
//echo ".....create processbackup$today.zip.........";
//exec("tar --exclude='./backups' -cf ./backups/processbackup".$today.".zip ./ ");

echo ".....unzip.........";
exec('unzip -o proc_lock.zip');
unlink("proc_lock.zip");

echo "<br><br>process_init upgrade successful<br><br>";

echo "rename <strong>process_init</strong> to process";
?>
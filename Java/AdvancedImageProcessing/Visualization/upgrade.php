<h1>Upgrade visualization</h1>
<?php
if(file_exists("vis_lock.zip")) die("Another upgrade in progress.");

if(isset($_POST['version'])) $version = $_POST['version'];
else $version = "vis";

$archive_file = "http://www.prosystemy.sk/visualization/$version.zip";
echo ".....download.........";

$archive_res = fopen($archive_file, 'r');
if(!$archive_res) die("Couldn't access the zip file.");

$success = file_put_contents("vis_lock.zip", $archive_res, LOCK_EX);
if(!$success) die("Couldn't download the zip file.");

$today = date("y_m_d", time());
echo ".....create backup$today.zip.........";
exec("tar --exclude='./backups' -cf ./backups/backup".$today.".zip ./ ");

echo ".....unzip.........";
exec('unzip -o vis_lock.zip');
unlink("vis_lock.zip");

echo "<br><br>Upgrade successful ";
if($version=='vis') echo "to newest version";
else echo "to version $version";

echo "<br><br>Reload all open pages";
echo "<br><br>Go to <a href='/'>HOMEPAGE</a>";
?>
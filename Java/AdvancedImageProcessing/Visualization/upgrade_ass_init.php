<h1>Upgrade ass_init</h1>
<?php
if(file_exists("ass_lock.zip")) die("Another upgrade in progress.");

$archive_file = "http://www.prosystemy.sk/visualization/ass_init.zip";
echo ".....download.........";

$archive_res = fopen($archive_file, 'r');
if(!$archive_res) die("Couldn't access the zip file.");

$success = file_put_contents("ass_lock.zip", $archive_res, LOCK_EX);
if(!$success) die("Couldn't download the zip file.");

echo ".....unzip.........";
$zip = new ZipArchive;
$res = $zip->open('ass_lock.zip');
if ($res === TRUE) {
    $old = umask(2);
    $zip->extractTo('./');
    $old = umask($old);
    $zip->close();
} else {
  die("Couldn't open the zip file.");
}
unlink("ass_lock.zip");

echo "<br><br>ass_init upgrade successful<br><br>";

echo "rename <strong>ass_init</strong> to ass";
?>
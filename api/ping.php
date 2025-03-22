<?php
error_reporting(0);
$out = array();
$result = exec("ping -n 2 -w 250 172.31.69.95", $out);
$up = false;
foreach($out as $line){
    echo $line . "<br>";
    if(str_contains($line, "time=")) {
        $up = true;
        break;
    }
}
echo "<br>HOST IS " . ($up ? "up" : "down");
?>
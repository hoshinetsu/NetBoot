<?php
error_reporting(0);
$out = array();
$result = exec("ping -n 2 -w 250 172.16.6.6", $out);
$up = false;
echo "<stdout>";
foreach ($out as $line) {
    echo $line . "<br>";
    if (str_contains($line, "time=")) {
        $up = true;
        break;
    }
}
echo "</stdout><up>";
echo $up ? 1 : 0;
echo "</up>";
?>
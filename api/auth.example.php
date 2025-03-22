<?php
error_reporting(0);

function error($e)
{
    die("<err>" . $e . "</err>");
}

function result($r)
{
    die("<out>" . $r . "</out>");
}

if (empty($_GET['user']) or empty($_GET['pass']) or empty($_GET['q'])) {
    error("bad request");
}

$user = trim($_GET['user']);
$pass = trim($_GET['pass']);
$query = intval(trim($_GET['q']));

if (strcmp(strtolower($user), "me") != 0 or strcmp(strtolower($pass), "hackme") != 0) {
    error("auth failure");
}

function testUp()
{
    $out = array();
    $addr = "172.16.254.1";
    exec("ping -n 4 -w 100 $addr", $out);
    $up = 0;
    foreach ($out as $line) {
        #echo $line . "<br>";
        if (str_contains($line, "Reply from $addr: bytes=32 time")) {
            $up = 1;
            break;
        }
    }
    return $up;
}

function sendPacket()
{
    $out = array();
    exec("wol 50EBF62F842C", $out);
    $sent = 0;
    foreach ($out as $line) {
        if (str_contains($line, "packet sent successfully")) {
            $sent = 1;
            break;
        }
    }
    return $sent;
}

# commented out so they wont execute when this script would get requested with default credentials
switch ($query) {
    case 10:
        #result(testUp());
        break;
    case 11:
        #result(sendPacket());
        break;
    default:
        break;
}

error("unknown query");

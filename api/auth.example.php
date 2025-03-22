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

if (strcmp(strtolower($user), "me") != 0) {
    error("auth failure");
}

if (strcmp(strtolower($pass), "hackme") != 0) {
    error("auth failure");
}

function testUp()
{
    $out = array();
    exec("ping -n 2 -w 250 172.16.254.1", $out);
    $up = false;
    foreach ($out as $line) {
        echo $line . "<br>";
        if (str_contains($line, "time=")) {
            $up = true;
            break;
        }
    }
    return $up;
}

function sendPacket()
{
    $out = array();
    exec("wol 60a4b7c798a8", $out);
    $sent = false;
    foreach ($out as $line) {
        if (str_contains($line, "packet sent successfully")) {
            $sent = true;
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

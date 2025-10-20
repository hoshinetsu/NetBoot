<?php
/* dont respond with default errors */
error_reporting(0);

$response = new stdClass();
$response->version = 251020;
$response->code = 400;
$response->debug = [];

/* class for hermetization */
final class WakeAPI
{
    private static function respond()
    {
        global $response;
        http_response_code($response->code);
        header('Content-Type: application/json; charset=utf-8');
        die(json_encode($response));
    }

    private static function dbgFlag($debugFlag)
    {
        global $response;
        $response->debug[$debugFlag] = 1;
    }

    private static function httpCode($code, $debugFlag)
    {
        global $response;
        $response->code = $code;
        self::dbgFlag($debugFlag);
        self::respond();
    }

    private static function code200($debugFlag)
    {
        self::httpCode(200, $debugFlag);
    }

    private static function error($code, $text)
    {
        global $response;
        $response->error = $text;
        self::httpCode($code, "error");
    }

    private static function executeCommand($cmd, $cond)
    {
        $out = [];
        $cond = strtolower($cond);
        exec($cmd, $out);
        foreach ($out as $line) {
            if (str_contains(strtolower($line), $cond)) {
                return 1;
            }
        }
        return 0;
    }

    private static function testUp()
    {
        global $response;
        $addr = "172.31.69.185";
        if (stripos(PHP_OS, 'WIN') === 0) {
            $cmd = "ping -n 1 -w 100 $addr";
            $cond = "Reply from $addr: bytes=32 time";
        } else {
            $cmd = "ping -c 1 -W 1 $addr";
            $cond = "bytes from $addr";
        }
        $response->isUp = self::executeCommand($cmd, $cond);
        self::code200("testUp");
    }

    private static function wakeUp()
    {
        global $response;
        $cmd = "wakeonlan 50EBF62F842C 2>&1";
        $cond = "packet";
        $response->magicSent = self::executeCommand($cmd, $cond);
        self::code200("wakeUp");
    }

    private static function powerUp()
    {
        global $response;
        $url = "http://desk-pdu.muffin.ntt/rpc/Switch.Set?id=0&on=true";
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, 1);
        $answer = curl_exec($ch);
        if (curl_errno($ch)) {
            $response->debug["shelly"] = curl_error($ch);
        } else {
            $response->debug["shelly"] = $answer;
            if (curl_getinfo($ch, CURLINFO_HTTP_CODE) == 200) {
                $response->poweredUp = 1;
            }
        }
        curl_close($ch);
        self::code200("powerUp");
    }

    public static function mainLogic()
    {
        global $response;
        if (empty($_GET['user']) or empty($_GET['pass']) or empty($_GET['q'])) {
            self::httpCode(400, "emptyParams");
        }
        $user = strtolower(trim($_GET['user']));
        $pass = strtolower(trim($_GET['pass']));
        $response->type = intval(trim($_GET['q']));
        self::dbgFlag("parse");
        if ($user === "admin" && $pass === "wakey") {
            self::dbgFlag("authPass");
            switch ($response->type) {
                case 10:
                    self::testUp();
                    break;
                case 11:
                    self::wakeUp();
                    break;
                case 20:
                    self::powerUp();
                    break;
                default:
                    self::error(422, "unknown command");
                    break;
            }
        } else {
            self::dbgFlag("authFail");
            self::error(401, "authentication failure");
        }
    }
}

WakeAPI::mainLogic();

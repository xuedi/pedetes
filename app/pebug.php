<?php
namespace Pedetes;

use Exception;

class pebug {

    private $msgStart;
    private $msgLogs = array();
    private $timer = array();
    private $timerLog = array();

    function __construct($ctn) {
        $this->msgStart = $ctn['startTime'];
        $this->log("pebug start: ".date("H:i:s", $this->msgStart), $this->msgStart);
        $this->timer_start("pebug", $this->msgStart);
        $this->timer_stop("pebug");
    }

    public function timer_start($timer_id, $time=null) {
        if(is_null($time)) $time = microtime(true);
        $this->timer[$timer_id] = $time;
    }

    public function timer_stop($timer_id) {
        $time = microtime(true) - $this->timer[$timer_id];
        $this->timerLog[$timer_id] = array(
            "time" => $time,
            "name" => $timer_id);
    }

    public function log($message, $time=null) {
        if(is_null($time)) $time = microtime(true);
        $this->msgLogs[] = array(
            "msg" => $message,
            "time" => $time - $this->msgStart);
    }

    public function debug($message) {
        $message = "<span style='color: blue; '><b>DEBUG: </b>$message</span>";
        $this->msgLogs[] = array(
            "msg" => $message,
            "time" => microtime(true) - $this->msgStart);
    }

    public function error($message) {
        $message = "<span style='color: red; '><b>ERROR: </b></span>$message";
        $this->msgLogs[] = array(
            "msg" => $message,
            "time" => microtime(true) - $this->msgStart);
        echo $this->report();
        exit();
    }

    public function exception($message) {
        throw new Exception($message);
    }

    public function report() {
        $retval  = "<div id='debug_logs' class='pebugWindows'>";
        $retval .= "<table>";
        foreach ($this->msgLogs as $value) {
            $msg = $value['msg'];
            $time = sprintf('%3.4f',$value['time']);
            $retval .= "<tr><td>$time</td><td>$msg</td></tr>";
        }
        $retval .="</table>";
        $retval .="</div>";

        $retval .= "<div id='debug_time' class='pebugWindows'>";
        $retval .= "<table>";
        sort($this->timerLog);
        foreach ($this->timerLog as $value) {
            $name = $value['name'];
            $time = sprintf('%3.4f',$value['time']);
            $retval .= "<tr><td align='right'>$name</td><td>$time</td>";
        }
        $retval .="</table>";
        $retval .="</div>";

        return $retval;
    }

}

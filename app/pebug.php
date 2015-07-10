<?php
namespace Pedetes;

class pebug {

    var $msgStart;
    var $msgLogs = array();

    var $timer = array();
    var $timerLog = array();

    public static function Instance() {
        static $inst = null;
        if($inst === null) $inst = new pebug();
        return $inst;
    }

    function init($start) {
        $this->msgStart = $start;
        $this->log("pebug start: ".date("H:i:s", $start), $start);
        $this->log("debug::init()");
        $this->timer_start("pebug", $start);
        $this->timer_stop("pebug");
    }

    private function __construct() {
        // you cannot touch it!
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
        $message = "<font color=blue><b>DEBUG: </b>$message</font>";
        $this->msgLogs[] = array(
            "msg" => $message,
            "time" => microtime(true) - $this->msgStart);
    }

    public function error($message) {
        $message = "<font color=red><b>ERROR: </b></font>$message";
        $this->msgLogs[] = array(
            "msg" => $message,
            "time" => microtime(true) - $this->msgStart);
        echo $this->report();
        exit();
    }

    public function report() {
        $retval  = "<div id='debug_logs'>";
        $retval .= "<table>";
        foreach ($this->msgLogs as $value) {
            $msg = $value['msg'];
            $time = sprintf('%3.4f',$value['time']);
            $retval .= "<tr><td>$time</td><td>$msg</td></tr>";
        }
        $retval .="</table>";
        $retval .="</div>";


        sort($this->timerLog);


        $retval .= "<div id='debug_time'>";
        $retval .= "<table>";
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

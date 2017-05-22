<?php
namespace Pedetes;


use DebugBar\StandardDebugBar;

class pebug {

    private $msgStart;
    private $msgLogs = array();
    private $timer = array();
    private $timerLog = array();

    /** @var StandardDebugBar $debugBar */
    private $debugBar;


    /**
     * pebug constructor.
     * @param float $startTime
     */
    function __construct(float $startTime) {
        $this->msgStart = $startTime;
        $this->log("pebug start: ".date("H:i:s", $this->msgStart), $this->msgStart);
        $this->timer_start("autoload", $this->msgStart);
        $this->timer_stop("autoload");

        $this->debugBar = new StandardDebugBar();
    }


    /**
     * Starts a named timer
     * @param string $timer_id The timer id as string for ex.: 'saveUser'
     * @param float|null $time
     */
    public function timer_start(string $timer_id, float $time=null) {
        if(is_null($time)) $time = microtime(true);
        $this->timer[$timer_id] = $time;
    }


    /**
     * Stops a timer and records the passed time
     * @param string $timer_id The timer id as string for ex.: 'saveUser'
     */
    public function timer_stop(string $timer_id) {
        $time = microtime(true) - $this->timer[$timer_id];
        $this->timerLog[$timer_id] = array(
            "time" => $time,
            "name" => $timer_id);
    }


    /**
     * Records a standart log message
     * @param string $message
     * @param float|null $time
     */
    public function log(string $message, float $time=null) {
        if(is_null($time)) $time = microtime(true);
        $this->msgLogs[] = array(
            "msg" => $message,
            "time" => $time - $this->msgStart);
    }


    /**
     * Records a debug message
     * @param string $message
     */
    public function debug(string $message) {
        $message = "<span style='color: blue; '><b>DEBUG: </b>$message</span>";
        $this->msgLogs[] = array(
            "msg" => $message,
            "time" => microtime(true) - $this->msgStart);
    }


    /**
     * Exits the app with an error
     * @param string $message
     */
    public function error(string $message) {
        $message = "<span style='color: red; '><b>ERROR: </b></span>$message";
        $this->msgLogs[] = array(
            "msg" => $message,
            "time" => microtime(true) - $this->msgStart);
        echo $this->report();
        exit();
    }


    /**
     * Generates the debugging bar for display
     * @return string
     */
    public function report() {
//        $renderer = $this->debugBar->getJavascriptRenderer();
//        return $renderer->render();



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

    /**
     * Generates the debugging bar for display
     * @return string
     */
    public function reportHead() {
        $renderer = $this->debugBar->getJavascriptRenderer();
        return $renderer->renderHead();
    }

}

<?php
namespace Pedetes;

class view extends smarty_i18n {

    var $pebug;

    function __construct($ctn) {
        parent::__construct($ctn);
        $this->pebug = $ctn['pebug'];
        $this->pebug->log( "view::__construct()" );
    }

    public function render( $name, $skipLayout=false, $cache=true ) {

        $base = $this->ctn['pathApp'];
        $view = $this->ctn['config']['path']['view'];

        // check if file exists
        if(!file_exists($base.$view.$name)) 
            $this->pebug->error( "view::render($name): File does not exist!" );

        // unique caller ID
        $this->assign("uniqueCaller", get_called_class());

        // render with all global vars
        if ( $skipLayout == true ) $this->displayML( $name, $cache );
        else {

            // check if file exists
            if(!file_exists($base.$view."layout/main.tpl")) 
                $this->pebug->error( "view::render(layout/main.tpl): File does not exist!" );

            // select content to be renderd into the layout
            $this->assign( "tplContent", $name );

            // display the whole page
            $this->displayML( "layout/main.tpl", $cache );

        }

        // debug summary!
        $this->pebug->log( "view::render()" );
        if($this->ctn['config']['console']) {
            echo $this->pebug->report();
        }
        echo "</body></html>";
        
    }

}

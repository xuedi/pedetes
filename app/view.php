<?php
namespace Pedetes;

use Exception;
use Pedetes\core\core_i18n_model;
use Pimple\Container;
use Twig_Environment;
use Twig_Loader_Filesystem;

class view {

    private $pebug;
    private $cache;
    private $configData;
    private $language;

    /** @var Twig_Environment $twig */
    private $twig;

    /** @var array $variables */
    private $variables;


    function __construct(pebug $pebug, cache $cache, config $config, string $language, string $pathApp) {
        $this->pebug = $pebug;
        $this->pebug->log( "view::__construct()" );

        $this->cache = $cache;
        $this->configData = $config->getData();
        $this->language = $language;

        $view = $this->configData['path']['view'];
        $temp = $this->configData['path']['temp'];

        $twigOptions = ['debug' => false, 'cache' => $pathApp.$temp];
        if(!$this->configData['caching'] ?? false) {
            $twigOptions['cache'] = false;
            $twigOptions['debug'] = true;
        }

        $loader = new Twig_Loader_Filesystem(($pathApp.$view));
        $this->twig = new Twig_Environment($loader, $twigOptions);

        $this->variables = [];
    }



    public function assign($nameOrArray, $valueIfName = null) : bool {
        if(empty($nameOrArray)) {
            return true;
        }
        if(is_array($nameOrArray)) {
            $this->variables = array_merge($this->variables, $nameOrArray);
            return true;
        }
        if(is_string($nameOrArray) && !empty($valueIfName)) {
            $this->variables[$nameOrArray] = $valueIfName;
            return true;
        }
        return false;
    }

    /**
     * @param $twigTemplate
     */
    public function render($twigTemplate) {
        $this->pebug->log( "view::render()" );


        // render
        $this->pebug->timer_start("render");
        try {
            $output = $this->twig->render($twigTemplate, $this->variables);
        } catch(Exception $e) {
            $this->pebug->error("view::render(): Error: ".$e->getMessage());
        }
        $this->pebug->timer_stop("render");


        // translate
        $this->pebug->timer_start("i18n");
        if(!$this->cache->exist('translations')) {
            $this->pebug->error("view::render(): Could not find language cache");
        } else $filter = $this->cache->get('translations');
        $lang = $this->language;
        if(isset($filter[$lang]['key'])&&isset($filter[$lang]['value'])) {
            $output = str_replace($filter[$lang]['key'], $filter[$lang]['value'], $output);
        }
        $this->pebug->timer_stop("i18n");


        // display
        $this->pebug->log( "view::display()" );
        if($this->configData['console']) {
            $output = str_replace('#pebug_bottom#',$this->pebug->report(), $output);
            $output = str_replace('#pebug_header#',$this->pebug->reportHeader(), $output);
        }
        echo $output;

    }

}

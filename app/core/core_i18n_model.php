<?php
namespace Pedetes\core;

use \PDO;
use Pedetes\cache;
use Pimple\Container;

class core_i18n_model extends \Pedetes\model {

    /** @var array */
    private $config = [];

    /** @var array */
    private $buffer = [];

    /** @var cache */
    var $cache;

	function __construct(Container $ctn) {
		parent::__construct($ctn);
        $this->cache = $ctn['cache'];
        $this->config = $ctn['config']->getData();
		$this->pebug->log( "core_i18n_model::__construct()" );
	}

	public function getLanguages($lang) {
		if(empty($lang)) $this->pebug->error( "core_i18n_model::getLanguages($lang): No language parameter given!" );
		$sql = "SELECT l.language, lt.name, lt.native FROM languages l
				LEFT JOIN languages_translation lt ON l.language = lt.translation
				WHERE lt.language = :language
				  AND l.enabled = 1";
		return $this->db->select($sql, array('language' => $lang));
	}

	public function getAvailableLanguages() {
		$tmp = $this->db->select("SELECT language FROM languages WHERE enabled = 1");
		return $this->db->filterField($tmp,'language');
	}

	public function getTranslationList($language = 'en') {
		$this->pebug->log( "layout_model::getTranslationList($language)" );
		$retVal = array();

		$sql = "SELECT i18n.*, i18n_translation.translation as translation 
				FROM i18n 
				LEFT JOIN i18n_translation 
				ON i18n.ukey = i18n_translation.ukey 
				AND i18n_translation.language = :language 
				ORDER BY file,ukey ";

		//TODO: replace by: $result = $this->db->selectOne($sql, array('language' => $language) );
		$result = $this->db->select($sql, array('language' => $language) );

		// base data
		foreach($result as $row) {
			$file = $row['file'];
			if($file!="") {
				$retVal[$file]['data'][] = array("ukey" => $row['ukey'],
												 "line" => $row['line'],
												 "translation" => $row['translation']);
			}
		} 

		// stats
		foreach($retVal as $key => $value) {
			foreach($value['data'] as $subKey => $subValue) {
				if(!isset($retVal[$key]['set'])) $retVal[$key]['set'] = 0;
				if(!isset($retVal[$key]['count'])) $retVal[$key]['count'] = 0;
				if($subValue['translation']!="") $retVal[$key]['set']++;
				$retVal[$key]['count']++;
			}
		}

		// get the percentages
		foreach($retVal as $key => $value) {
			$retVal[$key]['p_green'] = ceil($value['set'] / $value['count'] * 100);
			$retVal[$key]['p_red'] = 100 - $retVal[$key]['p_green'];
		}
		
		return $retVal;
	}


	public function setTranslation($lang, $key, $value) {
		$value = str_replace("'", "&#39;", $value);
		$sth = "REPLACE INTO i18n_translation (ukey, language, translation) VALUES ('$key', '$lang', '$value'); ";
		$this->db->select($sth, null, PDO::FETCH_COLUMN);
	}


	public function getTranslations() {
		$retVal = array();
        $result = $this->db->select("SELECT ukey, language, translation FROM i18n_translation ");
		foreach($result as $row) {
			$lang = $row['language'];
			$retVal[$lang]['key'][] = "##".$row['ukey']."##";
			$retVal[$lang]['value'][] = nl2br($row['translation']);
		} 
		return $retVal;
	}


	public function publish() {
        $list = $this->getTranslations();
        $this->cache->set('translations', $list);
	}


	public function search() {
		$this->potClear();
		$base = $this->ctn['pathApp'];
        $path = $this->config['path']['view'];
		$fileList = $this->getTemplateFiles($path);
		foreach($fileList as $value) {
			if(substr($value, -4)=="twig") {
				if(file_exists($base.$value)) {
                    $lines = file($base.$value);
					$lineCount=0;
					foreach($lines as $line) {
						$match = "";
						$lineCount++;
						preg_match_all('/##(.+?)##/', $line, $match);
						if(!empty($match[1])) {
							foreach($match[1] as $string) {
								if($string!="") {
									if(strlen($string)<=128) {
										$this->potAdd($string, $value, $lineCount);
									}
								}
							}
						}
					}
				} else echo "File does not exist: $value<br />";
			}
		}
		$this->potSave();
		//$this->_i18n_generate_cache();
	}


	public function saveForm($lang) {
		if($lang!="") {
			foreach($_REQUEST as $key => $value) {
				if(substr($key,0,5)=="ukey_" && $value != "") {
					$key = substr($key,5);
					$this->setTranslation($lang, $key, $value);
				}
			}
		}
	}

	// search for unused translations and clean them out of the DB
	public function clean() {
		$this->search();
        $this->db->select("DELETE FROM i18n_translation WHERE ukey NOT IN (SELECT ukey FROM i18n)");
		$this->publish();
	}


/********/



	private function getTemplateFiles($folder) {
		$base = $this->ctn['pathApp'];
		if(substr($folder, -1) == '/') 
			$folder = substr($folder, 0, -1);
		$return = array();
		$handle = opendir($base.$folder);
		while(false !== ($file = readdir($handle))) {
			if($file != "." && $file != "..") {
				$filePath = $folder."/".$file;
				if(is_dir($base.$filePath)) $return = array_merge($return, $this->getTemplateFiles($filePath));
				else $return[] = $filePath;
			}
		}
		closedir($handle);
		return $return;
	}


	public function potClear() {
		$sth = "TRUNCATE TABLE i18n";
		$this->db->select($sth, null, PDO::FETCH_COLUMN);
		return;
	}


	public function potAdd($ukey, $file, $line) {
		$this->buffer[] = array('ukey' => $ukey, 'file' => $file, 'line' => $line);
	}


	public function potSave() {
		$flag = 0;
		$sth = "INSERT IGNORE INTO i18n (ukey, file, line) VALUES ";
		foreach ($this->buffer as $value) {
			$ukey = $value['ukey'];
			$file = $value['file'];
			$line = $value['line'];
			$sth .= "('$ukey', '$file', '$line'), ";
			$flag = 1;
		}
		if($flag) {
			$sth = substr($sth, 0, strlen($sth)-2);
			$this->db->select($sth, null, PDO::FETCH_COLUMN);
		}
	}

}

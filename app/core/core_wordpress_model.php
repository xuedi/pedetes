<?php
namespace Pedetes\core;

use \PDO;

class core_wordpress_model extends \Pedetes\model {

	var $table;
	var $fields;
	private $wp = null;

	function __construct($ctn) {
		parent::__construct($ctn);
        $this->pebug->log("core_wordpress_model::__construct()");

        $this->wp = $ctn['config']['wordpress']."wp-blog-header.php";
        if(!file_exists($this->wp)) $this->pebug->error( "core_wordpress_model::__construct(): WP not found!" );
	}


	public function _getContent($category=null, $url=null) {
		require($this->wp);

		if($category) $id = $this->_getPostTop($category);
		else $id = $this->_getPostByUrl($url);

		if($id) {
			$wp_posts = get_posts($id);
			foreach($wp_posts as $wp_post) {
				if($wp_post->ID==$id) {
					$retVal = array();
					$retVal['url'] = $wp_post->post_name;
					$retVal['title'] = $wp_post->post_title;
					$retVal['content'] = $this->_wpMod($wp_post->post_content);
					$retVal['links'] = $this->_getLinks($id);
					return $retVal;		
				}
			}
		}
	}


	// get top entry out of the category
	private function _getPostTop($category) {
		if(empty($category)) $this->_trough404('_getPostTop: No category set');
		require($this->wp);
		$cid = get_cat_ID($category);
		if($cid) {
			$wp_posts = get_posts(array('category'=>get_cat_ID($category)));
			foreach($wp_posts as $wp_post) {
				$id = $wp_post->ID;
				$tags = wp_get_post_tags($wp_post->ID);
				foreach($tags as $tag) {
					if($tag->name=='top') return $id;
				}
			}
			if(!empty($id)) return $id; // Backup, random one
		}
		$this->_trough404('_getPostTop: No category found');
	}


	// loads specific post by url
	private function _getPostByUrl($url) {
		require($this->wp);
		$query = array('name'=>$url,'post_type'=>'post','post_status'=>'publish','posts_per_page'=>1);
		$posts = get_posts($query);
		if(!empty($posts)) return $posts[0]->ID;
		else return 0;
	}


	// get links for posts from the same category
	private function _getLinks($id) {
		require($this->wp);
		$retVal = array();
		$posts = get_posts(array('category__in' => wp_get_post_categories($id)));
		foreach( $posts as $post ) {
			setup_postdata($post);
			$retVal[] = array(
				'id' => $post->ID,
				'date' => $post->post_date,
				'link' => $post->post_name,
				'title' => $post->post_title,
				'active' => ($post->ID == $id) ? 1 : 0
				);
		}
		return $retVal;
	}


	// add structure elements
	private function _wpMod($post) {
		return '<p>'.str_replace("\n", '</p><p>', $post).'</p>';
	}


	// should be pdebug
	private function _trough404($msg=null) {
		dbg('Blog: 404 --> '.$msg,true);
	}




}

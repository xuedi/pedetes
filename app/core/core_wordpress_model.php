<?php
namespace Pedetes\core;

use Pedetes\config;

class core_wordpress_model extends \Pedetes\model {

    /** @var config $config */
	private $config = null;
	private $wp = null;

	function __construct($ctn) {
		parent::__construct($ctn);
		$this->pebug->log("core_wordpress_model::__construct()");
        $this->config = $ctn['config'];

		$this->wp = $this->config->getData()['wordpress']."wp-load.php";
		if(!file_exists($this->wp)) {
			$this->pebug->error( "core_wordpress_model::__construct(): WP not found! [".$this->wp."]" );
		}

        $this->pebug->timer_start("wordpress");
		require($this->wp);
        $this->pebug->timer_stop("wordpress");
	}


	public function getCategoryTree() {
		$tree = array();
		$list = array();

		$args = array(
			'type' => 'post',
			'child_of' => 0,
			'orderby' => 'name',
			'order' => 'ASC',
			'hide_empty' => 0,
			'hierarchical' => 1,
			'taxonomy' => 'category',
			'pad_counts' => false 
			);

		$data = get_categories($args);
		foreach($data as $value) {
			$list[] = array(
				'id' => $value->term_id,
				'name' => $value->name,
				'slug' => $value->slug,
				'parent' => $value->category_parent,
				'children' => [],
				);
		}

		$tree = $this->_hierarchical($list);

		return $tree;
	}


	public function _getContent($url=null) {
		if($url[1]=='category') {
			$id = $this->_getPostByCategory($url[2]);
		} else {
			$id = $this->_getPostByUrl($url['full']);
		}

		if($id) {
			$this->pebug->log( "core_wordpress_model::_getContent($id)" );
			$wp_post = get_post($id);
			$retVal = array();
			$retVal['url'] = $wp_post->post_name;
			$retVal['title'] = $wp_post->post_title;
			$retVal['content'] = $this->_wpMod($wp_post->post_content);
			$retVal['links'] = $this->_getLinks($id);
			$retVal['cloud'] = $this->_getTagCould();
			$retVal['author'] = $this->_getAuthorDetails($wp_post->post_author);
			return $retVal;		
		}
		return null;
	}







	// get top entry out of the category
	private function _getPostByCategory($category) {
		$cid = get_category_by_slug($category)->term_id; // ?? update to PHP 7.1
		if(!isset($cid)) $cid = false;
		if($cid) {
			$query = array(
				'numberposts' => 1000,
				'category' => $cid,
				'post_status' => 'publish'
			);
			$wp_posts = get_posts($query);
			foreach($wp_posts as $wp_post) {
				$id = $wp_post->ID;
				$tags = wp_get_post_tags($wp_post->ID);
				foreach($tags as $tag) {
					if($tag->name=='top') return $id; // Prefer top
				}
			}
			if(!empty($id)) return $id; // Backup, random one
		}
		return null;
	}


	// loads specific post by url
	private function _getPostByUrl($url) {
		$query = array('name'=>$url,'post_type'=>'post','post_status'=>'publish','posts_per_page'=>1);
		$posts = get_posts($query);
		if(!empty($posts)) return $posts[0]->ID;
		return null;
	}









	private function _getAuthorDetails($user_id) {
		$retVal = array();
		$retVal['url'] = get_the_author_meta('url', $user_id);
		$retVal['desc'] = get_the_author_meta('description', $user_id);
		$retVal['name'] = get_the_author_meta('nicename', $user_id);
		$retVal['email'] = get_the_author_meta('email', $user_id);
		$retVal['avatar'] = get_avatar($user_id, 80);
		return $retVal;
	}


	private function _getLinks($id) {
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
		if(count($retVal)<=1) {
			return null;
		}
		return $retVal;
	}


	private function _getTagCould() {
		$retVal = array();
		$args = array(
			'smallest'                  => 12, 
			'largest'                   => 18,
			'unit'                      => 'pt', 
			'number'                    => 45,  
			'format'                    => 'array',
			'separator'                 => "\n",
			'orderby'                   => 'name', 
			'order'                     => 'ASC',
			'exclude'                   => null, 
			'include'                   => null, 
			'topic_count_text_callback' => default_topic_count_text,
			'link'                      => 'view', 
			'taxonomy'                  => 'post_tag', 
			'echo'                      => true,
			'child_of'                  => null, // see Note!
		);
		$tags = wp_tag_cloud($args);
		if($tags) {
			foreach($tags as $value) {
				$retVal[] = $value;
			}
			return implode(' ', $retVal);
		}
		return null;
	}


	private function _wpMod($post) {
		return '<p>'.str_replace("\n", '</p><p>', $post).'</p>';
	}


	private function _hierarchical(array $elements, $parentId = 0) {
		$branch = array();
		foreach($elements as $element) {
			if ($element['parent'] == $parentId) {
				$children = $this->_hierarchical($elements, $element['id']);
				if($children) {
					$element['children_count'] = count($children);
					$element['children'] = $children;
				}
				$branch[$element['slug']] = $element;
			}
		}
		return $branch;
	}


}

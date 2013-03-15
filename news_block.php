<?php
require_once 'simplepie.inc';

class NewsBlock {
	
	public $feed; // simpiepie objects
	public $num_stories;
	private $title;
	private $link;
	private $stories;
	private $cfg;
	private $urls;

	function __construct($cfg) {
		$this->cfg = $cfg;
		$this->urls = array();
		$this->num_stories = isset($cfg['num_stories']) ? $cfg['num_stories'] : 1;
		$this->cfg['heading'] = isset($cfg['heading']) ? $cfg['heading'] : true;
		$this->feed = new SimplePie();

		$this->feed->strip_attributes(array_diff($this->feed->strip_attributes, array('class', 'id')));

		if (isset($cfg['url'])) {
			$this->set_feed_url($cfg['url']);
		} else {
			$this->set_feed_url($this->determine_feed($_SERVER['REQUEST_URI']));
		}
		$this->feed->enable_order_by_date(false);
		$cache_location = isset($cfg['cache_location']) ? $cfg['cache_location'] : '/pass/services/www/dept/iit/hbg/Includes/cache';
		$this->feed->set_cache_duration(240);
		$this->feed->set_cache_location($cache_location);
		
		$this->feed->init();

		$this->title = isset($cfg['title']) ? $cfg['title'] : $this->feed->get_title();
		$this->link = isset($cfg['more_link']) ? $cfg['more_link'] : $this->feed->get_link();
		$this->stories = $this->feed->get_items(0, $this->num_stories);
		if (count($this->stories) == 0) {
			echo '<!-- Warning: Unable to retrieve any stories. -->';
		}
	}
	
	//wraps simpiepie method
	public function set_feed_url($feed) {
		$this->urls[] = $feed;
		$this->feed->set_feed_url($feed);
	}

    //TODO: Making this a template would be cool
	public function render() {
		echo "<!-- num_stories: ". $this->num_stories. " -->";
		if (count($this->stories) > 0) {
			echo '<div class="newsblock">' .
				($this->cfg['heading'] ? '<h3>' .  
				htmlentities(ucfirst(html_entity_decode($this->title, ENT_QUOTES)), ENT_QUOTES, 'UTF-8') . 
				'</h3>' : '') .
				'<div class="newsblock-inner">' . $this->get_news_list();
			if ($this->link !== false) {
				echo '<a class="more" style="float:left;" href="' . $this->link . '">&#8658; '. 
					(isset($this->cfg['more_title']) ? $this->cfg['more_title'] : 'More ' . $this->title). 
					' &#8658;</a>';
			}
			echo '<div style="clear:both;"></div></div></div>';
		}
	}
	
	private function get_news_list() {
		$news_list = '<ul class="'. $this->get_css_classes() .'">';
		foreach ($this->stories as $item):
			$news_list .= '<li><h4><a class="headline" href="' . $item->get_permalink() . 
					 '">' . htmlentities(html_entity_decode($item->get_title(), ENT_QUOTES), ENT_QUOTES, 'UTF-8') . '</a></h4>' .
					 '<span class="pubdate">' . $item->get_date('M j Y') . '</span>' . 
					 $item->get_description() . '</li>';
		endforeach;
		$news_list .= '</ul>';

		return $news_list;
	}

	function get_css_classes()
	{
		$css = '';
		$tmp = parse_url($this->urls[0]);
		$tmp = explode('/', $tmp['path']);
		$tmp = $tmp[1];
		switch ($tmp) {
			case 'calendar':
				$css .= 'events ';
				break;
			default:
				$css .= 'news ';
		}
		return trim($css);
	}

    //TODO: Making this loaded from a external file would be nice
	function determine_feed($from_where) {
		switch ($from_where) {
			case '/about/rsstest.php':
			case '/dept/iit/cl/spa/rsstest2.php':
			case '/dept/iit/cl/spa/index.php':
			case '/dept/iit/cl/spa/':	
				$url = 'http://harrisburg.psu.edu/news/feed/public-affairs';
				break;
			case '/dept/iit/cl/sba/':
			case '/dept/iit/cl/sba/index.php':
				$url = 'http://harrisburg.psu.edu/news/feed/business-administration';
				break;
			case '/dept/iit/cl/hum/index.php':
			case '/dept/iit/cl/hum/':
				$url = 'http://harrisburg.psu.edu/news/feed/humanities';
				break;
			case '/dept/iit/cl/set/index.php':
			case '/dept/iit/cl/set/':
				$url = 'http://harrisburg.psu.edu/news/feed/science-engineering-technology';
				break;
			default:
				$url = 'http://harrisburg.psu.edu/news/feed/all';
				break;
		}
		return $url;
	}

}
?>


<?php

/**
 * Contains the description of a user query
 *
 * It allows to extract the meaningful bits of the query to be manipulated in an
 * easy way.
 */
class FreshRSS_UserQuery {

	private $deprecated = false;
	private $get;
	private $get_name;
	private $get_type;
	private $name;
	private $order;
	/** @var FreshRSS_BooleanSearch */
	private $search;
	private $state;
	private $url;
	/** @var FreshRSS_FeedDAO */
	private $feed_dao;
	/** @var FreshRSS_CategoryDAO */
	private $category_dao;
	/** @var FreshRSS_TagDAO */
	private $tag_dao;

	/**
	 * @param array<string,string> $query
	 */
	public function __construct($query, FreshRSS_FeedDAO $feed_dao = null, FreshRSS_CategoryDAO $category_dao = null, FreshRSS_TagDAO $tag_dao = null) {
		$this->category_dao = $category_dao;
		$this->feed_dao = $feed_dao;
		$this->tag_dao = $tag_dao;
		if (isset($query['get'])) {
			$this->parseGet($query['get']);
		}
		if (isset($query['name'])) {
			$this->name = trim($query['name']);
		}
		if (isset($query['order'])) {
			$this->order = $query['order'];
		}
		if (empty($query['url'])) {
			if (!empty($query)) {
				unset($query['name']);
				$this->url = Minz_Url::display(['params' => $query]);
			}
		} else {
			$this->url = $query['url'];
		}
		if (!isset($query['search'])) {
			$query['search'] = '';
		}
		// linked too deeply with the search object, need to use dependency injection
		$this->search = new FreshRSS_BooleanSearch($query['search']);
		if (isset($query['state'])) {
			$this->state = $query['state'];
		}
	}

	/**
	 * Convert the current object to an array.
	 *
	 * @return array<string,string>
	 */
	public function toArray() {
		return array_filter(array(
			'get' => $this->get,
			'name' => $this->name,
			'order' => $this->order,
			'search' => $this->search->__toString(),
			'state' => $this->state,
			'url' => $this->url,
		));
	}

	/**
	 * Parse the get parameter in the query string to extract its name and
	 * type
	 *
	 * @param string $get
	 */
	private function parseGet($get) {
		$this->get = $get;
		if (preg_match('/(?P<type>[acfst])(_(?P<id>\d+))?/', $get, $matches)) {
			$id = intval($matches['id'] ?? '0');
			switch ($matches['type']) {
				case 'a':
					$this->parseAll();
					break;
				case 'c':
					$this->parseCategory($id);
					break;
				case 'f':
					$this->parseFeed($id);
					break;
				case 's':
					$this->parseFavorite();
					break;
				case 't':
					$this->parseTag($id);
					break;
			}
		}
	}

	/**
	 * Parse the query string when it is an "all" query
	 */
	private function parseAll() {
		$this->get_name = 'all';
		$this->get_type = 'all';
	}

	/**
	 * Parse the query string when it is a "category" query
	 *
	 * @throws FreshRSS_DAO_Exception
	 */
	private function parseCategory(int $id) {
		if ($this->category_dao === null) {
			throw new FreshRSS_DAO_Exception('Category DAO is not loaded in UserQuery');
		}
		$category = $this->category_dao->searchById($id);
		if ($category) {
			$this->get_name = $category->name();
		} else {
			$this->deprecated = true;
		}
		$this->get_type = 'category';
	}

	/**
	 * Parse the query string when it is a "feed" query
	 *
	 * @throws FreshRSS_DAO_Exception
	 */
	private function parseFeed(int $id) {
		if ($this->feed_dao === null) {
			throw new FreshRSS_DAO_Exception('Feed DAO is not loaded in UserQuery');
		}
		$feed = $this->feed_dao->searchById($id);
		if ($feed) {
			$this->get_name = $feed->name();
		} else {
			$this->deprecated = true;
		}
		$this->get_type = 'feed';
	}

	/**
	 * Parse the query string when it is a "tag" query
	 *
	 * @throws FreshRSS_DAO_Exception
	 */
	private function parseTag(int $id) {
		if ($this->tag_dao == null) {
			throw new FreshRSS_DAO_Exception('Tag DAO is not loaded in UserQuery');
		}
		$tag = $this->tag_dao->searchById($id);
		if ($tag) {
			$this->get_name = $tag->name();
		} else {
			$this->deprecated = true;
		}
		$this->get_type = 'tag';
	}

	/**
	 * Parse the query string when it is a "favorite" query
	 */
	private function parseFavorite() {
		$this->get_name = 'favorite';
		$this->get_type = 'favorite';
	}

	/**
	 * Check if the current user query is deprecated.
	 * It is deprecated if the category or the feed used in the query are
	 * not existing.
	 *
	 * @return boolean
	 */
	public function isDeprecated() {
		return $this->deprecated;
	}

	/**
	 * Check if the user query has parameters.
	 * If the type is 'all', it is considered equal to no parameters
	 *
	 * @return boolean
	 */
	public function hasParameters() {
		if ($this->get_type === 'all') {
			return false;
		}
		if ($this->hasSearch()) {
			return true;
		}
		if ($this->state) {
			return true;
		}
		if ($this->order) {
			return true;
		}
		if ($this->get) {
			return true;
		}
		return false;
	}

	/**
	 * Check if there is a search in the search object
	 *
	 * @return boolean
	 */
	public function hasSearch() {
		return $this->search->getRawInput() != "";
	}

	public function getGet() {
		return $this->get;
	}

	public function getGetName() {
		return $this->get_name;
	}

	public function getGetType() {
		return $this->get_type;
	}

	public function getName() {
		return $this->name;
	}

	public function getOrder() {
		return $this->order;
	}

	public function getSearch() {
		return $this->search;
	}

	public function getState() {
		return $this->state;
	}

	public function getUrl() {
		return $this->url;
	}

}

<?php
namespace YuzuruS\Wordpress;
/**
 * Post
 *
 * @author Yuzuru Suzuki <navitima@gmail.com>
 * @license MIT
 */
class Post
{

	private $_client;
	private $_username;
	private $_password;
	private $_title;
	private $_description;
	private $_keywords;
	private $_date;
	private $_categories;
	private $_wp_slug = 'entry_filename';
	private $_thumbnail_url;
	private $_blog_id;

	/**
	 * Post constructor.
	 *
	 * @param $username
	 * @param $password
	 * @param $endpoint
	 * @param int $port
	 */
	public function __construct($username, $password, $endpoint, $port = 80)
	{
		$this->_client = new \XML_RPC_Client('/xmlrpc.php', $endpoint, $port);
		$this->_username = new \XML_RPC_Value($username, 'string');
		$this->_password = new \XML_RPC_Value($password, 'string');
		$this->_date = new \XML_RPC_Value(time(), 'dateTime.iso8601');
	}

	/**
	 * setTitle
	 *
	 * @param $title
	 * @return $this
	 */
	public function setTitle($title)
	{
		$this->_title = new \XML_RPC_Value($title, 'string');
		return $this;
	}

	/**
	 * setDescription
	 *
	 * @param $description
	 * @return $this
	 */
	public function setDescription($description)
	{
		$this->_description = new \XML_RPC_Value($description, 'string');
		return $this;
	}

	/**
	 * setKeywords
	 *
	 * @param $keywords
	 * @return $this
	 */
	public function setKeywords($keywords)
	{
		if (is_array($keywords)) {
			foreach ($keywords as $keyword) {
				$this->_keywords[] = new \XML_RPC_Value($keyword, 'string');
			}
		} else {
			$this->_keywords = [
				new \XML_RPC_Value($keywords, 'string')
			];
		}
		$this->_keywords = new \XML_RPC_Value($this->_keywords, 'array');
		return $this;
	}

	/**
	 * setCategories
	 *
	 * @param $categories
	 * @return $this
	 */
	public function setCategories($categories)
	{
		if (is_array($categories)) {
			foreach ($categories as $category) {
				$this->_categories[] = new \XML_RPC_Value($category, 'string');
			}
		} else {
			$this->_categories = [
				new \XML_RPC_Value($categories, 'string')
			];
		}
		$this->_categories = new \XML_RPC_Value($this->_categories, 'array');
		return $this;
	}

	/**
	 * makeCategories
	 *
	 * @param $categories
	 * @return array
	 */
	public function makeCategories($categories)
	{
		if (empty($this->_blog_id)) {
			$res = $this->_getBlogId();
			if ($res['status'] === false) {
				return $res;
			}
		}
		foreach($categories as $c) {
			$tmp = [];
			foreach ($c as $k => $v) {
				$tmp[$k] = new \XML_RPC_Value($v, 'string');
			}
			$message = new \XML_RPC_Message(
				'wp.newCategory',
				[
					$this->_blog_id,
					$this->_username,
					$this->_password,
					new \XML_RPC_Value($tmp, 'struct')
				]
			);

			$result = $this->_client->send($message);

			if(!$result) {
				return [
					'status' => false,
					'msg' => 'Could not connect to the server.',
				];
			} else if($result->faultCode()) {
				return [
					'status' => false,
					'msg' => $result->faultString(),
				];
			}
		}
		return ['status' => true];
	}

	/**
	 * setDate
	 *
	 * @param $date
	 * @return $this
	 */
	public function setDate($date)
	{
		$this->_date = new \XML_RPC_Value(strtotime($date), '_dateTime.iso8601');
		return $this;
	}

	/**
	 * setWpSlug
	 *
	 * @param $wp_slug
	 * @return $this
	 */
	public function setWpSlug($wp_slug)
	{
		$this->_wp_slug = $wp_slug;
		return $this;
	}

	/**
	 * setThumbnail
	 *
	 * @param $url
	 * @return $this
	 */
	public function setThumbnail($url)
	{
		$this->_thumbnail_url = $url;
		return $this;
	}

	/**
	 * _getBlogId
	 *
	 * @return array
	 */
	private function _getBlogId() {
		$message = new \XML_RPC_Message(
			'blogger.getUsersBlogs',
			[
				new \XML_RPC_Value('', 'string'),
				$this->_username,
				$this->_password,
			]
		);

		$result = $this->_client->send($message);

		if(!$result) {
			return [
				'status' => false,
				'msg' => 'Could not connect to the server.',
			];
		} else if($result->faultCode()) {
			return [
				'status' => false,
				'msg' => $result->faultString(),
			];
		}

		$blogs = XML_RPC_decode($result->value());
		$this->_blog_id = new \XML_RPC_Value($blogs[0]["blogid"], 'string');
		return ['status' => true];
	}

	/**
	 * post
	 *
	 * @return array|mixed
	 */
	public function post() {
		$params = [
			'title' => $this->_title,
			'description' => $this->_description,
			'wp_slug' => $this->_wp_slug,
			'dateCreated' => $this->_date,
		];

		if (!empty($this->_categories)) {
			$params['categories'] = $this->_categories;
		}

		if (!empty($this->_keywords)) {
			$params['mt_keywords'] = $this->_keywords;
		}

		if (!empty($this->_thumbnail_url)) {
			$res = $this->_uploadImage($this->_thumbnail_url);
			if ($res['status'] === false) {
				return $res;
			}
			$params['wp_post_thumbnail'] = new \XML_RPC_Value($res['id'], 'int');
		}

		$content = new \XML_RPC_Value($params, 'struct');
		$publish = new \XML_RPC_Value(1, "boolean");

		if (empty($this->_blog_id)) {
			$res = $this->_getBlogId();
			if ($res['status'] === false) {
				return $res;
			}
		}

		$message = new \XML_RPC_Message(
			'metaWeblog.newPost',
			[$this->_blog_id, $this->_username, $this->_password, $content, $publish]
		);

		$result = $this->_client->send($message);
		if(!$result) {
			return [
				'status' => false,
				'msg' => 'Could not connect to the server.',
			];
		} else if($result->faultCode()) {
			return [
				'status' => false,
				'msg' => $result->faultString(),
			];
		}

		return ['status' => true, $result];
	}

	/**
	 * _uploadImage
	 *
	 * @param $path
	 * @param null $name
	 * @return array|mixed
	 */
	private function _uploadImage($path, $name = null) {
		$data = file_get_contents($path);
		if ($data === false) {
			return [
				'status' => false,
				'msg' => 'Could not connect to image url.',
			];
		}

		$info = new \finfo(FILEINFO_MIME_TYPE);
		$mimeType = $info->buffer($data);
		$fileName = $name ? $name : basename($path);
		$file = new \XML_RPC_Value(
			[
				'type' => new \XML_RPC_Value($mimeType, 'string'),
				'bits' => new \XML_RPC_Value($data, 'base64'),
				'name' => new \XML_RPC_Value($fileName, 'string')
			],
			'struct'
		);
		$message = new \XML_RPC_Message(
			'wp.uploadFile',
			[
				new \XML_RPC_Value(1, 'int'),
				$this->_username,
				$this->_password,
				$file
			]
		);
		return $this->_sendXMLRPC($message);
	}

	/**
	 * _sendXMLRPC
	 *
	 * @param $message
	 * @return array|mixed
	 */
	private function _sendXMLRPC($message) {
		if (!($res = $this->_client->send($message))) {
			return [
				'status' => false,
				'msg' => 'Could not connect to the server.',
			];
		} else if($res->faultCode()) {
			return [
				'status' => false,
				'msg' => $res->faultString(),
			];
		}
		return XML_RPC_decode($res->value());
	}
}

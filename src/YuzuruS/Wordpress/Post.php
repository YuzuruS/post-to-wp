<?php
namespace YuzuruS\Wordpress;
use PhpXmlRpc\Client;
use PhpXmlRpc\Value;
use PhpXmlRpc\Request;
use PhpXmlRpc\Encoder;

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
    private $_wp_slug;
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
        $this->_client = new Client('/xmlrpc.php', $endpoint, $port);
        $this->_username = new Value($username, Value::$xmlrpcString);
        $this->_password = new Value($password, Value::$xmlrpcString);
        $this->_date = new Value(time(), Value::$xmlrpcDateTime);
        $this->_wp_slug = new Value('entry_filename', Value::$xmlrpcString);
    }

    /**
     * setTitle
     *
     * @param $title
     * @return $this
     */
    public function setTitle($title)
    {
        $this->_title = new Value($title, Value::$xmlrpcString);
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
        $this->_description = new Value($description, Value::$xmlrpcString);
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
                $this->_keywords[] = new Value($keyword, Value::$xmlrpcString);
            }
        } else {
            $this->_keywords = [
                new Value($keywords, Value::$xmlrpcString)
            ];
        }
        $this->_keywords = new Value($this->_keywords, Value::$xmlrpcArray);
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
                $this->_categories[] = new Value($category, Value::$xmlrpcString);
            }
        } else {
            $this->_categories = [
                new Value($categories, Value::$xmlrpcString)
            ];
        }
        $this->_categories = new Value($this->_categories, Value::$xmlrpcArray);
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
                $tmp[$k] = new Value($v, Value::$xmlrpcString);
            }
            $message = new Request(
                'wp.newCategory',
                [
                    $this->_blog_id,
                    $this->_username,
                    $this->_password,
                    new Value($tmp, Value::$xmlrpcStruct)
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
        $this->_date = new Value(strtotime($date), Value::$xmlrpcDateTime);
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
        $this->_wp_slug = new Value($wp_slug, Value::$xmlrpcString);
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
        $message = new Request(
            'blogger.getUsersBlogs',
            [
                new Value('', Value::$xmlrpcString),
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
        $xmlrcp_decoder = new Encoder();
        $blogs = $xmlrcp_decoder->decode($result->value());
        $this->_blog_id = new Value($blogs[0]["blogid"], Value::$xmlrpcInt);
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
            if (isset($res['status']) && $res['status'] === false) {
                return $res;
            }
            $params['wp_post_thumbnail'] = new Value($res['id'], Value::$xmlrpcInt);
        }

        $content = new Value($params, Value::$xmlrpcStruct);
        $publish = new Value(1, Value::$xmlrpcBoolean);

        if (empty($this->_blog_id)) {
            $res = $this->_getBlogId();
            if ($res['status'] === false) {
                return $res;
            }
        }

        $message = new Request(
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

        $file = new Value(
            [
                'type' => new Value($mimeType, Value::$xmlrpcString),
                'bits' => new Value($data, Value::$xmlrpcBase64),
                'name' => new Value($fileName, Value::$xmlrpcString)
            ],
            Value::$xmlrpcStruct
        );
        $message = new Request(
            'wp.uploadFile',
            [
                new Value(1, Value::$xmlrpcInt),
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
        $xmlrcp_decoder = new Encoder();
        return $xmlrcp_decoder->decode($res->value());
    }
}

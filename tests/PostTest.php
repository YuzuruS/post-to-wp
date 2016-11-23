<?php
require __DIR__ . '/../vendor/autoload.php';
/**
 * PostTest
 *
 * @version $id$
 * @copyright Yuzuru Suzuki
 * @author Yuzuru Suzuki <navitima@gmail.com>
 * @license PHP Version 3.0 {@link http://www.php.net/license/3_0.txt}
 */
use YuzuruS\Wordpress\Post;
class PostTest extends \PHPUnit_Framework_TestCase
{

	public function testPost()
	{
		$wp = new Post(getenv('WP_USERNAME'), getenv('WP_PASSWD'), getenv('WP_ENDPOINT'));

		$res = $wp->makeCategories([
			['name' => 'かて1', 'slug' => 'cate1'],
			['name' => 'かて2', 'slug' => 'cate2'],
		]);
		$this->assertTrue($res['status']);

		$res = $wp
			->setTitle('たいとる')
			->setDescription('本文')
			->setKeywords(['key1','key2'])
			->setCategories(['かて1','かて2'])
			->setDate('2016-11-11')
			->setWpSlug('entry')
			//->setThumbnail('https://www.pakutaso.com/shared/img/thumb/SAYA160312500I9A3721_TP_V.jpg')
			->post();
		$this->assertTrue($res['status']);
	}

	public function testWrongPost1()
	{
		$wp = new Post(getenv('WP_WRONGNAME'), getenv('WP_PASSWD'), getenv('WP_ENDPOINT'));

		$res = $wp->makeCategories([
			['name' => 'かて1', 'slug' => 'cate1'],
			['name' => 'かて2', 'slug' => 'cate2'],
		]);

		$this->assertFalse($res['status']);
	}

	public function testWrongPost2()
	{
		$wp = new Post(getenv('WP_WRONGNAME'), getenv('WP_PASSWD'), getenv('WP_ENDPOINT'));

		$res = $wp
			->setTitle('たいとる')
			->setDescription('本文')
			->setKeywords(['key1','key2'])
			->setCategories(['かて1','かて2'])
			->setDate('2016-11-11')
			->setWpSlug('entry')
			->setThumbnail('https://www.pakutaso.com/shared/img/thumb/SAYA160312500I9A3721_TP_V.jpg')
			->post();

		$this->assertFalse($res['status']);
	}

	public function tearDown()
	{
	}
}

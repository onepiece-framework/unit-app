<?php
/**
 * unit-app:/App.class.php
 *
 * @created   2018-04-04
 * @version   1.0
 * @package   unit-app
 * @author    Tomoaki Nagahara <tomoaki.nagahara@gmail.com>
 * @copyright Tomoaki Nagahara All right reserved.
 */

/** namespace
 *
 * @created   2019-02-20
 */
namespace OP\UNIT;

/** Used class.
 *
 * @created   2019-02-20
 */
use Exception;
use OP\OP_CORE;
use OP\OP_UNIT;
use OP\OP_SESSION;
use OP\IF_UNIT;
use OP\IF_APP;
use OP\Env;
use OP\Cookie;
use OP\Notice;
use OP\UNIT_APP;
use OP\UNIT_ROUTER;
use OP\UNIT_LAYOUT;
use OP\UNIT_TEMPLATE;
use function OP\RootPath;
use function OP\ConvertURL;
use function OP\ConvertPath;

/** App
 *
 * @created   2018-04-04
 * @version   1.0
 * @package   unit-app
 * @author    Tomoaki Nagahara <tomoaki.nagahara@gmail.com>
 * @copyright Tomoaki Nagahara All right reserved.
 */
class App implements IF_UNIT, IF_APP
{
	/** trait.
	 *
	 */
	use OP_CORE, OP_UNIT, OP_SESSION, UNIT_APP, UNIT_ROUTER, UNIT_LAYOUT, UNIT_TEMPLATE;

	/** SmartURL Arguments.
	 *
	 * @var array
	 */
	private $_args;

	/** Content is the result of execute the endpoint.
	 *
	 * @var string
	 */
	private $_content;

	/** ETag
	 *
	 */
	private function _ETag()
	{

	}

	/** Automatically.
	 *
	 */
	function Auto()
	{
		try{
			//	Is http?
			if( Env::isHttp() ){

				//	Get End-Point.
				$endpoint = $this->EndPoint();

				//	Get extension
				$ext = substr($endpoint, strrpos($endpoint, '.') + 1);

				//	Get mime by extension.
				$mime = Env::Ext($ext);

				//	Set MIME
				Env::Mime($mime);

				//	Get End-Point content.
				$this->_content = self::__TEMPLATE_GET($endpoint, ['app'=>$this]);

				//	ETag
				$this->_ETag();

				//	Layout
				$this->__LAYOUT();

				/*

				//	Get current mime.
				$mime = Env::Mime();

				//	Check whether to do layout.
				if( $mime === 'text/html' and Env::Get('layout')['execute'] ?? null ){
					//	Do layout.
					$this->Unit('Layout')->Auto($this->_content);
				}else{
					//	No layout.
					echo $this->_content;
				};

				//	...
				unset($this->_content);

				*/

			}else{
				//	...
				$root = $_SERVER['PWD'].'/';
				$path = $_SERVER['argv'][1] ?? 'index.php';
				$file = $root . $path;

				//	...
				if(!$endpoint = realpath($file) ){
					throw new Exception("This file has not been exists. ($file)");
				};

				//	...
				$this->Template($endpoint);
			};
		}catch( \Throwable $e ){
			Notice::Set($e);
		};
	}

	/** Output content.
	 *
	 * @created  2019-11-25
	 */
	function Content()
	{
		echo $this->_content;
	}

	/** Template
	 *
	 * @created  2019-11-21
	 * @param    string      $path
	 * @param    string      $args
	 * @return   string      $content
	 */
	function Template(string $path, array $args=[])
	{
		//	...
		$args['app'] = $this;

		//	...
		return $this->__TEMPLATE($path, $args, true);
	}

	/** Layout
	 *
	 * <pre>
	 * App::Layout(true);       // Execute layout.
	 * App::Layout(false);      // Does not execute layout.
	 * App::Layout('name');     // Set layout name.
	 * $layout = App::Layout(); // Get layout name.
	 * </pre>
	 *
	 * @updated  2019-05-10  Optimized.
	 * @param    null|boolean|string    $value
	 * @return   null|boolean|string    $value
	 */
	static function Layout($val=null)
	{
		return self::__LAYOUT_CONFIG($val);
	}

	/** Register WebPack file.
	 *
	 * @param  string|array  $path
	 */
	function WebPack($path)
	{
		$this->Unit('WebPack')->Auto($path);
	}

	/** Get/Set title.
	 *
	 * @param  string  $title
	 * @param  string  $separator
	 * @return string  $title
	 */
	static function Title($title=null, $separator=' | ')
	{
		//	...
		static $_title;

		//	...
		if( empty($title) ){
			return $_title;
		}

		//	...
		if( empty($_title) ){
			$_title = Env::Get('app')['title'] ?? null;
		};

		//	...
		$_title = $_title ? $title . $separator . $_title : $title;
	}

	/** Unique User ID.
	 *
	 * @return  string  $uuid
	 */
	static function UUID()
	{
		//	...
		if(!$uuid = Cookie::Get('uuid') ){
			$uuid = substr( md5($_SERVER['REMOTE_ADDR'] . microtime()), 0, 10);
			Cookie::Set('uuid', $uuid);
		}

		//	...
		return $uuid;
	}

	/** Convert to url from meta url.
	 *
	 * @param   string  $path
	 * @return  string  $url
	 */
	static function URL(string $url)
	{
		//	...
		static $_app, $_locale;

		//	Cache
		if( $_app === null ){
			$_app = ConvertURL('app:/');
		};

		//	Cache
		if( $_locale === null ){
			//	...
			if( Env::Get('app')['g11n'] ?? null ){
				$_locale = Cookie::Get('locale') ?? '';
			}else{
				$_locale = false;
			};
		};

		//	Check if url query.
		if( $pos = strpos($url, '?')){
			$que = substr($url, $pos);
			$url = substr($url, 0, $pos);
			$url = rtrim($url,'/').'/';
		};

		//	Check if app root.
		$result = ($url === 'app:/') ? $_app: ConvertURL($url);

		//	...
		if( $_locale ){

			//	...
			$path = substr($result, strlen($_app)-1);

			//	...
			$result = $_app . $_locale . $path;
		};

		//	...
		return '/'.ltrim($result,'/') . ($que ?? null);
	}

	/** CDN FQDN
	 *
	 * @created  2019-04-18
	 * @param    string      $url
	 * @return   string      $url
	 */
	static function CDN()
	{
		//	...
		require_once(__DIR__.'/function/cdn.php');
		return APP\FUNCTIONS\CDN();
	}

	/** Canonical
	 *
	 * @created  2019-04-17
	 * @param    string     $url
	 * @return   string     $fqdn
	 */
	static function Canonical($url=null)
	{
		//	...
		$config = Env::Get('canonical');

		//	...
		$scheme = $config['scheme'] ?? empty($_SERVER['HTTPS']) ? 'http':'https';
		$domain = $config['domain'] ?? $_SERVER['HTTP_HOST'];
		$uri    = $url              ?? $_SERVER['REQUEST_URI'];

		//	...
		return "{$scheme}://{$domain}{$uri}";
	}
}

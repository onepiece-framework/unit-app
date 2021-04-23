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
use OP\Config;
use OP\Cookie;
use OP\Notice;
use OP\UNIT_APP;
use OP\UNIT_ROUTER;
use OP\UNIT_LAYOUT;
use OP\UNIT_TEMPLATE;
use function OP\Unit;
use function OP\RootPath;
use function OP\ConvertURL;
use function OP\ConvertPath;
use function OP\CompressPath;
use function OP\Content;
use function OP\Template;
use function OP\GetTemplate;
use function OP\UNIT\APP\GetMIME;

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

				//	Check end-point if asset directory.
				if( strpos($endpoint, RootPath('asset')) === 0 ){
					//	Overwrite end-point.
					$endpoint = ConvertPath('app:/404.php');
				};

				//	Execute End-Point.
				$hash = Content(CompressPath($endpoint), ['app'=>$this]);

				//	ETag
				if( Config::Get('app')['etag'] ?? null ){
					Unit('ETag')->Auto($hash);
				}

				//	Set mime if empty.
				if(!$mime = Env::Mime() ){
					//	Get extension
					$ext = substr($endpoint, strrpos($endpoint, '.') + 1);

					//	Get MIME
					include(__DIR__.'/function/GetMIME.php');
					$mime = GetMIME($ext);

					//	Set MIME
					Env::Mime($mime);
				}

				//	Check whether to do layout.
				if( $mime === 'text/html' and Env::Get('layout')['execute'] ?? null ){
					//	Do layout.
					$this->__LAYOUT();
				}else{
					//	No layout.
					Content();
				};

			}else{
				//	In case of shell
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
		Unit('WebPack')->Auto($path);
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
}

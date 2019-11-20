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
	use OP_CORE, OP_UNIT, OP_SESSION;

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

	/** Automatically.
	 *
	 */
	function Auto()
	{
		try{
			//	Is http?
			if( Env::isHttp() ){

				//	Set MIME
				Env::Mime('text/html');

				/* @var $router \OP\UNIT\Router */
				$router = $this->Unit('Router');

				//	Get End-Point.
				$endpoint = $router->EndPoint();

				//	...
				if( $g11n = Env::Get('g11n') ){
					//	...
					if( ($g11n['execute'] ?? null) and ($g11n['transfer'] ?? null) ){
						//	Separate G11n rogif
						include_once(__DIR__.'/G11n.class.php');
						APP\G11n::Auto($router);
					};
				};

				//	Get End-Point content.
				$this->_content = $this->Template($endpoint, [], 'Get');

				//	...
				$mime = Env::Mime();

				//	ETag
				if( Env::Get('app')['etag'] ?? null ){
					//	Check if MIME is HTML.
					if( $mime === 'text/html' ){
						//	...
						if( Env::isAdmin() ){
							$notice = Notice::Has() ? date('Y-m-d H:i:s'): 'Notice is empty';
							$notice.= ', ';
						}else{
							$notice = null;
						};

						//	Add unique hash to content for ETag.
						$this->_content .=
						PHP_EOL.
						'<!-- '.
						$notice .
						$this->Unit('WebPack')->FileContentHash('js')  .', '.
						$this->Unit('WebPack')->FileContentHash('css') .', '.
						' -->'.PHP_EOL;
					};

					//	Generate ETag.
					$etag = substr(md5($this->_content), 0, 8);

					//	Set ETag.
					$this->Unit('Http')->Header()->ETag($etag);
				};

				//	Check whether to do layout.
				if( $mime === 'text/html' and Env::Get('layout')['execute'] ){
					//	Do layout.
					$this->Unit('Layout')->Auto($this->_content);
				}else{
					//	No layout.
					echo $this->_content;
				};
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

	/** Template
	 *
	 * @param  string $path
	 * @param  string $args
	 * @return string $content
	 */
	function Template(string $path, array $args=[], $method='Out')
	{
		//	...
		$args['app'] = $this;

		//	...
		return $this->Unit('Template')->{$method}($path, $args, true);
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
		//	Get layout name.
		if( $val === null ){
			//	...
			$layout = Env::Get('layout');

			//	...
			if( empty($layout['execute']) ){
				return false;
			};

			//	...
			return $layout['name'] ?? $_GET['layout'] ?? $_GET['name'] ?? null;
		};

		//	Set config.
		switch( $type = gettype($val) ){
			case 'boolean':
				$layout['execute'] = $val;
				break;

			case 'string':
				$layout['name'] = $val;
				break;

			default:
				Notice::Set("Has not been support this type. ($type)");
				return;
		};

		//	...
		Env::Set('layout', $layout);
	}

	/** Register WebPack file.
	 *
	 * @param  string|array  $path
	 */
	function WebPack($path)
	{
		$this->Unit('WebPack')->Auto($path);
	}

	/** Get to transparently GET or POST.
	 *
	 * @updated  2019-05-10  Add $key param.
	 * @param    string      $key
	 * @return   array       $request
	 */
	function Request($key=null)
	{
		//	...
		switch( strtoupper($_SERVER['REQUEST_METHOD'] ?? null) ){
			case 'GET':
				return \OP\Encode( ($key ? $_GET [$key] : $_GET ) );

			case 'POST':
				return \OP\Encode( ($key ? $_POST[$key] : $_POST) );
		};
	}

	/** Get Smart URL Arguments.
	 *
	 * @return  array  $args
	 */
	function Args()
	{
		return \OP\Encode( $this->Unit('Router')->Args() );
	}

	/** Get/Set title.
	 *
	 * @param  string  $title
	 * @param  string  $separator
	 * @return string  $title
	 */
	function Title($title=null, $separator=' | ')
	{
		//	...
		static $_title = [];

		//	...
		if( $title ){
			//	Stack title.
			array_unshift($_title, $title.$separator);
		}else{
			//	Print title.
			return join('', $_title) . Env::Get('app')['title'] ?? null;
		};
	}

	/** Unique User ID.
	 *
	 * @return  string  $uuid
	 */
	function UUID()
	{
		//	...
		if(!$uuid = Cookie::Get('uuid') ){
			$uuid = substr( md5($_SERVER['REMOTE_ADDR'] . microtime()), 0, 10);
			Cookie::Set('uuid', $uuid);
		}

		//	...
		return $uuid;
	}

	/** Env
	 *
	 * @param   string  $key
	 * @param   mixed   $var
	 * @return \OP\Env  $env
	 */
	function Env($key=null, $var='')
	{
		//	...
		static $_env;

		//	...
		if(!$_env ){
			$_env = new Env();
		};

		//	...
		if( $key === null ){
			return $_env;
		}

		//	...
		if( $var === '' ){
			return Env::Get($key);
		}else{
			Env::Set($key, $var);
		};
	}

	/** Convert to url from meta url.
	 *
	 * @param   string  $path
	 * @return  string  $url
	 */
	function URL(string $url)
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
			if( Env::Get('g11n')['execute'] ?? null ){
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
	function CDN(string $url)
	{
		//	...
		$url = $this->URL($url);

		//	...
		if( Env::isLocalhost() ){
			return $url;
		};

		//	...
		if(!Env::Get('cdn')['execute'] ?? null ){
			return $url;
		};

		//	...
		$scheme = empty($_SERVER['HTTPS']) ? 'http':'https';
		$domain = Env::Get('cdn')['domain'];

		//	...
		return "{$scheme}://{$domain}{$url}";
	}
}

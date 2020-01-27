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
			/* @var $router \OP\UNIT\Router */
			$router = $this->Unit('Router');

			//	Get End-Point.
			$endpoint = $router->EndPoint();

			//	Is http?
			if( Env::isHttp() ){
				//	...
				$this->UserID();

				//	...
				Env::Mime('text/html');

				//	...
				if( $g11n = Env::Get('g11n') ){
					//	...
					if( ($g11n['execute'] ?? null) and ($g11n['transfer'] ?? null) ){
						include_once(__DIR__.'/G11n.class.php');
						APP\G11n::Auto($router);
					};
				};

				//	Get End-Point content.
				$this->_content = $this->Template($endpoint, [], 'Get');

				//	ETag
				if( Env::Get('app')['etag'] ?? null ){
					//	...
					if( Env::Mime() === 'text/html' ){
						$this->_content .=
						'<!-- '.
						sprintf('Layout(%s)', Env::Get('layout')['name']) .', '.
						sprintf('Notice::Has(%s)', Notice::Has() ? date('Y-m-d H:i:s'):'null') .', '.
						$this->Unit('WebPack')->FileContentHash('js')  .', '.
						$this->Unit('WebPack')->FileContentHash('css') .', '.
						' -->'.PHP_EOL;
					};

					//	...
					$etag = substr(md5($this->_content), 0, 8);

					//	...
					$this->Unit('Http')->Header()->ETag($etag);
				};

				//	Display layout in content.
				if( (empty($mime = Env::Mime()) or $mime === 'text/html') and Env::Get('layout')['execute'] ){
					$this->Unit('Layout')->Auto($this->_content);
				}else{
					echo $this->_content;
				};

			}else{
				//	...
				$this->Template($endpoint);
			};
		}catch( \Throwable $e ){
			Notice::Set($e);
		};
	}

	/** Template
	 *
	 * @param	 string		 $path
	 * @param	 string		 $args
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
	 * @param	 null|boolean|string	 $value
	 */
	function Layout($val=null)
	{
		//	...
		$config = Env::Get('layout');

		//	...
		if( is_bool($val) ){
			$config['execute'] = $val;
		}else if( is_string($val) ){
			$config['name'] = $val;
		};

		//	...
		Env::Set('layout', $config);

		//	...
	//	return $config;
	}

	/** WebPack
	 *
	 */
	function WebPack($path)
	{
		$this->Unit('WebPack')->Auto($path);
	}

	/** Get to transparently GET or POST.
	 *
	 * @return array $request
	 */
	function Request()
	{
		//	...
		static $_request = null;

		//	...
		if( $_request === null){
			//	...
			switch( strtoupper($_SERVER['REQUEST_METHOD'] ?? null) ){
				case 'GET':
					$_request = \OP\Encode($_GET);
					break;

				case 'POST':
					$_request = \OP\Encode($_POST);
					break;
			};

			//	In case of shell.
			if( isset($_SERVER['argv']) ){
				//	...
				foreach( array_slice($_SERVER['argv'], 2) as $arg ){
					if( $pos = strpos($arg, '=') ){
						$key = substr($arg, 0, $pos);
						$var = substr($arg, $pos+1);
					}else{
						$key = $arg;
						$var = null;
					};

					//	...
					$_request[$key] = $var;
				};

				//	...
				$_request = \OP\Encode($_request);
			};
		};

		//	...
		return $_request;
	}

	/** Get Smart URL Arguments.
	 *
	 * @return array
	 */
	function Args()
	{
		return \OP\Encode( $this->Unit('Router')->Args() );
	}

	/** Get/Set title.
	 *
	 * @param	 string	 $title
	 * @param	 string	 $separator
	 * @return	 string	 $title
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
	 * @return NULL
	 */
	function UserID()
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
	 * @return Env
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
	 * @return  string
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
			$_locale = Cookie::Get('locale') ?? '';
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

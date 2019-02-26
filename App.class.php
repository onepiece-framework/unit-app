<?php
/**
 * unit-app:/App.class.php
 *
 * @creation  2018-04-04
 * @version   1.0
 * @package   unit-app
 * @author    Tomoaki Nagahara <tomoaki.nagahara@gmail.com>
 * @copyright Tomoaki Nagahara All right reserved.
 */

/** namespace
 *
 * @creation  2019-02-20
 */
namespace OP\UNIT;

/** Used class.
 *
 */
use OP\Env;
use OP\Unit;
use OP\Notice;
use function OP\ConvertPath;

/** App
 *
 * @creation  2018-04-04
 * @version   1.0
 * @package   unit-app
 * @author    Tomoaki Nagahara <tomoaki.nagahara@gmail.com>
 * @copyright Tomoaki Nagahara All right reserved.
 */
class App implements \OP\IF_UNIT, \OP\IF_APP
{
	/** trait.
	 *
	 */
	use \OP\OP_CORE, \OP\OP_UNIT;

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
			//	Get End-Point.
			$endpoint = $this->Unit('Router')->EndPoint();

			//	Get End-Point content.
			$content = $this->Template($endpoint, [], 'Get');

			//	Display layout in content.
			$this->Unit('Layout')->Auto($content);

		}catch( \Throwable $e ){
			Notice::Set($e);
		};
	}

	/** Automatically run.
	 *
	 */
	static function _Auto()
	{
		//	End-point file path.
		$endpoint = null;

		//	Each http status code
		switch( $status = $_SERVER['REDIRECT_STATUS'] ){
			//	Error status.
			case '403':
		//	case '404':
				//	Overwrite end-point file path.
				$endpoint = ConvertPath("app:/{$status}.php");
				break;
		}

		//	Execute end-point.
	//	$content = OP\UNIT\NEWWORLD\Dispatch::Get($endpoint);

		//	For developers.
		if( Env::isLocalhost() ){
			$etag  = $_GET['etag'] ?? true;
			$cache = false;
		}

		//	Get mime
		list($type, $ext) = explode('/', strtolower(Env::Mime() ?? '/') );

		//	...
		if( $type === 'text' ){
			//	...
			switch( $ext  ){
				case 'plain':
				case 'css':
				case 'json':
				case 'jsonp':
				case 'javascript':
					$etag  = $etag  ?? true; // Add Etag to URL Query for JS and CSS.
					$cache = $cache ?? true;
					break;

				default:
					//	Set mime.
					Env::Mime('text/html');

					//	Etag flag.
					if( $etag = $etag ?? true ){
						//	Check if Notice occurring.
						if( Notice::Has() ){
							//	Finger print is microtime.
							$fp = microtime();
						}else{
							//	Get unique hash key.
							Unit::Load('webpack');
						//	$hash_js  = \OP\UNIT\WebPack::Hash('js');
						//	$hash_css = \OP\UNIT\WebPack::Hash('css');

							//	Finger print is unique hash key.
							$fp = "$hash_js, $hash_css";
						}

						//	Add finger print for reload.
						$content .= "<!-- $fp -->";
					}
				break;
			}
		}else{
			//	image
			$etag  = true;
			$cache = true;
		}

		//	Generate 304 Not Modified hash key by content.
		if( $etag ){
			$etag = Hasha1($content);
		}

		//	Cache control.
		if( $etag || ($cache ?? false) ){
			//	Overwrite at empty.
			header('Pragma: ', true);

			//	Cache control.
			$age    = 60*60*1;
			header("Cache-Control: max-age={$age}", true);

			/** This section is for http 1.0.
			 *
			 *  If not set "Cache-Control" header then search "Expires".
			 *  If exists "Expires" header then subtraction from "Date" header. (Will to max-age)
			 *  If has not been set both header then search "Last-modified" header. (Do automatic calculate)
			 */
			$date   = time();
			$time   = $date + $age;
			$date   = gmdate('D, j M Y H:i:s ', $date) . 'GMT';
			$expire = gmdate('D, j M Y H:i:s ', $time) . 'GMT';
			header("Date: {$date}", true);
			header("Expires: {$expire}", true);
		}

		//	Submit Etag header.
		if( $etag ){
			/*
			//	...
			$last_modified = filemtime( __FILE__ );
			$last_modified = gmdate( "D, d M Y H:i:s T", $last_modified);

			//	...
			header("Last-Modified: {$last_modified}", true);
			*/
			header("Etag: {$etag}", true);
		}

		//	Check 304 Not Modified.
		if( $etag === filter_input( INPUT_SERVER, 'HTTP_IF_NONE_MATCH' ) ){
			header('HTTP/1.1 304 Not Modified');
			return;
		}

		//	The content is wrapped in the Layout.
		echo self::$_LAYOUT_::Get($content);
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
		return $this->Unit('Template')->$method($path, $args, true);
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
		return $config;
	}

	/** Get to transparently GET or POST.
	 *
	 * @return array $request
	 */
	function Request()
	{
		//	...
		$method = $_SERVER['REQUEST_METHOD'];

		//	...
		$request = ${"_{$method}"};

		//	...
		return Escape( $request ?? [] );
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
		if( $title ){
			Env::Set('title', $title.$separator.Env::Get('title'));
		};

		//	...
		return Env::Get('title');
	}
}

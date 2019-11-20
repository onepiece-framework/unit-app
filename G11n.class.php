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
namespace OP\UNIT\APP;

/** Used class.
 *
 */
use OP\OP_CORE;
use OP\Env;
use OP\Cookie;
use OP\Unit;
use function OP\RootPath;
use function OP\ConvertURL;
use function OP\ConvertPath;

/** G11n
 *
 * @created   2018-04-04
 * @version   1.0
 * @package   unit-app
 * @author    Tomoaki Nagahara <tomoaki.nagahara@gmail.com>
 * @copyright Tomoaki Nagahara All right reserved.
 */
class G11n
{
	/** trait.
	 *
	 */
	use OP_CORE;

	/** Automatically.
	 *
	 * @param \OP\UNIT\Router
	 */
	static function Auto($router)
	{
		//	Get End-Point.
		$endpoint = $router->EndPoint();

		//	In case of not has locale.
		if( empty($router->Locale()) ){
			//	Get transfer locale.
			if( $locale = Cookie::Get('locale') ?? Env::Get('g11n')['default'] ){

				//	Use at generate URL.
				$args = $router->Args();

				//	Separate URL Query.
				$url_query = ($pos = strpos($_SERVER['REQUEST_URI'], '?')) ? substr($_SERVER['REQUEST_URI'], $pos): null;

				//	/var/www/html/app/
				$app_root = RootPath()['app'];

				//	/var/www/html/app/img/index.php --> /var/www/html/app/img/user/icon.png
				$fullpath = dirname($endpoint) .'/'. join('/', $args);

				//	/var/www/html/app/img/user/icon.png --> /img/user/icon.png
				$path = substr($fullpath, strlen($app_root)-1);

				//	/var/www/html/app/{$locale}/img/user/icon.png
				$url  = $app_root . $locale . $path . $url_query;

				//	...
				Unit::Instantiate('Http')->Location($url);
			};
		};
	}
}

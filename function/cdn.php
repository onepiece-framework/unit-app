<?php
/**
 * unit-app:/function/cdn.php
 *
 * @created   2019-11-29
 * @version   1.0
 * @package   unit-app
 * @author    Tomoaki Nagahara <tomoaki.nagahara@gmail.com>
 * @copyright Tomoaki Nagahara All right reserved.
 */

/** namespace
 *
 * @created   2019-11-29
 */
namespace OP\UNIT\APP\FUNCTIONS;

/** use
 *
 * @created   2019-11-29
 */
use OP\Env;

/** Get CDN FQDN
 *
 */
function CDN()
{
	//	...
	if( Env::isLocalhost() ){
		return null;
	};

	//	...
	if(!Env::Get('cdn')['execute'] ?? null ){
		return null;
	};

	//	...
	$domain = Env::Get('cdn')['fqdn'];

	//	...
	return "//{$domain}";
}

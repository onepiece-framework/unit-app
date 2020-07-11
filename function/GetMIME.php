<?php
/** op-unit-app:/function/GetMIME.php
 *
 * @created   2020-07-11
 * @version   1.0
 * @package   op-unit-app
 * @author    Tomoaki Nagahara <tomoaki.nagahara@gmail.com>
 * @copyright Tomoaki Nagahara All right reserved.
 */

/** namespace
 *
 */
namespace OP\UNIT\APP;

/** use
 *
 */
use OP\Notice;

/** Get MIME from extension.
 *
 */
function GetMIME(string $ext):string
{
	//	...
	switch($ext){
		//	...
		case 'php':
		case 'html':
			$mime = 'text/html';
			break;

		//	...
		case 'css':
			$mime = 'text/css';
			break;

		//	...
		case 'js':
			$mime = 'text/javascript';
			break;

		//	...
		default:
			Notice::Set("This MIME has not been support. ($mime)");
	}

	//	...
	return $mime;
}

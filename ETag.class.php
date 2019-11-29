<?php
/**
 * unit-app:/ETag.class.php
 *
 * @created   2019-11-21
 * @version   1.0
 * @package   unit-app
 * @author    Tomoaki Nagahara <tomoaki.nagahara@gmail.com>
 * @copyright Tomoaki Nagahara All right reserved.
 */

/** namespace
 *
 * @created   2019-11-21
 */
namespace OP\UNIT\App;

/** Used class.
 *
 * @created   2019-11-21
 */
use OP\Env;
use OP\Unit;
use OP\Notice;

/** ETag
 *
 * @created   2019-11-21
 * @version   1.0
 * @package   unit-app
 * @author    Tomoaki Nagahara <tomoaki.nagahara@gmail.com>
 * @copyright Tomoaki Nagahara All right reserved.
 */
class ETag
{
	/** ETag
	 *
	 */
	private function _ETag()
	{
		//	...
		if(!$this->ETag() ){
			return;
		}

		//	Comment branch.
		switch( Env::Mime() ){
			case 'text/html':
				$st = '<!--';
				$en = ' -->';
				break;

			case 'text/css':
			case 'text/javascript':
				$st = '/*';
				$en = '*/';
				break;

			default:
				return;
		}

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
		$st.
		$notice .
		$this->Unit('WebPack')->FileContentHash('js')  .', '.
		$this->Unit('WebPack')->FileContentHash('css') .', '.
		$en.
		PHP_EOL;

		//	Generate ETag.
		$etag = substr(md5($this->_content), 0, 8);

		//	Set ETag.
		Unit::Singleton('Http')->Header()->ETag($etag);
	}

	/** ETag
	 *
	 * @created  2019-10-28
	 * @param    boolean
	 * @return   boolean
	 */
	function ETag($value=null)
	{
		static $_etag;
		if( $value !== null ){
			$_etag = $value;
		}
		return $_etag ?? Env::Get('app')['etag'] ?? null;
	}

}

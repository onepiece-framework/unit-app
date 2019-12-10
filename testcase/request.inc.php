<?php
/**
 * module-testcase:/unit/app/request.php
 *
 * @creation  2019-04-02
 * @version   1.0
 * @package   module-testcase
 * @author    Tomoaki Nagahara <tomoaki.nagahara@gmail.com>
 * @copyright Tomoaki Nagahara All right reserved.
 */
/* @var $app     \OP\UNIT\App     */
$request = $app->Request();
D( $request );
?>
<form method="post">
	<input type="text"   name="text"   value="<?= $request['text'] ?? null ?>"/>
	<input type="submit" name="submit" value=" Submit "/>
</form>

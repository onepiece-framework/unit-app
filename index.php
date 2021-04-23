<?php
/**
 * unit-app:/index.php
 *
 * @created   2018-04-04
 * @version   1.0
 * @package   unit-app
 * @author    Tomoaki Nagahara <tomoaki.nagahara@gmail.com>
 * @copyright Tomoaki Nagahara All right reserved.
 */

/** namespace
 *
 * @created   2019-11-21
 */
namespace OP;

/** Load
 *
 */
Unit::Load('Router');
Unit::Load('Layout');

/** Include
 *
 */
include(__DIR__.'/APP.trait.php');
include(__DIR__.'/App.class.php');

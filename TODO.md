# Cache

```
<?php
/** Http cache.
 *
 *  @creation   2018-10-02
 *  @separation 2019-02-28
 */
private function _Cache()
{
	//	Lifetime
	if(!$age = $_GET['cache'] ?? null ){
		$age = Env::isLocalhost() ? 10: (60 * 60 * 1);
	};

	//	Overwrite at empty.
	header('Pragma: ', true);

	//	Cache control.
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

	//	...
	$last_modified = \OP\Time::Get(true); // filemtime( __FILE__ );
	$last_modified = gmdate( "D, d M Y H:i:s T", $last_modified);

	//	...
	header("Last-Modified: {$last_modified}", true);
}
?>
```

# ETag

```
<?php
/** Etag.
 *
 *  This routine will execution time and execution load can not be saved.
 *  But, can save on network traffic.
 *
 *  @creation  2018-10-02
 */
private function _Etag()
{
	//	Generate 304 Not Modified hash key by content.
	$etag = $_GET['etag'] ?? Hasha1($this->_content);

	//	...
	header("Etag: {$etag}", true);

	//	...
	$io = ($etag !== filter_input( INPUT_SERVER, 'HTTP_IF_NONE_MATCH' ));

	//	Check 304 Not Modified.
	if( $io ){
		header('HTTP/1.1 304 Not Modified');
	};

	//	...
	return $io;
}
?>
```


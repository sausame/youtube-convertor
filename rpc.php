<?php

class Rpc
{
	const NO_ERROR = 0;
	const UNKNOWN_ERROR = -1;

	public static function onResult($data, $code, $message) {
		return '{"jsonrpc" : "2.0", "error" : {"code": ' . $code . ', "message": "' . $message . '"}, "data": ' . $data . '}';
	}

	public static function onSucceed($data=NULL) {
		return self::onResult($data, self::NO_ERROR, null);
	}

	public static function onError($data=NULL) {
		return self::onResult($data, self::UNKNOWN_ERROR, null);
	}
}

?>


<?php

namespace phpseclib\Crypt;

class Seeder {

	public static function getSeed() {
		$data = \Zoon\Redis\RedisProvider::getInstance()->get('phpseclib::seeder');
		if ($data) {
			$data = unserialize($data);
		} else {
			$data = ['seed' => null, 'count' => 0];
		}

		$seed = pack('H*', sha1(
			(isset($_SERVER) ? phpseclib_safe_serialize($_SERVER) : '') .
			(isset($_POST) ? phpseclib_safe_serialize($_POST) : '') .
			(isset($_GET) ? phpseclib_safe_serialize($_GET) : '') .
			(isset($_COOKIE) ? phpseclib_safe_serialize($_COOKIE) : '') .
			phpseclib_safe_serialize($GLOBALS) .
			phpseclib_safe_serialize($data) .
			phpseclib_safe_serialize($data)
		));
		$data['seed'] = $seed;
		$data['count']++;
		
		\Zoon\Redis\RedisProvider::getInstance()->setex('phpseclib::seeder', 10 * 86400, serialize($data));

		return $seed;
	}

	public static function _original_getSeed() {
		// save old session data
		$old_session_id = session_id();
		$old_use_cookies = ini_get('session.use_cookies');
		$old_session_cache_limiter = session_cache_limiter();
		$_OLD_SESSION = isset($_SESSION) ? $_SESSION : false;
		if ($old_session_id != '') {
			session_write_close();
		}

		session_id(1);
		ini_set('session.use_cookies', 0);
		session_cache_limiter('');
		session_start();

		$v = $seed = $_SESSION['seed'] = pack('H*', sha1(
			(isset($_SERVER) ? phpseclib_safe_serialize($_SERVER) : '') .
			(isset($_POST) ? phpseclib_safe_serialize($_POST) : '') .
			(isset($_GET) ? phpseclib_safe_serialize($_GET) : '') .
			(isset($_COOKIE) ? phpseclib_safe_serialize($_COOKIE) : '') .
			phpseclib_safe_serialize($GLOBALS) .
			phpseclib_safe_serialize($_SESSION) .
			phpseclib_safe_serialize($_OLD_SESSION)
		));
		if (!isset($_SESSION['count'])) {
			$_SESSION['count'] = 0;
		}
		$_SESSION['count'] ++;

		session_write_close();

		// restore old session data
		if ($old_session_id != '') {
			session_id($old_session_id);
			session_start();
			ini_set('session.use_cookies', $old_use_cookies);
			session_cache_limiter($old_session_cache_limiter);
		} else {
			if ($_OLD_SESSION !== false) {
				$_SESSION = $_OLD_SESSION;
				unset($_OLD_SESSION);
			} else {
				unset($_SESSION);
			}
		}

		return $seed;
	}

}

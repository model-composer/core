<?php namespace Model\Core;

use Composer\InstalledVersions;
use Model\Config\Config;

class Model
{
	private static bool $initialized = false;

	public static function init(): void
	{
		if (self::$initialized)
			return;

		DEFINE('START_TIME', microtime(true));

		Config::loadEnv();

		$config = self::getConfig();
		if (!defined('APP_NAME'))
			define('APP_NAME', $config['name']);
		if (!defined('PATH'))
			define('PATH', $config['path']);

		define('INCLUDE_PATH', realpath(dirname(__FILE__) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..') . DIRECTORY_SEPARATOR);
		define('PATHBASE', substr(INCLUDE_PATH, 0, -strlen(PATH)));

		if (isset($_COOKIE['ZKADMIN']) and $_COOKIE['ZKADMIN'] == '69')
			define('DEBUG_MODE', 1);
		else
			define('DEBUG_MODE', (int)($config['debug'] ?? false));

		define('ZK_LOADING_ID', substr(md5(microtime()), 0, 16));

		if (!defined('HTTPS')) {
			if ((!empty($_SERVER['HTTPS']) and $_SERVER['HTTPS'] !== 'off') or ($_SERVER['SERVER_PORT'] ?? null) == 443)
				define('HTTPS', 1);
			else
				define('HTTPS', 0);
		}

		if (!defined('BASE_HOST'))
			define('BASE_HOST', (HTTPS ? 'https' : 'http') . '://' . ($_SERVER['HTTP_HOST'] ?? ''));

		self::$initialized = true;
	}

	public static function cleanUp(): void
	{
		if (InstalledVersions::isInstalled('model/cache'))
			\Model\Cache\Cache::invalidate();
	}

	public static function getConfig(): array
	{
		return Config::get('core', function () {
			return [
				'name' => defined('APP_NAME') ? APP_NAME : '',
				'path' => defined('PATH') ? PATH : '/',
				'debug' => defined('MAIN_DEBUG_MODE') and (bool)MAIN_DEBUG_MODE,
			];
		});
	}
}

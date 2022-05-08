<?php namespace Model\Core;

use Composer\InstalledVersions;
use Model\Config\Config;

class Model
{
	public static function init(): void
	{
		Config::loadEnv();

		$config = self::getConfig();
		if (!defined('APP_NAME'))
			define('APP_NAME', $config['name']);
		if (!defined('PATH'))
			define('PATH', $config['path']);
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
			];
		});
	}
}

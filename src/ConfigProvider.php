<?php namespace Model\Core;

use Model\Config\AbstractConfigProvider;

class ConfigProvider extends AbstractConfigProvider
{
	public static function migrations(): array
	{
		return [
			[
				'version' => '0.2.0',
				'migration' => function (array $config, string $env) {
					if ($config) // Already existing
						return $config;

					return [
						'name' => defined('APP_NAME') ? APP_NAME : '',
						'path' => defined('PATH') ? PATH : '/',
						'debug' => defined('MAIN_DEBUG_MODE') and (bool)MAIN_DEBUG_MODE,
					];
				},
			],
			[
				'version' => '0.2.3',
				'migration' => function (array $config, string $env) {
					$config['force_https'] = defined('HTTPS') && (bool)HTTPS;
					$config['force_www'] = defined('FORCE_WWW') && (bool)FORCE_WWW;
					return $config;
				},
			],
		];
	}
}

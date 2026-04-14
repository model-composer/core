<?php namespace Model\Core\Providers;

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
			[
				'version' => '0.3.15',
				'migration' => function (array $config, string $env) {
					if (!array_key_exists('debug_cookie_secret', $config))
						$config['debug_cookie_secret'] = '{{env.DEBUG_COOKIE|' . bin2hex(random_bytes(32)) . '}}';
					return $config;
				},
			],
			[
				'version' => '0.3.17',
				'migration' => function (array $config, string $env) {
					if (!array_key_exists('allowed_hosts', $config))
						$config['allowed_hosts'] = [];
					return $config;
				},
			],
		];
	}
}

<?php namespace Model\Core;

use Composer\InstalledVersions;
use Model\Config\Config;

class Model
{
	private static bool $initialized = false;
	private static array $inputVarsCache;

	public static function init(): void
	{
		if (self::$initialized)
			return;

		define('START_TIME', microtime(true));

		define('INCLUDE_PATH', realpath(dirname(__FILE__) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..') . DIRECTORY_SEPARATOR);

		Config::loadEnv();

		$config = self::getConfig();

		define('APP_NAME', $config['name']);
		define('PATH', $config['path']);
		define('PATHBASE', substr(INCLUDE_PATH, 0, -strlen(PATH)));

		if (isset($_COOKIE['ZKADMIN']) and $_COOKIE['ZKADMIN'] == '69')
			define('DEBUG_MODE', 1);
		else
			define('DEBUG_MODE', (int)($config['debug'] ?? false));

		define('ZK_LOADING_ID', substr(md5(microtime()), 0, 16));

		if ((!empty($_SERVER['HTTPS']) and $_SERVER['HTTPS'] !== 'off') or ($_SERVER['SERVER_PORT'] ?? null) == 443)
			define('HTTPS', 1);
		else
			define('HTTPS', $config['force_https']);

		if (!defined('BASE_HOST'))
			define('BASE_HOST', (HTTPS ? 'https' : 'http') . '://' . ($_SERVER['HTTP_HOST'] ?? ''));

		error_reporting(E_ALL);
		ini_set('display_errors', DEBUG_MODE);

		mb_internal_encoding('utf-8');

		if (!self::isCLI()) {
			if (
				$config['force_www']
				and !str_starts_with($_SERVER['HTTP_HOST'], 'www.')
				and !str_starts_with($_SERVER['HTTP_HOST'], 'localhost')
				and !str_starts_with($_SERVER['HTTP_HOST'], '127.0.0.1')
				and !str_starts_with($_SERVER['HTTP_USER_AGENT'] ?? '', 'curl')
			) {
				header('Location: http' . (HTTPS ? 's' : '') . '://www.' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
				exit;
			}

			header('Content-type: text/html; charset=utf-8');
			setcookie('ZK', PATH, time() + (60 * 60 * 24 * 365), PATH);
		}

		if (DEBUG_MODE and function_exists('opcache_reset'))
			opcache_reset();

		if (!isset($_SESSION))
			$_SESSION = [];

		self::$initialized = true;
	}

	public static function cleanUp(): void
	{
		if (InstalledVersions::isInstalled('model/cache'))
			\Model\Cache\Cache::invalidate();
	}

	/**
	 * Return post (or CLI) payload
	 *
	 * @return array|null
	 */
	public static function getInput(): ?array
	{
		if (self::isCLI()) {
			if (!isset(self::$inputVarsCache)) {
				self::$inputVarsCache = [];

				global $argv;

				if (is_array($argv) and count($argv) > 2) {
					$arr = $argv;
					unset($arr[0]); // Script name
					unset($arr[1]); // Main request (accessible via getRequest method)

					foreach ($arr as $input) {
						$input = explode('=', $input);
						if (count($input) === 2)
							self::$inputVarsCache[$input[0]] = $input[1];
					}
				}
			}

			return self::$inputVarsCache;
		} else {
			$contentType = self::getRequestContentType();
			if ($contentType and in_array($contentType, ['application/json', 'text/json'])) {
				$payload = file_get_contents('php://input');
				if (empty($payload))
					$payload = '{}';

				return json_decode($payload, true, 512, JSON_THROW_ON_ERROR);
			} else {
				$mp = @(new \Djiele\Http\MultipartHandler());
				if ($mp->getBoundary()) {
					$mp->populateGlobals();
					return json_decode($_POST['body'] ?? '{}', true, 512, JSON_THROW_ON_ERROR);
				} else {
					return $_REQUEST;
				}
			}
		}
	}

	/**
	 * @return string|null
	 */
	private static function getRequestContentType(): ?string
	{
		$headers = getallheaders();
		foreach ($headers as $k => $v) {
			if (mb_strtolower($k) === 'content-type')
				return $v;
		}

		return null;
	}

	/**
	 * Returns true if executed via CLI, false otherwise
	 *
	 * @return bool
	 */
	public static function isCLI(): bool
	{
		return (php_sapi_name() == "cli");
	}

	public static function getConfig(): array
	{
		return Config::get('core', [
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
		]);
	}
}

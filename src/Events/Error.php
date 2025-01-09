<?php namespace Model\Core\Events;

use Model\Events\AbstractEvent;

class Error extends AbstractEvent
{
	public ?string $errcode = null;
	public ?array $backtrace = null;

	public function __construct(public int $errno, public string $errstr, public ?string $errfile = null, public ?int $errline = null)
	{
		$this->errcode = match ($this->errno) {
			E_ERROR => 'E_ERROR',
			E_WARNING => 'E_WARNING',
			E_PARSE => 'E_PARSE',
			E_NOTICE => 'E_NOTICE',
			E_CORE_ERROR => 'E_CORE_ERROR',
			E_CORE_WARNING => 'E_CORE_WARNING',
			E_COMPILE_ERROR => 'E_COMPILE_ERROR',
			E_COMPILE_WARNING => 'E_COMPILE_WARNING',
			E_USER_ERROR => 'E_USER_ERROR',
			E_USER_WARNING => 'E_USER_WARNING',
			E_USER_NOTICE => 'E_USER_NOTICE',
			E_RECOVERABLE_ERROR => 'E_RECOVERABLE_ERROR',
			E_DEPRECATED => 'E_DEPRECATED',
			E_USER_DEPRECATED => 'E_USER_DEPRECATED',
			E_ALL => 'E_ALL',
			default => null,
		};

		$this->backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
		array_shift($this->backtrace);
	}

	public function getData(): array
	{
		return [
			'number' => $this->errno,
			'code' => $this->errcode,
			'str' => $this->errstr,
			'file' => $this->errfile,
			'line' => $this->errline,
			'backtrace' => $this->backtrace,
		];
	}
}

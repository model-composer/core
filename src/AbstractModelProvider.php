<?php namespace Model\Core;

abstract class AbstractModelProvider
{
	public static function realign(): void
	{
	}

	public static function getDependencies(): array
	{
		return [];
	}
}

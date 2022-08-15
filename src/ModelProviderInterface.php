<?php namespace Model\Core;

interface ModelProviderInterface
{
	public static function realign(): void;

	public static function getDependencies(): array;
}

<?php

declare(strict_types=1);

namespace PHPWind\Config;

use PHPWind\Exception\InvalidConfigurationException;

final class ConfigLoader
{
    public static function fromArray(array $config): PHPWindConfig
    {
        $config = PHPWindConfig::fromArray($config);
        $config->validate();

        return $config;
    }

    /**
     * @throws InvalidConfigurationException
     */
    public static function load(string $file): PHPWindConfig
    {
        if (!is_file($file) || !is_readable($file)) {
            throw new InvalidConfigurationException("Config file not found or unreadable: {$file}");
        }

        $config = require $file;

        if (!is_array($config)) {
            throw new InvalidConfigurationException("Config file must return an array: {$file}");
        }

        return self::fromArray($config);
    }
}

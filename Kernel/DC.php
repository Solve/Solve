<?php
/*
 * This file is a part of Solve framework.
 *
 * @author Alexandr Viniychuk <alexandr.viniychuk@icloud.com>
 * @copyright 2009-2014, Alexandr Viniychuk
 * created: 02.11.14 11:25
 */

namespace Solve\Kernel;

use Solve\Config\Config;
use Solve\Config\ConfigService;
use Solve\Logger\Logger;
use Solve\DependencyInjection\DependencyContainer;
use Solve\Utils\Inflector;

/**
 * Class DC
 * @package Solve\Kernel
 *
 * Class DC is a helper class to access main instance of dependency container
 *
 * @method static Logger getLogger() returns logger
 * @method static Config getProjectConfig($deepKey = null) returns logger
 *
 * @version 1.0
 * @author Alexandr Viniychuk <alexandr.viniychuk@icloud.com>
 */
class DC {

    /**
     * @var DependencyContainer
     */
    static private $_dependencyContainerInstance;

    public static function setInstance(DependencyContainer $dc) {
        self::$_dependencyContainerInstance = $dc;
    }

    public static function get($dependency) {
        return self::$_dependencyContainerInstance->get($dependency);
    }

    public static function getConfig($name, $deepKey = null) {
        return $deepKey ? ConfigService::getConfig($name)->get($deepKey) : ConfigService::getConfig($name);
    }

    public static function __callStatic($method, $params) {
        if (substr($method, 0, 3) == 'get') {
            if (substr($method, -6) == 'Config') {
                return static::getConfig(strtolower(substr($method, 3, -6)), empty($params[0]) ? null : $params[0]);
            }
            $dependency = Inflector::underscore(substr($method, 3));
            return static::get($dependency);
        }
    }

}
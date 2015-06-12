<?php
/*
 * This file is a part of Solve framework.
 *
 * @author Alexandr Viniychuk <alexandr.viniychuk@icloud.com>
 * @copyright 2009-2014, Alexandr Viniychuk
 * created: 02.11.14 11:25
 */

namespace Solve\Kernel;

use Solve\Application\Application;
use Solve\Autoloader\Autoloader;
use Solve\Config\Config;
use Solve\Config\ConfigService;
use Solve\EventDispatcher\EventDispatcher;
use Solve\Http\Response;
use Solve\Logger\Logger;
use Solve\DependencyInjection\DependencyContainer;
use Solve\Router\Router;
use Solve\Security\SecurityService;
use Solve\Storage\ArrayStorage;
use Solve\View\View;

/**
 * Class DC
 * @package Solve\Kernel
 *
 * Class DC is a helper class to access main instance of dependency container
 *
 * @method static Application getApplication() returns logger
 * @method static Response getResponse() returns instance of response
 * @method static Logger getLogger() returns logger
 * @method static Router getRouter() returns router
 * @method static SecurityService getSecurity() returns security service
 * @method static View getView()
 * @method static Autoloader getAutoloader() returns router
 * @method static EventDispatcher getEventDispatcher() returns main instance of event dispatcher
 * @method static Config getProjectConfig($deepKey = null, $defaultValue = null) returns config
 * @method static Config getDatabaseConfig($deepKey = null, $defaultValue = null) returns config
 * @method static Config getSecurityConfig($deepKey = null, $defaultValue = null) returns config
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

    /**
     * @param $name
     * @param null $deepKey
     * @return ArrayStorage|Config
     */
    public static function getConfig($name, $deepKey = null, $defaultValue = null) {
        return $deepKey ? ConfigService::getConfig($name)->get($deepKey) : ConfigService::getConfig($name);
    }

    public static function getEnvironment() {
        return Kernel::getMainInstance()->getEnvironment();
    }

    public static function getContainer() {
        return self::$_dependencyContainerInstance;
    }

    public static function __callStatic($method, $params) {
        if (substr($method, 0, 3) == 'get') {
            if (substr($method, -6) == 'Config') {
                array_unshift($params, strtolower(substr($method, 3, -6)));
                return call_user_func_array(array('static', 'getConfig'), $params);
            }
            $dependency = lcfirst(substr($method, 3));
            return static::get($dependency);
        }
    }

}
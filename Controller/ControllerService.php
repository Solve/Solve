<?php
/*
 * This file is a part of Solve framework.
 *
 * @author Alexandr Viniychuk <alexandr.viniychuk@icloud.com>
 * @copyright 2009-2014, Alexandr Viniychuk
 * created: 11/3/14 4:08 PM
 */

namespace Solve\Controller;


use Solve\Kernel\DC;
use Solve\Utils\Inflector;

class ControllerService {

    private static $_activeNamespace;
    private static $_loadedControllers   = array();
    private static $_executedPreActions  = array();
    private static $_executedPostActions = array();

    /**
     * @param $controllerName
     * @param string|null $namespace
     * @return BaseController
     */
    public static function getController($controllerName, $namespace = null) {
        if (empty($namespace)) $namespace = self::$_activeNamespace;
        $fullControllerName = ucfirst($namespace) . '\\Controllers\\' . ucfirst(Inflector::camelize($controllerName));
        if (empty(self::$_loadedControllers[$fullControllerName])) {
            self::$_loadedControllers[$fullControllerName] = new $fullControllerName();
        }
        return self::$_loadedControllers[$fullControllerName];
    }

    public static function safeCall($controllerName, $actionName) {
        $instance = static::getController($controllerName);
        if (method_exists($instance, $actionName)) {
            $instance->{$actionName}();
        } else {
            static::fireRouteNotFound($controllerName, $actionName);
        }
    }

    public static function processControllerAction($controllerName, $actionName) {
        if (static::isControllerExists($controllerName)) {
            if (empty(static::$_executedPreActions[$controllerName])) {
                static::safeCall($controllerName, '_preAction');
                static::$_executedPreActions[$controllerName] = true;
            }
            static::safeCall($controllerName, $actionName);
            if (empty(static::$_executedPostActions[$controllerName])) {
                static::safeCall($controllerName, '_postAction');
                static::$_executedPostActions[$controllerName] = true;
            }
        } else {
            static::fireRouteNotFound($controllerName, $actionName);
        }
    }

    protected static function fireRouteNotFound($controllerName, $actionName) {
        DC::getEventDispatcher()->dispatchEvent('route.notFound');
        DC::getLogger()->add('Invalid action call:' . $controllerName . '->' . $actionName . '()');
    }

    public static function isControllerExists($controllerName, $namespace = null) {
        if (empty($namespace)) $namespace = self::$_activeNamespace;
        $fullControllerName = ucfirst($namespace) . '\\Controllers\\' . ucfirst(Inflector::camelize($controllerName));
        return class_exists($fullControllerName);
    }

    /**
     * @return mixed
     */
    public static function getActiveNamespace() {
        return self::$_activeNamespace;
    }

    /**
     * @param mixed $activeNamespace
     */
    public static function setActiveNamespace($activeNamespace) {
        self::$_activeNamespace = $activeNamespace;
    }

}
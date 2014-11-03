<?php
/*
 * This file is a part of Solve framework.
 *
 * @author Alexandr Viniychuk <alexandr.viniychuk@icloud.com>
 * @copyright 2009-2014, Alexandr Viniychuk
 * created: 11/3/14 1:43 PM
 */

namespace Solve\Application;


use Solve\Router\Route;
use Solve\Utils\Inflector;

class ApplicationRouter {

    private static $_instance;

    protected function __construct() {
    }

    public static function getInstance() {
        if (empty(self::$_instance)) {
            self::$_instance = new static();
        }
        return self::$_instance;
    }


}
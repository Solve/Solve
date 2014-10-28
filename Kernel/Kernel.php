<?php
/*
 * This file is a part of Solve framework.
 *
 * @author Alexandr Viniychuk <alexandr.viniychuk@icloud.com>
 * @copyright 2009-2014, Alexandr Viniychuk
 * created: 27.10.14 18:22
 */

namespace Solve\Kernel;


class Kernel {

    /**
     * @var Kernel
     */
    private static $_mainInstance;

    private $_dependencyContainer;

    public static function getMainInstance(DependencyContainer $dc = null) {
        if (empty(self::$_mainInstance)) {
            self::$_mainInstance = new static($dc);
        }
        return self::$_mainInstance;
    }

}
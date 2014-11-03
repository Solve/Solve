<?php
/*
 * This file is a part of Solve framework.
 *
 * @author Alexandr Viniychuk <alexandr.viniychuk@icloud.com>
 * @copyright 2009-2014, Alexandr Viniychuk
 * created: 11/3/14 5:45 PM
 */

namespace Solve\View\ViewEngine;

use Solve\View\View;

abstract class AbstractViewEngine {

    /**
     * @var View
     */
    protected $_view;

    public function __construct($view) {
        $this->_view = $view;
    }

    public function configure() {

    }

    abstract public function render();

}
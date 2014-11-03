<?php
/*
 * This file is a part of Solve framework.
 *
 * @author Alexandr Viniychuk <alexandr.viniychuk@icloud.com>
 * @copyright 2009-2014, Alexandr Viniychuk
 * created: 11/3/14 4:05 PM
 */

namespace Solve\Controller;


use Solve\Kernel\DC;
use Solve\View\View;

class BaseController {

    public $view;

    public function __construct() {
        $this->view = DC::getView();
    }

    public function _preAction() {
    }

    public function _postAction() {
    }

}
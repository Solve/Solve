<?php
/*
 * This file is a part of Solve framework.
 *
 * @author Alexandr Viniychuk <alexandr.viniychuk@icloud.com>
 * @copyright 2009-2014, Alexandr Viniychuk
 * created: 11/3/14 2:43 PM
 */

namespace Frontend\Controllers;

use Solve\Controller\BaseController;

class ApplicationController extends BaseController {

    public function _preAction() {
        echo "application pre action \n";
    }

    public function _postAction() {
        echo "application post action \n";
    }

}
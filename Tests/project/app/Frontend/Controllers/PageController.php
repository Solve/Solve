<?php
/*
 * This file is a part of Solve framework.
 *
 * @author Alexandr Viniychuk <alexandr.viniychuk@icloud.com>
 * @copyright 2009-2014, Alexandr Viniychuk
 * created: 11/4/14 5:28 PM
 */

namespace Frontend\Controllers;


use Solve\Controller\BaseController;

class PageController extends BaseController {

    public function _preAction() {
        echo "about controller pre \n";
    }

    public function aboutAction() {
        echo "action about \n";
    }

    public function _postAction() {
        echo "about controller post \n";
    }
}
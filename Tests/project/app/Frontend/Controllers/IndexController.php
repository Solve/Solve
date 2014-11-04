<?php
/*
 * This file is a part of Solve framework.
 *
 * @author Alexandr Viniychuk <alexandr.viniychuk@icloud.com>
 * @copyright 2009-2014, Alexandr Viniychuk
 * created: 11/3/14 1:37 PM
 */

namespace Frontend\Controllers;

use Solve\Controller\BaseController;
use Solve\Kernel\DC;
use Solve\Router\Router;
use Solve\View\View;

class IndexController extends BaseController {

    public function defaultAction() {
        $this->view->name = 'Alexandr';
        $this->view->setVar('name', 'AlexandrHTML', View::FORMAT_HTML);
        $this->view->setStandaloneTemplate('index/default');
    }

    public function testAction() {
        die('test');
    }

}
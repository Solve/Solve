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
use Solve\Database\QC;
use Solve\Kernel\DC;
use Solve\View\View;

class IndexTestController extends BaseController {

    public function _preAction() {
        echo "index controller pre \n";
    }

    public function defaultAction() {
        $this->view->name = 'Alexandr';
        $this->view->city = 'Kiev';
        var_dump(QC::create('users')->execute());
//        $this->view->setVar('name', 'AlexandrHTML', View::FORMAT_HTML);
//        $this->view->setNoLayout();
//        $this->view->setStandaloneTemplate('index/default');
//        echo "index default  \n";
//        $this->forwardToRoute('test');
//        $this->redirectToUri('about/');
//        $this->redirectSelf();
//        var_dump($this->view->fetchTemplate('index/default'));die();
    }

    public function testAction() {
        echo "index test  \n";
        $this->view->setResponseFormat(View::FORMAT_CONSOLE);
        $this->forwardToRoute('about');
    }

    public function _postAction() {
        echo "index controller post \n";
    }
}
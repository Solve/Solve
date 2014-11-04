<?php
/*
 * This file is a part of Solve framework.
 *
 * @author Alexandr Viniychuk <alexandr.viniychuk@icloud.com>
 * @copyright 2009-2014, Alexandr Viniychuk
 * created: 11/4/14 1:23 PM
 */

namespace Solve\Controller;


use Solve\View\View;

class JsonController extends BaseController {

    public function __construct() {
        parent::__construct();
        $this->view->setResponseFormat(View::FORMAT_JSON);
    }


}
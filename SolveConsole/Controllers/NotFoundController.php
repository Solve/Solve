<?php
/*
 * This file is a part of Solve framework.
 *
 * @author Alexandr Viniychuk <alexandr.viniychuk@icloud.com>
 * @copyright 2009-2014, Alexandr Viniychuk
 * created: 07.11.14 16:40
 */

namespace SolveConsole\Controllers;


use Solve\Controller\ConsoleController;
use Solve\Controller\ControllerService;

class NotFoundController extends ConsoleController {

    public static function notFoundAction() {
        echo "command not found\n\n";
        ControllerService::processControllerAction('IndexController', 'defaultAction');
    }

}
<?php
/*
 * This file is a part of Solve framework.
 *
 * @author Alexandr Viniychuk <alexandr.viniychuk@icloud.com>
 * @copyright 2009-2014, Alexandr Viniychuk
 * created: 07.11.14 15:52
 */

namespace SolveConsole\Controllers;

use Solve\Controller\ConsoleController;

class IndexController extends ConsoleController {

    public function defaultAction() {
        $this->writeln("Solve console tools v0.1.");
        $this->message("Kernel's ready.");
    }

}
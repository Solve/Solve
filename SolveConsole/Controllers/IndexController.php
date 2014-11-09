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
use Solve\DocComment\DocComment;
use Solve\Utils\Inflector;

class IndexController extends ConsoleController {

    public function defaultAction() {
        $this->writeln('Available commands:');
        $controllers  = array(
            'SolveConsole\\Controllers\\DbController',
            'SolveConsole\\Controllers\\GenController',
        );
        $commandsList = '';
        foreach ($controllers as $controllerName) {
            $r    = new \ReflectionClass($controllerName);
            $name = Inflector::underscore(substr($r->getShortName(), 0, -10));
            $help = DocComment::parseFromString($r->getDocComment())->getAnnotationsAsString('help');
            $commandsList .= '  <bold>' . $name . "</bold>\t" . $help ."\n";
        }
        $this->writeln($commandsList);

    }

}
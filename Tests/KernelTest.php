<?php
/*
 * This file is a part of Solve framework.
 *
 * @author Alexandr Viniychuk <alexandr.viniychuk@icloud.com>
 * @copyright 2009-2014, Alexandr Viniychuk
 * created: 27.10.14 18:24
 */

namespace Solve\Tests;

require_once __DIR__ . '/../Kernel/Kernel.php';
require_once __DIR__ . '/../Kernel/Environment.php';
require_once __DIR__ . '/../vendor/autoload.php';

use Solve\Kernel\Kernel;

class KernelTest extends \PHPUnit_Framework_TestCase {

    public function testBasic() {
        /**
         * Kernel
         */
        $kernel = Kernel::getProjectInstance();
        $env = $kernel->getEnvironment();
        var_dump($env);die();
    }

}

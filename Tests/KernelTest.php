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
require_once __DIR__ . '/../Kernel/DC.php';
require_once __DIR__ . '/../Application/Application.php';
require_once __DIR__ . '/../Environment/Environment.php';
require_once __DIR__ . '/../vendor/autoload.php';

use Solve\Config\ConfigService;
use Solve\Kernel\DC;
use Solve\Kernel\Kernel;

class KernelTest extends \PHPUnit_Framework_TestCase {

    public function testBasic() {
        /**
         * Kernel
         */
        $kernel = Kernel::getMainInstance();
        $env = $kernel->getEnvironment();
        $this->assertEquals(realpath(__DIR__ . '/../../../') . '/', $env->getProjectRoot(), 'root detected correctly');
        $kernel->getEnvironment()->setProjectRoot(realpath(__DIR__ . '/project/') . '/', true);
        $kernel->onEnvironmentUpdate();
        $this->assertEquals('Test project', DC::getProjectConfig('name'));

        $kernel->run();
    }

}

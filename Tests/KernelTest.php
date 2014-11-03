<?php
/*
 * This file is a part of Solve framework.
 *
 * @author Alexandr Viniychuk <alexandr.viniychuk@icloud.com>
 * @copyright 2009-2014, Alexandr Viniychuk
 * created: 27.10.14 18:24
 */

namespace Solve\Tests;

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../Kernel/Kernel.php';
require_once __DIR__ . '/../Kernel/DC.php';
require_once __DIR__ . '/../Application/Application.php';
require_once __DIR__ . '/../Controller/BaseController.php';
require_once __DIR__ . '/../Controller/ControllerService.php';
require_once __DIR__ . '/../Router/ApplicationRoute.php';
require_once __DIR__ . '/../Environment/Environment.php';
require_once __DIR__ . '/../View/View.php';
require_once __DIR__ . '/../View/RenderEngine/BaseRenderEngine.php';
require_once __DIR__ . '/../View/RenderEngine/SlotRenderEngine.php';

use Solve\Config\ConfigService;
use Solve\Kernel\DC;
use Solve\Kernel\Kernel;
use Solve\Utils\FSService;

class KernelTest extends \PHPUnit_Framework_TestCase {

    public function testBasic() {
        $this->buildTestStructure();
        /**
         * Kernel
         */
        $kernel = Kernel::getMainInstance();
        $env = $kernel->getEnvironment();
        $this->assertEquals(realpath(__DIR__ . '/../../../') . '/', $env->getProjectRoot(), 'root detected correctly');
        $kernel->getEnvironment()->setProjectRoot(realpath(__DIR__ . '/project/') . '/', true);
        $kernel->onEnvironmentUpdate();
        $this->assertEquals('Test project', DC::getProjectConfig('name'));
        $_SERVER['REQUEST_URI'] = '/';
        $_SERVER['QUERY_STRING'] = '';
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['DOCUMENT_ROOT'] = $env->getWebRoot();

        $kernel->run();
    }

    public function buildTestStructure() {
        FSService::makeWritable(array(
            __DIR__ . '/project/app/Frontend/Controllers',
            __DIR__ . '/project/app/Frontend/Views',
            __DIR__ . '/project/app/Admin',
            __DIR__ . '/project/src/classes',
            __DIR__ . '/project/src/db',
            __DIR__ . '/project/src/helpers',
            __DIR__ . '/project/src/libs',
            __DIR__ . '/project/tmp/log',
            __DIR__ . '/project/tmp/cache',
        ));
    }

}

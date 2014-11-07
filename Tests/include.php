<?php
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../Kernel/Kernel.php';
require_once __DIR__ . '/../Kernel/DC.php';
require_once __DIR__ . '/../Kernel/PackagesConfigurator.php';
require_once __DIR__ . '/../Application/Application.php';
require_once __DIR__ . '/../Application/ConsoleApplication.php';
require_once __DIR__ . '/../Controller/BaseController.php';
require_once __DIR__ . '/../Controller/JsonController.php';
require_once __DIR__ . '/../Controller/ControllerService.php';
require_once __DIR__ . '/../Router/ApplicationRoute.php';
require_once __DIR__ . '/../Router/ConsoleRequest.php';
require_once __DIR__ . '/../Environment/Environment.php';
require_once __DIR__ . '/../View/View.php';
require_once __DIR__ . '/../View/RenderEngine/BaseRenderEngine.php';
require_once __DIR__ . '/../View/RenderEngine/SlotRenderEngine.php';
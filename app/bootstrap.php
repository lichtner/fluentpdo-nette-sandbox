<?php

/**
 * My Application bootstrap file.
 */
use Nette\Application\Routers\Route;


// Load Nette Framework
require LIBS_DIR . '/nette/Nette/loader.php';


// Configure application
$configurator = new Nette\Config\Configurator;

// Enable Nette Debugger for error visualisation & logging
//$configurator->setDebugMode($configurator::AUTO);
$configurator->enableDebugger(__DIR__ . '/../log');

// Enable RobotLoader - this will load all classes automatically
$configurator->setTempDirectory(__DIR__ . '/../temp');
$configurator->createRobotLoader()
	->addDirectory(APP_DIR)
	->addDirectory(LIBS_DIR)
	->register();

// Create Dependency Injection container from config.neon file
$configurator->addConfig(__DIR__ . '/config/config.neon');
$container = $configurator->createContainer();

// connect FluentPDO panel
$container->fpdo->debug = function($FluentQuery) {
	FluentPDOPanel::getInstance()->logQuery($FluentQuery);
};

# connect NotORM panel
$panel = NotORMPanel::getInstance();
$panel->setPlatform($container->pdo->getAttribute(PDO::ATTR_DRIVER_NAME));
\Nette\Diagnostics\Debugger::addPanel($panel);

$container->notorm->debug = function($query, $parameters) {
	NotORMPanel::getInstance()->logQuery($query, $parameters);
};

// Setup router
$container->router[] = new Route('index.php', 'Homepage:default', Route::ONE_WAY);
$container->router[] = new Route('<presenter>/<action>[/<id>]', 'Homepage:default');


// Configure and run the application!
$container->application->run();

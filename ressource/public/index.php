<?php

const PROJECT_VENDOR = "SBPGames";
const PROJECT_PATH = "../..";
const PROJECT_RESOURCES_PATH = PROJECT_PATH."/ressource";
const DEVELOPMENT_MODE = true;

// Adds a classes autoloader.
require PROJECT_PATH."/src/autoloader.php";

spl_autoload_register(function(string $class){
	\SBPGames\Autoloader\loadClass($class,
		PROJECT_PATH, PROJECT_VENDOR, DEVELOPMENT_MODE
	);
});

// Initializes web system.
$ws = new \SBPGames\Framework\WebSystem(
	new SBPGames\Framework\Service\ServiceContainer(
		new \SBPGames\Framework\Service\ServiceConfig(PROJECT_RESOURCES_PATH),
		[
			\SBPGames\Framework\Service\Database\DatabaseService::class
		]
	),
	[
		\SBPGames\Main\MainApp::class
	]
);
$response = new \SBPGames\Framework\Message\Response();
$request = \SBPGames\Framework\Message\ServerRequest::fromGlobals();

if($request->getUri()->getHost() === "127.0.0.1")
	$request = $request->withUri(
		$request->getUri()->withHost("sbpgames.fr")
	);


// Processes current request with the web system.
$response = $ws->processRequest($request, $response);

// Sends web system's response.
$response->write(\SBPGames\Framework\Message\FileStream::fromOutput());
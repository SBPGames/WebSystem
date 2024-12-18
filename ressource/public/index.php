<?php

const PROJECT_VENDOR = "SBPGames";
const PROJECT_PATH = "../..";
const DEVELOPMENT_MODE = true;

// Adding a classes autoloader.
require PROJECT_PATH."/src/autoloader.php";

spl_autoload_register(function(string $class){
	\SBPGames\Autoloader\loadClass($class,
		PROJECT_PATH, PROJECT_VENDOR, DEVELOPMENT_MODE
	);
});
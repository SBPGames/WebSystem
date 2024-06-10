<?php

const PROJECT_PATH = "../..";
const DEVELOPMENT_MODE = true;

// Adding a classes autoloader.
require PROJECT_PATH."/src/autoload.php";

spl_autoload_register(function(string $class){
	\SBPGames\Autoloader\loadClass($class, PROJECT_PATH, DEVELOPMENT_MODE);
});
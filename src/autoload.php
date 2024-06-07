<?php

const PROJECT_VENDOR = "SBPGames";
const SOURCES_FOLDER = "src";
const LIBRARIES_FOLDER = "lib";

// TODO: Make it a preprocessing operation (Like in composer).
/** Retrieves all folder names in the librairies folder (LIBRARIES_FOLDER) if
 * exists, searched relatively from the given `$path`, and if a namespace's
 * vendor name (PHP, PSR-1 and PSR-4). **These folders should be treated as
 * installed library vendors.**
 *
 * @deprecated
 * @param string $path A path from where the librairies folder
 * (LIBRARIES_FOLDER) can be found.
 * @return array A array of allowed folder names (Installed library vendors)
 * found under the librairies folder (LIBRARIES_FOLDER) - empty if the latter
 * folder is not found or can't be read.
 */
function getLibrairiesVendors(string $path): array{
	$path = rtrim($path, "/").LIBRARIES_FOLDER;
	$vendors = [];

	if(is_dir($path) && is_readable($path))
		foreach(scandir($path) as $vendor)
			if(is_dir($vendor))
				array_push($vendors, $vendor);

	return $vendors;
}

/** Loads once a file (Like `require_once` do) containing the class called
 * `$classname`. The file is searched relatively from the given `$path` in the
 * sources folder (SOURCES_FOLDER) - if the class is not a library (having
 * PROJECT_VENDOR at namespace's top-level) - or in the librairies folder
 * (LIBRARIES_FOLDER).
 *
 * @param string $classname The name of the class to be loaded, indicating by
 * its namespace the path of its file relative to one of the specific folders.
 * @param string $path A path from where specific folders, where the file is
 * searched, can be found.
 * @return void
 * @throws UnexpectedValueException If `$classname` doesn't have a (valid)
 * vendor name as namespace's top-level (PHP, PSR-1 and PSR-4).
 */
function loadClass(string $classname, string $path): void{

}
<?php

namespace SBPGames\Autoloader;

const PROJECT_VENDOR = "SBPGames";
const SOURCES_FOLDER = "src";
const LIBRARIES_FOLDER = "lib";

/** Retrieves all folder names in the librairies folder (LIBRARIES_FOLDER) if
 * exists, searched relatively from the given `$path`, and if it has a vendor
 * namespace (PHP, PSR-1 and PSR-4). **These folders should be treated as
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
 * PROJECT_VENDOR vendor as namespace prefix) - or in the librairies folder
 * (LIBRARIES_FOLDER).
 * 
 * **NOTE:** This function catch `require_once`'s errors by defining a new
 * error handler, and restoring the previous one at the end. So, some
 * additionnal exceptions are defined to replace some of its catched errors.
 * 
 * **STANDARD:** PSR-4; Additionnal exceptions and errors can be desactivated
 * with `$throwing`. Moreover, underscores aren't allowed in namespace or class
 * names.
 *
 * @param string $class The fully qualified name of the class to be loaded,
 * indicating by its namespace the path of its file relative to one of the
 * specific folders.
 * @param string $path A path from where specific folders, where the file is
 * searched, can be found.
 * @param string $throwing Indicates if the method should throw exceptions and
 * errors on purpose (Set it to false to follow PSR-4 standard).
 * @return void
 * @throws UnexpectedValueException
 * 		- If `$class` isn't a valid fully qualified class name (PHP, PSR-1
 * 		and PSR-4);
 * 		- Or if any file containing the class called `$classname` can't be found
 *		in one of the specific folders.
 */
function loadClass(string $class, string $path, bool $throwing = true): void{

}
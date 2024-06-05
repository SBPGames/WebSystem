<?php

const PROJECT_VENDOR = "PetRescue";
const SOURCES_FOLDER = "src";
const LIBRARIES_FOLDER = "lib";

// TODO: Make it a preprocessing operation (Like in composer).
/** Retrieves all folder names in the librairies folder (LIBRARIES_FOLDER) if
 * exists, searched relatively from the given `$path`, and if allowed as a
 * namespace vendor atom (PHP, PSR-1 and PSR-4). **These folders should be
 * treated as installed library vendors.**
 * @param string $path A relative path from where the librairies folder
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

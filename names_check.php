<?php
/* photo organization - names check
 * Written by Nathaniel Rudd (deafears@naterudd.com)
 * Project Home Page: http://github.com/naterudd/photo_organization
 * Copyright 2017 Nathaniel Rudd
 *
 * This file is part of photo organization.
 *
 * photo organization is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 */

require_once("getid3/getid3.php");

// Find the name of the set from the folder
$current_folder = basename(getcwd());

$dirHandle = opendir(getcwd());
while($file = readdir($dirHandle)){ if ($file!="."&&$file!="..") {

	// Get exif date taken
	$getID3 = new getID3;
	$info=$getID3->analyze($file);
	if ($info['fileformat']=='mp4'&&isset($info['tags']['quicktime']['creation_date'][0])) {
		$file_date=strtotime($info['tags']['quicktime']['creation_date'][0]);
	} elseif ($info['fileformat']=='jpg'&&isset($info['jpg']['exif']['EXIF']['DateTimeOriginal'])) {
		$file_date=strtotime($info['jpg']['exif']['EXIF']['DateTimeOriginal']);
	} else {
		// No date found in the file, cancel out
// 		echo "No date found in the file, reverting to file creation time.\n";
		$file_date=(filemtime($file)<filectime($file))?filemtime($file):filectime($file);
	}

	if (!$file_date) { 
		echo "File date still not valid.\n";
		exit;
	}
	
	list($orig_date,$orig_time,$postname)=explode("_", $file,3);
	$new_file=date("Ymd_His",$file_date)."_$postname";
	
	
	if ($file!=$new_file) {
		echo "$file - should be $new_file\n";
	
		// Move the file
		$rename_success=rename($file,$new_file);
	
		// Rename didn't work, cancel out
		if (!$rename_success) { 
			echo "Renaming was unsuccessful.\n";
			exit;		
		}

		// Change file creation to match exif date taken
		exec("SetFile -d '".date('m/d/Y H:i:s',$file_date)."' ".escapeshellarg($new_file));

		// Change file modification to match exif date taken
		touch($new_file,$file_date); // touch -t
		
	}


}}

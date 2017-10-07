<?php
/* photo organization - photo organize
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

require_once("config.php");
require_once("getid3/getid3.php");

$files=readDirs($photo_organize_drop_base_path);
sort($files);
chdir("/");

foreach ($files as $file) {
	//MARK Organize a file

	$filename = basename($file);
	echo "$filename";

	// MARK - Gather file information
	$getID3 = new getID3;
	$info=$getID3->analyze($file);
	
	// MARK -- Gather date
	if (key_exists('fileformat', $info)&&($info['fileformat']=='mp4'||$info['fileformat']=='quicktime')&&isset($info['tags']['quicktime']['creation_date'][0])) {
		$file_date=strtotime($info['tags']['quicktime']['creation_date'][0]);
	} elseif (key_exists('fileformat', $info)&&$info['fileformat']=='jpg'&&isset($info['jpg']['exif']['EXIF']['DateTimeOriginal'])) {
		$file_date=strtotime($info['jpg']['exif']['EXIF']['DateTimeOriginal']);
	} else {
		$file_date=(filemtime($file)<filectime($file))?filemtime($file):filectime($file);
		if ($info['fileformat']!="png") {
			$log[$file]['minorerror'][]="Can't find a date in file tags, used ".date("Y-m-d H:i:s",$file_date);
		}
	}

	if (!$file_date) { 
		echo "File date not valid.\n";
		exit;
	}
	
	// MARK - Copy to organized location

	// MARK -- Verify / Create file organized container directory
	if (!is_dir($photo_organize_organized_base_path."/".date('ym',$file_date))) {
		mkdir($photo_organize_organized_base_path."/".date('ym',$file_date),0777);
	}
	
	// MARK -- Copy the file
	$new_file=$photo_organize_organized_base_path."/".date('ym',$file_date)."/".date("Ymd_His_",$file_date).$filename;
	$success = copy($file,$new_file);
	
	if ($success) {
		// MARK -- Finish logging
		$log[$file]['success']=true;
		
		// MARK -- Move the original file to completed
		$path_parts=pathinfo($file);
		if (!is_dir($path_parts['dirname']."/completed")) {
			mkdir($path_parts['dirname']."/completed",0777);
		}
		rename($file, $path_parts['dirname']."/completed/".$filename);
	
		echo "\n";
	} else {
		// MARK - Error organizing
		echo " - ERROR ORGANIZING\n";
		$log[$file]['error']="Organization Failure.";
	}
	
}


// MARK Send off the log
$success="";$failure="";$minor="";$unexpected_types="";
if (count($log)) { foreach ($log as $f=>$l) {
	if (key_exists("error", $l)) { 
		$failure.="$f - <span style='color:#900;'>{$l['error']}</span><br/>\n";
	} else if ($f=="unexpected_types") {
		$unexpected_types=implode(",",array_flip($l));
	} else { $success.="$f<br/>\n";}
}}
if ($success!=""||$failure!=""||$minor!=""||$unexpected_types!="") {
	$body=($failure!="")?"<br/><div style='color:#900;font-size:1.1em;font-weight:bold;'>Failure</div>\n<div>$failure</div>\n":"";
	$body.=($minor!="")?"<br/><div style='color:#c60;font-size:1.1em;font-weight:bold;'>Minor</div>\n<div>$minor</div>\n":"";
	$body.=($unexpected_types!="")?"<br/><div style='color:#999;font-size:1.1em;font-weight:bold;'>Unexpected Types</div>\n<div>$unexpected_types</div>\n":"";
	$body.=($success!="")?"<br/><div style='color:#090;font-size:1.1em;font-weight:bold;'>Success</div>\n<div>$success</div>\n":"";
	$headers = array( "From: $photo_organize_email_from", "MIME-Version: 1.0", "Content-type: text/html" );
	$rc = mail($photo_organize_email_to, "Photo Organization Results", $body, implode("\r\n", $headers) );
}


function readDirs($path){
	global $log, $photo_organize_acceptable_formats;
	$return_array=array();
	$dirHandle = opendir($path);
	while($file = readdir($dirHandle)){
		$getID3 = new getID3;
		$info=$getID3->analyze($path."/".$file);
		if(is_dir($path."/".$file) && $file!='.' && $file!='..' && $file!='completed'){
			$return_array=array_merge_recursive($return_array, readDirs($path."/".$file));
		} else if (key_exists('fileformat', $info)) { 
			if (key_exists($info['fileformat'],$photo_organize_acceptable_formats)) {
				$return_array[]=$path."/".$file;
			} else {
				if (count($log['unexpected_types'])) { if (!key_exists($info['fileformat'], $log['unexpected_types'])) {
					$log['unexpected_types'][$info['fileformat']]=1;
				}} else {
					$log['unexpected_types'][$info['fileformat']]=1;
				}
			}
		}
	}
	return $return_array;
}
<?php

// MARK Global variables
$email_address="myaddress@domain.com";

// MARK photo file variables
$photo_file_drop_base_path=getcwd()."/drop/";
$photo_file_organized_base_path=getcwd()."/organized/";
$photo_file_acceptable_formats=array('jpg'=>1,'mp4'=>1,'png'=>1,'quicktime'=>1);

$photo_file_email_from=str_replace("@", "+filer@", $email_address); // or a specific address "filer@domain.com";
$photo_file_email_to=str_replace("@", "+filer@", $email_address); // or a specific address "myaddress@domain.com", or the above variable $email_address;

?>
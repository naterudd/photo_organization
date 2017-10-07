<?php

// MARK Global variables
$email_address="myaddress@domain.com";

// MARK photo organize variables
$photo_organize_drop_base_path=getcwd()."/drop/";
$photo_organize_organized_base_path=getcwd()."/organized/";
$photo_organize_acceptable_formats=array('jpg'=>1,'mp4'=>1,'png'=>1,'quicktime'=>1);

$photo_organize_email_from=str_replace("@", "+organizer@", $email_address); // or a specific address "organizer@domain.com";
$photo_organize_email_to=str_replace("@", "+organizer@", $email_address); // or a specific address "myaddress@domain.com";, or the above variable $email_address;

?>
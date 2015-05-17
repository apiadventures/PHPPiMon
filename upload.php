<?php
$dir = "/tmp/motion/*.jpg";
//get the list of all files with .jpg extension in the directory and safe it in an array named $images
$images = glob( $dir );

//extract only the name of the file without the extension and save in an array named $find
foreach( $images as $image ):
$output = exec("/home/pi/Dropbox-Uploader/dropbox_uploader.sh upload $image");
echo $output;
echo shell_exec("sudo rm $image");

endforeach;
?>

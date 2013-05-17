<?php


/**

Title       : HMA (Hide My Ass) Feeder     
Author      : Héctor Valverde <hvalverde at uma.es>
Description : This file is a script to feed the database
			  with proxies from www.hidemyass.com
Date        : Nov. 2012

*/


// ------ Chapter 1: Fuctions including and initial params ----------
// Reliable relative file path
$full_list_path			  = "full_list";
$total_list_to_be_checked = $full_list_path."/_full_list.txt";
$reliable_full_list 	  = $full_list_path."/_reliable_list.txt";
// No Planet Lab relative file path
$planetlab_full_list 	  = "full_list_nopl/_full_list.txt";
// ------------------------------------------------------------------
include_once("/home/hvalverde/proxy-raw/core/config.inc.php");
include_once(_ABS_CORE_PATH."/lib/functions.proxy-checker.php");
// Notifications
$email 					  = _ADMIN_MAIL;
$phone					  = _ADMIN_PHONE;
// ------------------------------------------------------------------


// ------ Chapter 2: Processing the compressed file -----------------
// Decompress the file and extract the list of files. Also the date
// ------------------------------------------------------------------
// Debugging with "proxylist-11-19-12.zip"
//$proxy_file_zip = _ABS_HMA_ZIP_PATH."proxylist-11-19-12.zip";
$proxy_file_zip = _ABS_HMA_ZIP_PATH.$argv[1];

// Explode the filename
   $name_file_zip = basename($proxy_file_zip,".zip");
$explode_file_zip = explode("-",$name_file_zip);

// Extract date form filename
$month_file_zip = $explode_file_zip[1];
  $day_file_zip = $explode_file_zip[2];
 $year_file_zip = $explode_file_zip[3];


// Extract file in $extracted_dir_location
$extracted_dir_location = _ABS_HMA_ZIP_PATH.$name_file_zip;
// This variable is a consant, but it is necessary to make a new directory
// inside this one with the concrete date for the HMA file. The name of this 
// new directory is going to be the same $proxy_file_zip but without the extension
// .zip.

try{
	$zip = new ZipArchive();
	$zip->open($proxy_file_zip);
	$zip->extractTo($extracted_dir_location);
	$zip->close();
}
catch(Exception $e){
	echo $e->getMessage();
}

// The files are already extracted.

// ------------------------------------------------------------------

// ------ Chapter 3: Loading list  ----------------------------------
// Loading the files with the proxies
// ------------------------------------------------------------------

// How many ips are going to be checked?
$total = extract_from_file($extracted_dir_location."/".$total_list_to_be_checked);
$total_number = count($total);
unset($total);

// The file to match if the given ip is not a Planet Lab proxy is
// $extracted_dir_location/full_list_nopl/_full_list.txt
// It is necessary to load the complete file into an array to check if a given ip
// match or not with this list. The path is defined at the beginning of the script.
$planet_lab_array = extract_from_file($extracted_dir_location."/".$planetlab_full_list);

// The file to match if the given ip is a reliable proxy is
// $extracted_dir_location/full_list/_reliable_list.txt
// Also it is necessary to load this file as described above.
$reliable_array = extract_from_file($extracted_dir_location."/".$reliable_full_list);

// The files to be proccessed are in $extracted_dir_location/full_list/$country.txt
// where $country is always a word with two letters. So, it necessary to build an 
// array form a regular expression where each item is a file path fom a concrete country.
// The regular expresion is: /$extracted_dir_location\/full_list\/[a-z]\{2\}\.txt/

// so...
$country_file_list = glob($extracted_dir_location."/".$full_list_path."/??.txt");

// ------------------------------------------------------------------

// ------ Chapter 4: Checking ---------------------------------------
// Check all the ips
// ------------------------------------------------------------------

// Once the script has loaded this two files (Planet Lab and Reliable lists)
// it is going to check each item from each file in the 'country files' list.
// In this point, the script knows how many proxies it will be to process and
// it can make a real-time progress report.

// Begin notification
shell_exec("echo 'The proxy checking started for $proxy_file_zip' | mutt -s 'Large Checking begin' $email");

// Counter
$counter_for_checked_ip = 0;
foreach($country_file_list as $single_country_file_list){
	file_list_checker($single_country_file_list,$planet_lab_array,$reliable_array);
	$counter_for_checked_ip++;
}

// ------------------------------------------------------------------


// ------ Chapter 5: Report -----------------------------------------
// Reporting (incomplete)
// ------------------------------------------------------------------

shell_exec("echo 'The proxy checking for $proxy_file_zip end' | mutt -s 'Large Checking end' $email");
// Delete files
shell_exec("rm -fr $extracted_dir_location; rm $proxy_file_zip");

// Furthermore, the script is going to count the items in each list from above and
// it will make a report with all the interesting information of the process.

// At the end, the scripts will read the database and count the number o items in it,
// the number of proxies alive, the number of new items and the time spent in the complete
// execution.

// ------------------------------------------------------------------


?>
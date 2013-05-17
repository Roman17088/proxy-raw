<?php

include("lib/functions.proxy-checker.php");

/** Prueba 1

$proxy_ip = "190.210.144.189:80";
$proxy_ip2 = "213.42.124.116:80";
$proxy_ip3 = "200.180.46.213:6515";

echo "$proxy_ip2\n";
print_r(check_single_proxy($proxy_ip2));
echo "$proxy_ip\n";
print_r(check_single_proxy($proxy_ip));
echo "$proxy_ip3\n";
print_r(check_single_proxy($proxy_ip3));

//print_r($db);


/** Prueba 2  */

$dir = "large_list/";
$files = glob($dir . "*.txt");

foreach ($files as $file){
	echo "CHECKING $file:\n\n";
	print_r(file_list_checker($file));
}


?>
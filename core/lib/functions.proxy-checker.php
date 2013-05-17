<?php

/**

Title       : Proxy-Checker Functions      
Author      : Héctor Valverde <hvalverde at uma.es>
Description : This file contains a collection of functions
		 	  to check proxies status and store this info.
Date        : Nov. 2012

*/


// ------ Chapter 1: Initial params ---------------------------------
// Configuration
// ------------------------------------------------------------------
// Initial configuration file must be included in the script file
// Data base config and initialization
require_once(_ABS_CORE_PATH."lib/sql.class.php");
// Object call
require_once(_ABS_CORE_PATH."lib/PHPProxyChecker.class.php");
// ------------------------------------------------------------------

// ------ Chapter 2: Checking Functions -----------------------------
// Functions for gate host communication and proxy checking
// ------------------------------------------------------------------
// Function 1  : check_single_proxy
// Args	       : [String] Proxy ip and port (<ip>:<port>)
// Returns     : [Array] A hash (associative array) whit all the proxy
//			     information.
function check_single_proxy($proxy_ip,$country="??",$planet_lab=0,$reliable=0){

	// Call checkProxy method from required object
	$result = PHPProxyChecker::checkProxy($proxy_ip);
	
	// Take IP and PORT from input string
	$explode = explode(":",$proxy_ip);
	
	$result["PROXY_IP"]   = $explode[0];
	$result["PROXY_PORT"] = $explode[1];
	$result["COUNTRY"]	  = $country;
	$result["PLANET_LAB"] = $planet_lab;
	$result["RELIABLE"]   = $reliable;
	
	return $result;
	
}

// Function 2  : file_list_checker
// Args	       : [String] A path to a file with a proxy list
// Returns     : [Array] Array of arrays with all the results
function file_list_checker($file_path,$planet_lab_array_compare=array(),$reliable_array_compare=array()){
	
	// Extract the existent proxy list
	$array_to_compare = existent_proxy_array();
	
	// Array with proxies to be checked
	$proxies_to_be_checked = extract_from_file($file_path);
	
	// Extract the country from path
	$basename = basename($file_path,".txt");
	
	// Compare for reliable
	foreach($proxies_to_be_checked as $single_proxy){
		
		//echo "Testing $single_proxy ...\n";
		
		$planet_lab = 0;
		$reliable   = 0;
		// Planet lab?
		if(preg_grep("/$single_proxy/",$planet_lab_array_compare)){ $planet_lab = 1; }
		// Reliable
		if(preg_grep("/$single_proxy/",$reliable_array_compare))  { $reliable = 1; }
		
		$single_result_array = check_single_proxy($single_proxy,$basename,$planet_lab,$reliable);
		
		// Record in the database
		record_proxy_checking($single_result_array,true,$array_to_compare);
		
		$all_results[] = $single_result_array;
		//print_r($single_result_array);
		
	}
	
	// Return the results
	return $all_results;
	
}
// ------------------------------------------------------------------

// ------ Chapter 3: MySQL Functions --------------------------------
// Functions to store new items or update them
// ------------------------------------------------------------------
// Function 3  : store_new_item
// Args	       : [Array] Complete information about a proxy
// Returns     : [Bool] True if the item has been inserted succesfully
function insert_new_item($proxy_info){
	
	global $db;
	
	// Extract values from array
	if(isset($proxy_info["NOT_WORKING"])){
		
		$ip		    = $proxy_info["PROXY_IP"];
		$port	    = $proxy_info["PROXY_PORT"];
		$country    = $proxy_info["COUNTRY"];
		$planet_lab = $proxy_info["PLANET_LAB"];
		$reliable   = $proxy_info["RELIABLE"];
		$is_alive   = 0;
		$first_check= date(_DATE_STRING_FORMAT_,time());
		$last_check = $first_check;
		
		$query = "insert into proxytable (ip, port, country, planet_lab, is_alive, first_check, last_check, hma_reliable) 
				  values ('$ip', $port, '$country', $planet_lab, $is_alive, '$first_check', '$last_check', $reliable)";
		
	}else{
		
		$ip         = $proxy_info["PROXY_IP"];
		$port	    = $proxy_info["PROXY_PORT"];
		$country    = $proxy_info["COUNTRY"];
		$type 	    = $proxy_info["TYPE"];
		$type_code  = $proxy_info["TYPE_CODE"];
		$type_name  = $proxy_info["TYPE_NAME"];
		$speed	    = $proxy_info["QUERY_TIME"];
		
		if($proxy_info["SUPPORT_GET"]     == "Y" )  { $get        = 1; }else{ $get        = 0; }
		if($proxy_info["SUPPORT_COOKIE"]  == "Y" )  { $cookie     = 1; }else{ $cookie     = 0; }
		if($proxy_info["SUPPORT_REFERER"] == "Y" )  { $referee    = 1; }else{ $referee    = 0; }
		if($proxy_info["SUPPORT_POST"]    == "Y" )  { $post       = 1; }else{ $post       = 0; }
		if($proxy_info["SUPPORT_SSL"]     == "Y" )  { $sssl       = 1; }else{ $sssl        = 0; }
		$planet_lab = $proxy_info["PLANET_LAB"];
		$reliable   = $proxy_info["RELIABLE"];
		
		$score		 = 1;
		$first_check = date(_DATE_STRING_FORMAT_,time());
		$last_check  = date(_DATE_STRING_FORMAT_,time());
		$is_alive    = 1;
		$check_no 	 = 1;
		
		$query = "insert into proxytable (ip,
										  port,
										  country,
										  type,
										  type_code,
										  type_name,
										  speed,
										  get,
										  cookie,
										  referee,
										  post,
										  sssl,
										  planet_lab,
										  score,
										  first_check,
										  last_check,
										  last_time_alive,
										  check_no,
										  is_alive,
										  hma_reliable)
				  values ('$ip',
				  		  $port,
						  '$country',
						  '$type',
						  '$type_code',
						  '$type_name',
						  '$speed',
						  $get,
						  $cookie,
						  $referee,
						  $post,
						  $sssl,
						  $planet_lab,
						  $score,
						  '$first_check',
						  '$last_check',
						  '$last_check',
						  $check_no,
						  $is_alive,
						  $reliable)";
		
	}
	
	//echo $query;
	
	if($db->query($query)) { return true; } else { return false; }

}

// Function 4  : update_new_item
// Args	       : [Array] Complete information about an existent proxy
// Returns     : [Bool] True if the item has been updated succesfully
function update_item($proxy_info){

	global $db;
	
	// Select existent info
	$query = "select * from proxytable WHERE ip = '".$proxy_info["PROXY_IP"]."' and port = ".$proxy_info["PROXY_PORT"];
	//echo $query."\n";
	$previous_info = $db->get_row($query);
	$id = $previous_info->id;
	
	if(isset($proxy_info["NOT_WORKING"])){
		
		// Update score (-1)
		$new_score = $previous_info->score - 1;
		
		// Update number of checkings
		$new_check_no = $previous_info->check_no + 1;
		
		// Update last_check
		$last_check = date(_DATE_STRING_FORMAT_,time());
		
		$country = $proxy_info["COUNTRY"];
		$planet_lab = $proxy_info["PLANET_LAB"];
		$reliable = $proxy_info["RELIABLE"];
		
		// Build the settings values
		$settings_values = "country         = '$country',
						   planet_lab	    = $planet_lab,
						   last_check	    = '$last_check',
						   score		    = $new_score,
						   check_no		    = $new_check_no,
						   last_time_alive  = '0000-00-00 00:00:00',
						   hma_reliable		= $reliable,
						   is_alive 		= 0";
		
	}else{
	
	
		$ip         = $proxy_info["PROXY_IP"];
		$port	    = $proxy_info["PROXY_PORT"];
		$country    = $proxy_info["COUNTRY"];
		$type 	    = $proxy_info["TYPE"];
		$type_code  = $proxy_info["TYPE_CODE"];
		$type_name  = $proxy_info["TYPE_NAME"];
		$speed	    = $proxy_info["QUERY_TIME"];
		
		if($proxy_info["SUPPORT_GET"]     == "Y" )  { $get        = 1; }else{ $get        = 0; }
		if($proxy_info["SUPPORT_COOKIE"]  == "Y" )  { $cookie     = 1; }else{ $cookie     = 0; }
		if($proxy_info["SUPPORT_REFERER"] == "Y" )  { $referee    = 1; }else{ $referee    = 0; }
		if($proxy_info["SUPPORT_POST"]    == "Y" )  { $post       = 1; }else{ $post       = 0; }
		if($proxy_info["SUPPORT_SSL"]     == "Y" )  { $sssl       = 1; }else{ $sssl       = 0; }
		$planet_lab = $proxy_info["PLANET_LAB"];
		$reliable   = $proxy_info["RELIABLE"];
	
		// Update speed
		if($previous_info->speed == ""){
			$new_speed = $speed;
		}else{
			$new_speed = $previous_info->speed."|".$speed;
		}
		
		// Make speed calcs
		$explode_speed = explode("|",$new_speed);
		$stats_speed = mean_and_error($explode_speed);
		$mean_speed = $stats_speed[0];
		$error_speed = $stats_speed[1];
		
		// Update score
		$new_score = $previous_info->score + 1;
		
		
		// Update last_check
		$last_check = date(_DATE_STRING_FORMAT_,time());
		
		// Update time_alive (just now)
		if($previous_info->last_time_alive == "0000-00-00 00:00:00"){
			$last_time_alive = $last_check;
		}else{
			$last_time_alive = $previous_info->last_time_alive;
		}
		
		// Update number of checkings
		$new_check_no = $previous_info->check_no + 1;
		
		// Build the settings values
		$settings_values = "country         = '$country',
							type            = '$type',
							type_code       = '$type_code',
							type_name       = '$type_name',
							get             = $get,
							cookie          = $cookie,
							referee		    = $referee,
							post		    = $post,
							sssl			= $sssl,
							planet_lab	    = $planet_lab,
							speed		    = '$new_speed',
							last_check	    = '$last_check',
							score		    = $new_score,
							average_speed   = $mean_speed,
							error_speed     = $error_speed,
							last_time_alive = '$last_time_alive',
							check_no		= $new_check_no,
							hma_reliable    = $reliable ";
	
	}
	
	// Build the query
	$query = "update proxytable set ".$settings_values." where id = ".$id;
	
	//echo $query;
	
	// Launch the query
	$db->query($query);

}


// Function 5  : check_if_already_registered
// Args	       : [Array] Complete information about a proxy
//				 If the scripts is going to check a large amount of proxies, it must avoid to do a query
//				 for each item. Then, it need an array with a previous formated (<ip>:<port) query
//			   : [Bool] $compare_from_previous_query 
// Returns     : [Bool] True if the item exists in the database
function check_if_already_registered($proxy_info,$compare_from_previous_query=false,$array_existent=array()){
	
	global $db;
	
	if(!$compare_from_previous_query){
		// Check if the proxy exists
		$check_query = "SELECT * FROM proxytable WHERE ip = ".$proxy_info["PROXY_IP"]." 
						AND port = ".$proxy_info["PROXY_PORT"];
		$check_row   = $db->get_row($check_query);
		if($check_row->id){
			return true;
		}else{
			return false;
		}
	}else{
	
		// Compare ip and port with the $array_existent
		if(in_array($proxy_info['PROXY_IP'].":".$proxy_info['PROXY_PORT'],$array_existent)){
			
			return true;
			
		}else{
			
			return false;
			
		}
		
	}
	
}

// Function 6  : record_proxy_checking
// Args	       : [Array] Complete information about a proxy
// Returns     : [Bool] True if the registration is success
function record_proxy_checking($proxy_info,$compare_from_previous_query=false,$array_existent=array()){
	
	if(check_if_already_registered($proxy_info,$compare_from_previous_query,$array_existent)){
		
		$bool = update_item($proxy_info);
	
	}else{
	
		$bool = insert_new_item($proxy_info);
	
	}
	
	return $bool;
	
}

// Function 7  : existent_proxy_array
// Args		   : void
// Returns	   : [Array] Array with the existent arrays <port>:<ip>
function existent_proxy_array(){
	
	global $db;
	$array_to_compare = array();
	$check_query = "SELECT ip,port FROM proxytable";
	$results = $db->get_results($check_query);
	
	if(!empty($results)){
		
		foreach($results as $proxy){
		
			$array_to_compare[] = $proxy->ip.":".$proxy->port;
			
		}
		
	}
	
	return $array_to_compare;
}


// ------------------------------------------------------------------

// ------ Chapter 4: File Functions ---------------------------------
// Functions to handle files and extract proxies
// ------------------------------------------------------------------
// Function 9  : extract_from_file
// Args        : [String] file path
// Returns	   : [Array] An array with proxies and ports (<ip>:<port>)
function extract_from_file($file_path){
	
	// Open file and extract the lines into an array
	$file_content = file($file_path, FILE_IGNORE_NEW_LINES) or die("Unable to read the file $file_path\n");
	
	// Match only ips and ports
	$array_proxies = preg_grep ("/[0-9]*\.[0-9]*\.[0-9]*\.[0-9]*:[0-9]*/",$file_content);
	
	return $array_proxies;
	
}
// ------------------------------------------------------------------

// ------ Chapter 5: Statistical Functions --------------------------
// Functions to make statistical description
// ------------------------------------------------------------------
// Function 10  : mean_and_error
// Args        : [Array] with values
// Returns     : [Array([Float] Mean,[Float] Standar error)]
function mean_and_error($aValues, $bSample = false)
{
    $fMean = array_sum($aValues) / count($aValues);
    $fVariance = 0.0;
    foreach ($aValues as $i)
    {
        $fVariance += pow($i - $fMean, 2);
    }
    $fVariance /= ( $bSample ? count($aValues) - 1 : count($aValues) );
	
	$array = array(round($fMean,3),round(sqrt($fVariance),3));
	
    return $array;
}
// ------------------------------------------------------------------




?>
<?php

/**

Title       : General Maintainer    
Author      : Héctor Valverde <hvalverde at uma.es>
Description : This file is a script to check the existent items
			  in the database
Date		: 29th Nov. 2012
*/


// ------ Chapter 1: Fuctions including and initial params ----------

// ------------------------------------------------------------------
include_once("/home/hvalverde/proxy-raw/core/config.inc.php");
include_once(_ABS_CORE_PATH."/lib/functions.proxy-checker.php");
// Query max size
$query_max_results = 100;
$clustering_items = $argv[1]; // How many clusters
$assigned_cluster = $argv[2]; // Number of cluster to process
// ------------------------------------------------------------------

// Total items
$count_query = "select count(*) from proxytable";
$count = $db->get_var($count_query);

$cluster_size = $count/$clustering_items;

$bottom_id = ($assigned_cluster*$cluster_size)-($cluster_size-1);  // Bottom id
$upper_id = $bottom_id + $cluster_size;			  				   // Upper id

//echo $bottom_id."\n";
//echo $upper_id."\n";

for($i=$bottom_id;$i<=$upper_id;$i=$i+$query_max_results){
	
	// Local bottom_id
	$local_bottom_id = $i;
	// Local upper_id
	$local_upper_id = $local_bottom_id + $query_max_results - 1;
	
	$local_upper_id > $upper_id ? $local_upper_id = $upper_id - 1 : $local_upper_id;
	
	// Query to take $query_max_results items
	$query = "select ip,port,country,planet_lab,hma_reliable,is_alive from proxytable where id < $local_upper_id and id > $local_bottom_id order by check_no asc";
	
	//echo $query."\n";
	
	
	// Results
	$results = $db->get_results($query);
	
	// For each result
	foreach($results as $row){
	
		$ip         = $row->ip;
		$port       = $row->port;
		$country    = $row->country;
		$planet_lab = $row->planet_lab;
		$reliable   = $row->hma_reliable;
		
		//echo "Updating: $ip:$port ->";
		
		// Check the proxy
		$proxy_info = check_single_proxy("$ip:$port",$country,$planet_lab,$reliable);
	
		// And update the item
		update_item($proxy_info);
		//echo " OK, ALIVE: $row->is_alive\n";
	
	}
	

}


?>
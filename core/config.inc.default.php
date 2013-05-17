<?php




	/**
	
	Title       : Proxy-Checker Settings     
	Author      : Héctor Valverde <hvalverde at uma.es>
	Description : This file contains some constants
	Date        : Nov. 2012
	
	*/

	// Administrator information
	// -------------------------
	
	define("_ADMIN_MAIL","");
	define("_ADMIN_PHONE","");
	
	// Server file structure configuration
	// -----------------------------------
	
	define("_ABS_CORE_PATH", "/home/hvalverde/proxy-raw/core/"); 			// <-- Absolute server core path
	define("_ABS_HMA_ZIP_PATH", _ABS_CORE_PATH."hma_compressed_files/");	// <-- Absolute path for HMA zip files
	
	// Server Timezone config
	// ----------------------
	define("_DATE_STRING_FORMAT_", 'Y-m-d H:i:s');
	//date_default_timezone_set("Etc/GMT+3");
		

	// Database connection configuration
	// ---------------------------------

	define("_DB_USER","");				// <-- mysql db user
	define("_DB_PASS","");		    	        // <-- mysql db password
	define("_DB_NAME","");				// <-- mysql db pname
	define("_DB_HOST","");				// <-- mysql server host
	
	
	// Gate to proxy checking configuration
	// ------------------------------------
	
	define('HTTP_GATE','');   	  // Gate for check HTTP,SOCKS proxy
	//define('HTTPS_GATE',''); 	  // Gate for check HTTPS proxy (SSL Certified)
	define('HTTPS_GATE',''); 		  // Gate for check HTTPS proxy (NO SSL Certified)
	define('CHECK_TIMEOUT',20);						  // Curl timeout request
	
	
	
	
	
	
	

?>
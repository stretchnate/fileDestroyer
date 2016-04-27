<?
	require_once('FileDestroyer.php');

	if( php_sapi_name() != "cli" ) {
		die("<h1>This script can only be executed via command line</h1>");
	}

	echo "\n";

	$file_destroyer = new FileDestroyer();
	//arguments
	//   * directory
	//   * filename - to search for
	//   * eliminate_type (dir|file|both)
	//   * recurse (bool)
	//   * dry_run (bool)
	//   * use_wildcard (bool)
	foreach($argv as $index => $arg) {
		$arg_array = explode("=", $arg);
		
		$method = buildMethod($arg_array[0]);

		if(method_exists($file_destroyer, $method)) {
			$file_destroyer->$method($arg_array[1]);
		}
	}

	try {
		$file_destroyer->execute();
	} catch(Exception $exception) {
		echo $exception->getMessage() . FileDestroyer::NEW_LINE;
	}
	

	function buildMethod($variable) {
		$variable = str_replace(" ", "", ucwords(str_replace("_", " ", $variable)));

		return "set".$variable;
	}
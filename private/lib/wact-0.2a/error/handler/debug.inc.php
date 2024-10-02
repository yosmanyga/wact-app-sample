<?php
//--------------------------------------------------------------------------------
// Copyright 2003 Procata, Inc.
// Released under the LGPL license (http://www.gnu.org/copyleft/lesser.html)
//--------------------------------------------------------------------------------
/**
* @package WACT_UTIL
* @version $Id: debug.inc.php,v 1.2 2004/10/16 20:43:49 jeffmoore Exp $
* @TODO Change to use a template
*/
require_once WACT_ROOT . 'error/error.inc.php';
/**
* Include template engine (currently used only for importVarFile())
*/
require_once WACT_ROOT . 'template/template.inc.php';
/**
* Current error object placed here, for sharing by HandleFrameworkError and
* BareBonesErrorHandler
*/
$GLOBALS['CurrentErrorObject'] = NULL;
/** 
* Putting high-level operations in an error handler leads to the dreaded
* recursive error problem that is the bane of non exception based error handling.
* Yet its nice to allow error message pages to have the look and feel of the rest
* of the site.
* that said, this function directly outputs HTML, and instead should probably
* format its output using a template.  That way, the template can be customized
* to match the look at feel of the site using the framework.
* The price of this is by using a template, there is an increased risk of a new
* error occurring while handling an error.  A bad situation.
*/
function HandleError($ErrorNumber, $ErrorMessage, $FileName, $LineNumber) {
	// Handle framework errors
	if ($ErrorNumber & E_USER_ERROR) {
		HandleFrameworkError($ErrorMessage);
	} else {
		HandleGeneralError($ErrorNumber, $ErrorMessage, $FileName, $LineNumber);
	}
}

/**
* Handles WACT specific framework errors. Currently "hacked" to disable
* multiple generation of error messages with compileall. BareBonesErrorHandler
* should be declared before calling importVarFile
* @TODO fix hack on BareBonesErrorHandler (related to compileall)
*/
function HandleFrameworkError($ErrorMessage) {
	$Error = @unserialize($ErrorMessage);
	if (is_object($Error)) {
		$GLOBALS['CurrentErrorObject'] =& $Error;

		if ( !defined('WACT_ERROR_CONTINUE') ) {
			$OldHandler = set_error_handler('BareBonesErrorHandler');
		}

		$Group = $Error->group;
		$MessageList = importVarFile("/errormessages/$Group.vars");
		$ErrorMessage = $MessageList[$Error->id];

		if ( defined('WACT_ERROR_CONTINUE') ) {
			$OldHandler = set_error_handler('BareBonesErrorHandler');
		}

		foreach($Error->info as $key => $replacement) {
			$ErrorMessage = str_replace('{' . $key . '}', $replacement, $ErrorMessage);
		}

		echo "<br><hr>\n";
		echo "<h3>Error:</h3>$ErrorMessage\n";
		echo "<br><hr>\n";
	
        echo "<ul>";
        DisplayTraceBack();
        echo "</ul>";
		
		if ( !defined('WACT_ERROR_CONTINUE') ) {
			exit;
		}
	} else {
	    // avoid silently swallowing this error if there is a problem.
	    echo "Could not unserialize object for framework error<BR>";
	    echo "Probably because message string exceeded 1023 byte charater limit<BR>";
	    exit;
	    // we should do something smarter than this here.
	}
}

function DisplayTraceBack() {
	// based on PHP manual page for debug_backtrace()
	if (version_compare(PHPVERSION(), '4.3', '>=')) {
		$Trace = debug_backtrace();
	
		foreach ($Trace as $line) {
		
		    if (in_array($line['function'], array(
		        'raiseerror', 'raiseerrorhandler', 'trigger_error', 'errorhandlerdispatch',
		        'handleerror', 'handleframeworkerror', 'displaytraceback' ))) {
		        continue;
		    }
		
			echo '<li>';
			echo '<font face="Courier New,Courier"><B>';
			if (isset($line['class'])) {
				echo $line['class'];
				echo ".";
			}
			echo $line['function'];
			echo "(";
			if (isset($line['args'])) {
				$sep = '';
				foreach ($line['args'] as $arg) {
					echo $sep;
					$sep = ', ';
				
					if (is_null($arg)) {
						echo 'NULL';
					} else if (is_array($arg)) {
						echo 'ARRAY[' . sizeof($arg) . ']';
					} else if (is_object($arg)) {
						echo 'OBJECT:' . get_class($arg);
					} else if (is_bool($arg)) {
						echo $arg ? 'TRUE' : 'FALSE';
					} else { 
						echo '"';
						echo htmlspecialchars(substr((string) @$arg, 0, 32));
						if (strlen($arg) > 32) {
							echo '...';
						}
						echo '"';
					}
				}
			}
			echo ")";
			echo "</b><br>\n";
			if (isset($line['file'])) {
                echo $line['file'];
                echo " line <b>";
                echo $line['line'];
                echo '</b></font>';
			}
		}
	}
}

/**
* Handles general erros (e.g. PHP errors)
*/
function HandleGeneralError($ErrorNumber, $ErrorMessage, $FileName, $LineNumber) {
	$ErrorTime = date("Y-m-d H:i:s (T)");

	// define an assoc array of error string
	// in reality the only entries we should
	// consider are 2,8,256,512 and 1024
	$ErrorType = array (
				1   =>  "Error",
				2   =>  "Warning",
				4   =>  "Parsing Error",
				8   =>  "Notice",
				16  =>  "Core Error",
				32  =>  "Core Warning",
				64  =>  "Compile Error",
				128 =>  "Compile Warning",
				256 =>  "User Error",
				512 =>  "User Warning",
				1024=>  "User Notice"
				);

	if ($ErrorNumber & (E_NOTICE | E_USER_NOTICE)) {
		$Level = 'NOTICE';
	} else if ($ErrorNumber & (E_WARNING | E_USER_WARNING | E_CORE_WARNING | E_COMPILE_WARNING)) {
		$Level = 'WARNING';
	} else if ($ErrorNumber & (E_ERROR | E_PARSE | E_CORE_ERROR | E_COMPILE_ERROR | E_USER_ERROR)) {
		$Level = 'ERROR';
	} else {
		$Level = 'Unknown';
	}

	echo "<br><hr>\n";
	echo "<h3>$Level:</h3>$ErrorMessage\n";
	echo "<ul><li>";
	echo '<font face="Courier New,Courier">';
	echo "$FileName line $LineNumber\n";
	
	DisplayTraceBack();
		
	echo "</ul>";
	echo "<hr>\n";
	if ( !defined('WACT_ERROR_CONTINUE') ) {
		exit;
	}
}

/** 
* This function is called only when an error has occured in the error handler.
* Usually this is because there is a problem accessing the template system.
* Here we heroicly attempt to print out something intelligable for the original error.
*/
function BareBonesErrorHandler($ErrorNumber, $ErrorMessage, $FileName, $LineNumber) {
	$Error =& $GLOBALS['CurrentErrorObject'];

	$filename = WACT_ROOT . 'default/errormessages/' . $Error->group . '.vars';

    $MessageList = array();

	if (FALSE !== ($RawLines = file($filename)) ) {
		while (list(,$Line) = each($RawLines)) {
			$EqualPos = strpos($Line, '=');
			if ($EqualPos === FALSE) {
				$MessageList[trim($Line)] = NULL;
			} else {
				$Key = trim(substr($Line, 0, $EqualPos));
				if (strlen($Key) > 0) {
					$MessageList[$Key] = trim(substr($Line, $EqualPos+1));
				}
			}
		}

		$ErrorMessage = $MessageList[$Error->id];
	} else {
		$ErrorMessage = "An resolvable WACT framework error occured.
							<br>Group: {$Error->group}
							<br>ID: {$Error->id}
							<br>Info: {$Error->info}
							<br>File: $FileName
							<br>Line: $LineNumber";
	}

	foreach($Error->info as $key => $replacement) {
		$ErrorMessage = str_replace('{' . $key . '}', $replacement, $ErrorMessage);
	}

	echo "<br><hr>\n";
	echo "<h3>Error:</h3>$ErrorMessage\n";
	if ( !defined('WACT_ERROR_CONTINUE') ) {
		exit;
	}
}
?>
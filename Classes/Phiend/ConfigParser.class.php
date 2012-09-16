<?php
namespace Phiend;

/**
 * @package phiend
 * @author Maciej 'hawk' Jarzebski
 * @version $Id: ConfigParser.class.php,v 1.9 2003/11/30 20:04:42 cryonax Exp $
 */

require_once PHIEND_DIR . 'ParserConstants.php';

/**
 * The configuration file parser.
 *
 * Created only when the configuration file is about to be parsed.
 * This is usually if the scripts detects the configuration file is newer than its output.
 * 
 * First, the whole file is read and parsed. All data is placed in internal tables and structures.
 * Only then output is generated. If any error is found in the file, an error is reported and
 * no output is produced.
 * 
 * Phiend configuration is contained in one XML file. The parser outputs many files
 * with php code and places them in a separate directory.
 * 
 * @package phiend
 */
class ConfigParser {

	/**
	 * Constructor.
	 *
	 * Initializes tables with default values.
	 */
	function ConfigParser() {
		global $_phiend_defaultTagContent, $_phiend_defaultAction, $_phiend_defaultAuthDriver,
			$_phiend_defaultLogDriver;
		
		$this->_disclaimer = "<?php\n\n" .
			"/*****************************************************************\n" .
 			" * File generated automatically by Phiend at " .
 			date('Y/m/d H:i:s') .
 			" *\n" .
 			" * Do not modify or delete!                                      *\n" .
 			" *****************************************************************/\n\n";

		$this->_fillWithDefaults($this->_basicConfig);
		$this->_fillWithDefaults($this->_sessionConfig);
		$this->_fillWithDefaults($this->_authConfig);
		$this->_fillWithDefaults($this->_errorConfig);

		$this->_fillWithDefaults($_phiend_defaultAction);
		$this->_fillWithDefaults($_phiend_defaultAction[_PHIEND_TAG_ACTION_CONFIG]);
		$this->_fillWithDefaults($_phiend_defaultAuthDriver);
		$this->_fillWithDefaults($_phiend_defaultLogDriver);
	}
	
	/**
	 * Parse the configuration file and write output.
	 *
	 * Creates a PHP SAX parser to read the file, and installs necessary SAX handlers.
	 * Name of the configuration file and names of output files are hard-coded.
	 * At the end, closes all files and frees parser resources.
	 */
	function parse() {
		$this->_parser = & xml_parser_create();
		xml_parser_set_option($this->_parser, XML_OPTION_CASE_FOLDING, 0);
		xml_parser_set_option($this->_parser, XML_OPTION_SKIP_WHITE, 1);
		xml_set_object($this->_parser, & $this);
		xml_set_element_handler($this->_parser, 'startElement',  'endElement');
		xml_set_character_data_handler($this->_parser, 'characters');
		$this->_filename = CODE_DIR . 'config/phiend-config.xml';
		$inputFile = fopen($this->_filename, 'r');
		$inputText = fread($inputFile, filesize(CODE_DIR . 'config/phiend-config.xml'));
		if (xml_parse($this->_parser, $inputText, true) == false) {
			$this->_callErrorHandler(xml_error_string(xml_get_error_code($this->_parser) ) );
		}
		fclose($inputFile);
		xml_parser_free($this->_parser);
	}

//--- sax handlers ------------------------------------------------------------
	
	/**
	 * Executed by parser at every start tag.
	 *
	 * Checks if the tag has a recognized name and if it is allowed in current context.
	 * If no, reports fatal error.
	 * For some tags, inits internal structures.
	 * 
	 * @param int $parser Reference to parser, not used
	 * @param string $name Name of tag
	 * @param array $attributes All attributes of the tag
	 */
	function startElement($parser, $name, $attributes) {
		global $_phiend_tagNames, $_phiend_allowedSubTags, $_phiend_defaultAction,
			$_phiend_defaultAuthDriver, $_phiend_defaultLogDriver;
		
		//standard stuff
		$tag = _PHIEND_TAG_NONE;
		
		foreach ($_phiend_tagNames as $tagNumber => $tagName) {
			if (strcmp($name, $tagName) == 0) {
				$tag = $tagNumber;
				break;
			}
		}
		if ($tag == _PHIEND_TAG_NONE) {
			$this->_callErrorHandler('Unrecognized tag "' . $name . '"');
			return;
		}
		if (count($this->_tagStack) > 0) {
			$prevTag = $this->_tagStack[count($this->_tagStack) - 1];
			if (in_array($tag, $_phiend_allowedSubTags[$prevTag]) == false) {
				$this->_callErrorHandler('Tag "' . $name . '" not allowed in "' . $_phiend_tagNames[$prevTag] . '"');
				return;
			}
		}
		array_push($this->_tagStack, $tag);
		$this->_attributes = $attributes;
		
		//do your job
		switch ($tag) {
			case _PHIEND_TAG_ACTION:
				$this->_currentAction = $_phiend_defaultAction;
				break;
			case _PHIEND_TAG_ALWAYS:
				//handled here because <always/> has no content and so cannot be handled in character handler,
				// like other action match tags.
				$this->_currentAction[_PHIEND_TAG_MATCHES][] = array(
					'type' => $tag,
					'value' => null,
				);
				break;
			case _PHIEND_TAG_AUTH_DRIVER:
				$this->_currentAuthDriver = $_phiend_defaultAuthDriver;
				break;
			case _PHIEND_TAG_LOG_DRIVER:
				$this->_currentLogDriver = $_phiend_defaultLogDriver;
				break;
		}
	}
	
	/**
	 * Executed by parser at every end tag.
	 *
	 * For some tags, saves internal structures.
	 * For the top-most tag (</phiend-config>), calls the function that generates all output.
	 * 
	 * @param int $parser Reference to parser, not used
	 * @param string $name Name of tag
	 */
	function endElement($parser, $name) {
		global $_phiend_tagNames;
		
		//standard stuff
		$tag = $this->_tagStack[count($this->_tagStack) - 1];
		if (strcmp($_phiend_tagNames[$tag], $name) != 0) {
			$this->_callErrorHandler('Ending tag ' . $name . ' does not match', E_USER_ERROR);
		}
		
		//do your job
		switch ($tag) {
			case _PHIEND_TAG_PHIEND_CONFIG:
				$this->_writeConfigOutput();
				break;
			case _PHIEND_TAG_ACTION:
				$this->_actions[] = $this->_currentAction;
				break;
			case _PHIEND_TAG_AUTH_DRIVER:
				$this->_authDrivers[] = $this->_currentAuthDriver;
				break;
			case _PHIEND_TAG_LOG_DRIVER:
				$this->_logDrivers[] = $this->_currentLogDriver;
				break;
		}
		
		//standard stuff
		array_pop($this->_tagStack);
	}
	
	/**
	 * Executed by parser for all character data contained in a tag.
	 *
	 * Copies the data into appropriate structures or tables for all tags,
	 * except for those which have no content.
	 * 
	 * @param int $parser Reference to parser, not used
	 * @param string $data Character data found
	 */
	function characters($parser, $data) {
		global $_phiend_tagContent;
		
		//standard stuff
		$tagStackSize = count($this->_tagStack);
		if ($tagStackSize == 0) {
			return;
		}
		$tag = $this->_tagStack[$tagStackSize - 1];
		if ($_phiend_tagContent[$tag] == _PHIEND_CONTENT_NONE) {
			return;
		}
		//convert $data (which is a string) to appropriate value based on tag content
		$value = $this->_readValue($data, $_phiend_tagContent[$tag]);
		
		//do your job
		switch ($tag) {
			case _PHIEND_TAG_USE_CUSTOM_ERROR_CODES:
			case _PHIEND_TAG_USE_REDIRECTS:
				$this->_basicConfig[$tag] = $value;
				break;
			case _PHIEND_TAG_USE_SESSIONS:
			case _PHIEND_TAG_SESSION_NAME:
			case _PHIEND_TAG_CHECK_IP:
				$this->_sessionConfig[$tag] = $value;
				break;
			case _PHIEND_TAG_USE_AUTH:
			case _PHIEND_TAG_CACHE_USER_ROLES:
				$this->_authConfig[$tag] = $value;
				break;
			case _PHIEND_TAG_ERROR_REPORTING:
			case _PHIEND_TAG_LOG_LEVEL:
			case _PHIEND_TAG_ECHO_LEVEL:
			case _PHIEND_TAG_DIE_LEVEL:
			case _PHIEND_TAG_STORE_LEVEL:
			case _PHIEND_TAG_USE_FULL_PATH:
				$this->_errorConfig[$tag] = $value;
				break;
			case _PHIEND_TAG_TYPE:
				//convert string value to special constants
				if (strcmp($value, 'logic') == 0) {
					$this->_currentAction[_PHIEND_TAG_ACTION_CONFIG][_PHIEND_TAG_TYPE] = '_PHIEND_ACTION_LOGIC';
				} elseif (strcmp($value, 'view') == 0) {
					$this->_currentAction[_PHIEND_TAG_ACTION_CONFIG][_PHIEND_TAG_TYPE] = '_PHIEND_ACTION_VIEW';
				} else {
					$this->_callErrorHandler('Unrecognized action type "' . $value . '"');
				}
				break;
			case _PHIEND_TAG_INHERIT_FROM:
				//must check if base action is already read
				$baseAction = null;
				foreach ($this->_actions as $action) {
					if (strcmp($action[_PHIEND_TAG_NAME], $value) == 0) {
						$baseAction = $action;
						break;
					}
				}
				if (is_null($baseAction)) {
					//base action not found
					$this->_callErrorHandler('Unknown base action "' . $value . '"');
				} else {
					//copy all inherited values from base action to this action,
					foreach ($this->_currentAction[_PHIEND_TAG_ACTION_CONFIG] as $key => $value) {
						$this->_currentAction[_PHIEND_TAG_ACTION_CONFIG][$key] = $baseAction[_PHIEND_TAG_ACTION_CONFIG][$key];
					}
				}
				break;
			case _PHIEND_TAG_FALLBACK_ACTION:
			case _PHIEND_TAG_REQUIRED_ROLES:
			case _PHIEND_TAG_FORCE_LOGOUT:
			case _PHIEND_TAG_ACCEPT_PASSWORD:
			case _PHIEND_TAG_ACCEPT_SID:
			case _PHIEND_TAG_USE_HTTPS:
				$this->_currentAction[_PHIEND_TAG_ACTION_CONFIG][$tag] = $value;
				break;
			case _PHIEND_TAG_EXACTLY:
			case _PHIEND_TAG_CONTAINS:
			case _PHIEND_TAG_EREG:
			case _PHIEND_TAG_PREG:
				//copy the value, but we need to record tag name too
				$this->_currentAction[_PHIEND_TAG_MATCHES][] = array(
					'type' => $tag,
					'value' => $value,
				);
				break;
			case _PHIEND_TAG_GET_PARAM:
				//copy the value as above, but also check if parameter "name" is present
				if (!isset($this->_attributes['name'])) {
					$this->_callErrorHandler('Tag "get-param" requires attribute "name"');
				} else {
					$this->_currentAction[_PHIEND_TAG_MATCHES][] = array(
						'type' => $tag,
						'value' => $value,
						'name' => $this->_attributes['name'],
					);
				}
				break;
			case _PHIEND_TAG_USER_SUPPLIED:
				$prevTag = $this->_tagStack[count($this->_tagStack) - 2];
				switch ($prevTag) {
					case _PHIEND_TAG_AUTH_DRIVER:
						$this->_currentAuthDriver[$tag] = $value;
						break;
					case _PHIEND_TAG_LOG_DRIVER:
						$this->_currentLogDriver[$tag] = $value;
						break;
				}
				break;
			case _PHIEND_TAG_PARAM:
				//copy the value, but also check if parameter "name" is present
				if (!isset($this->_attributes['name'])) {
					$this->_callErrorHandler('Tag "param" requires attribute "name"');
				} else {
					$prevTag = $this->_tagStack[count($this->_tagStack) - 2];
					switch ($prevTag) {
						case _PHIEND_TAG_ACTION:
							$this->_currentAction[_PHIEND_TAG_PARAM][$this->_attributes['name']] = $value;
							break;
						case _PHIEND_TAG_AUTH_DRIVER:
							$this->_currentAuthDriver[_PHIEND_TAG_PARAM][$this->_attributes['name']] = $value;
							break;
						case _PHIEND_TAG_LOG_DRIVER:
							$this->_currentLogDriver[_PHIEND_TAG_PARAM][$this->_attributes['name']] = $value;
							break;
					}
				}
				break;
			case _PHIEND_TAG_NAME:
				$prevTag = $this->_tagStack[count($this->_tagStack) - 2];
				switch ($prevTag) {
					case _PHIEND_TAG_ACTION:
						$this->_currentAction[_PHIEND_TAG_NAME] = $value;
						break;
					case _PHIEND_TAG_AUTH_DRIVER:
						$this->_currentAuthDriver[_PHIEND_TAG_NAME] = $value;
						break;
					case _PHIEND_TAG_LOG_DRIVER:
						$this->_currentLogDriver[_PHIEND_TAG_NAME] = $value;
						break;
				}
				break;
		}
	}

//--- subroutines -------------------------------------------------------------

	/**
	* Fill table with default tag contents.
	* 
	* All null values are replaced with appropriate default tag content.
	* @param array $data The table to fill
	* @access private
	*/
	function _fillWithDefaults(&$data) {
		global $_phiend_defaultTagContent;
	
		foreach ($data as $key => $value) {
			if (is_null($data[$key])) {
				$data[$key] = $_phiend_defaultTagContent[$key];
			}
		}
	}

	/**
	* Call custom error handler directly.
	* 
	* Bypassess trigger_error(...) to provide filename and line number in the XML file, not in this file.
	* @param string $message Message to pass to error handler
	* @param int $code Error code (one of E_USER_something)
	* @access private
	*/
	function _callErrorHandler($message, $code = E_USER_ERROR) {
		_phiend_errorHandler(
			$code,
			$message,
			$this->_filename,
			xml_get_current_line_number($this->_parser)
		);
	}

	/**
	 * Read and convert raw character data.
	 * 
	 * Convert raw data (as returned by parser) into correct value depending on tag content. 
	 * 
	 * @return mixed Converted value
	 * @param string $data Raw data to convert
	 * @param int $type Type of data, one of _PHIEND_CONTENT_XXX constants
	 * @access private
	 */
	function _readValue($data, $type) {
		switch ($type) {
			case _PHIEND_CONTENT_BOOLEAN:
				//todo: other ways of writing boolean values
				if (strcasecmp($data, 'false') == 0) {
					return false;
				} else return (boolean) $data;
			case _PHIEND_CONTENT_STRING:
			case _PHIEND_CONTENT_DEFINED:
				return $data;
			case _PHIEND_CONTENT_INTEGER:
				return (integer) $data;
		}
	}
	
	/**
	 * Write character data.
	 * 
	 * Convert data (stored as a string, integer or bool value) to a string so that
	 * it may be written to output file.
	 * 
	 * @return string Value to output
	 * @param array $parent Table containing the value to convert
	 * @param int $tag Tag to convert (one of _PHIEND_TAG_something constants)
	 * @access private
	 */
	function _writeValue($parent, $tag) {
		global $_phiend_tagContent;
		
		$value = $parent[$tag];
	
		switch ($_phiend_tagContent[$tag]) {
			case _PHIEND_CONTENT_BOOLEAN:
				return ($value == true) ? 'true' : 'false';
			case _PHIEND_CONTENT_STRING:
				return "'" . $value . "'";
			case _PHIEND_CONTENT_INTEGER:
			case _PHIEND_CONTENT_DEFINED:
				return $value;
		}
	}
	
	/**
	 * Write all output.
	 *
	 * Output files are created/opened, written to and closed.
	 * 
	 * @access private
	 */
	function _writeConfigOutput() {
		global $_phiend_tagNames, $_phiend_tagContent;
		
		//resolve dependencies
		if ( (count($this->_logDrivers) == 0) && ($this->_errorConfig[_PHIEND_TAG_LOG_LEVEL] != 0) ) {
			trigger_error('No log drivers specified, logging disabled', E_USER_NOTICE);
			$this->_errorConfig[_PHIEND_TAG_LOG_LEVEL] = 0;
		}
		if ( (count($this->_authDrivers) == 0) && ($this->_authConfig[_PHIEND_TAG_USE_AUTH] == true) ) {
			trigger_error('No auth drivers specified, auth disabled', E_USER_NOTICE);
			$this->_authConfig[_PHIEND_TAG_USE_AUTH] = false;
		}
		if ($this->_sessionConfig[_PHIEND_TAG_USE_SESSIONS] == false) {
			unset($this->_sessionConfig[_PHIEND_TAG_SESSION_NAME]);
			unset($this->_sessionConfig[_PHIEND_TAG_CHECK_IP]);
			$this->_authConfig[_PHIEND_TAG_USE_AUTH] = false;
		}
		if ($this->_authConfig[_PHIEND_TAG_USE_AUTH] == false) {
			unset($this->_authConfig[_PHIEND_TAG_CACHE_USER_ROLES]);
		}
		
		//write auth drivers setup file
		$outAuth = $this->_disclaimer;
		foreach ($this->_authDrivers as $authDriver) {
			$rootDir = $authDriver[_PHIEND_TAG_USER_SUPPLIED] ? 'CODE_DIR' : 'PHIEND_DIR';
			$outAuth .= "include_once " . $rootDir . " . 'auth-drivers/" . $authDriver[_PHIEND_TAG_NAME] . ".class.php';\n";
			$outAuth .= "\$params = array(\n";
			foreach ($authDriver[_PHIEND_TAG_PARAM] as $paramName => $paramValue) {
				$outAuth .= "\t'" . $paramName . "' => '" . $paramValue . "',\n";
			}
			$outAuth .= ");\n";
			$outAuth .= "\$this->_authDrivers[] = new " . $authDriver[_PHIEND_TAG_NAME] . "(\$params);\n\n";
		}
		$outAuth .= "?>";
		$fileAuth = fopen(CONFIG_OUTPUT_DIR . 'auth-drivers.php', 'w');
		fwrite($fileAuth, $outAuth);
		fclose($fileAuth);

		//write log drivers setup
		$outLog = "";
		foreach ($this->_logDrivers as $logDriver) {
			$rootDir = $logDriver[_PHIEND_TAG_USER_SUPPLIED] ? 'CODE_DIR' : 'PHIEND_DIR';
			$outLog .= "include_once " . $rootDir . " . 'log-drivers/" . $logDriver[_PHIEND_TAG_NAME] . ".class.php';\n";
			$outLog .= "\$params = array(\n";
			foreach ($logDriver[_PHIEND_TAG_PARAM] as $paramName => $paramValue) {
				$outLog .= "\t'" . $paramName . "' => '" . $paramValue . "',\n";
			}
			$outLog .= ");\n";
			$outLog .= "\$this->logDrivers[] = new " . $logDriver[_PHIEND_TAG_NAME] . "(\$params);\n\n";
		}
		$outLog .= "?>";
	
		//basic config
		$outGlobal = $this->_disclaimer;
		
		//session config
		$outGlobal .= "\$this->_sessionConfig = array(\n";
		$outGlobal .= "\t'use-sessions' => " . $this->_writeValue($this->_sessionConfig, _PHIEND_TAG_USE_SESSIONS) . ",\n";
		if ($this->_sessionConfig[_PHIEND_TAG_USE_SESSIONS] == true) {
			$outGlobal .= "\t'session-name' => " . $this->_writeValue($this->_sessionConfig, _PHIEND_TAG_SESSION_NAME) . ",\n";
			$outGlobal .= "\t'check-ip' => " . $this->_writeValue($this->_sessionConfig, _PHIEND_TAG_CHECK_IP) . ",\n";
		}
		$outGlobal .= ");\n\n";
		
		//auth config
		$outGlobal .= "\$this->_authConfig = array(\n";
		$outGlobal .= "\t'use-auth' => " . $this->_writeValue($this->_authConfig, _PHIEND_TAG_USE_AUTH) . ",\n";
		if ($this->_authConfig[_PHIEND_TAG_USE_AUTH] == true) {
			$outGlobal .= "\t'cache-user-roles' => " . $this->_writeValue($this->_authConfig, _PHIEND_TAG_CACHE_USER_ROLES) . ",\n";
		}
		$outGlobal .= ");\n\n";
		
		//error config
		$outGlobal .= "error_reporting(" . $this->_writeValue($this->_errorConfig, _PHIEND_TAG_ERROR_REPORTING) . ");\n";
		$outGlobal .= "\$this->logLevel = " . $this->_writeValue($this->_errorConfig, _PHIEND_TAG_LOG_LEVEL) . ";\n";
		$outGlobal .= "\$this->echoLevel = " . $this->_writeValue($this->_errorConfig, _PHIEND_TAG_ECHO_LEVEL) . ";\n";
		$outGlobal .= "\$this->dieLevel = " . $this->_writeValue($this->_errorConfig, _PHIEND_TAG_DIE_LEVEL) . ";\n";
		$outGlobal .= "\$this->storeLevel = " . $this->_writeValue($this->_errorConfig, _PHIEND_TAG_STORE_LEVEL) . ";\n";
		if ($this->_errorConfig[_PHIEND_TAG_USE_FULL_PATH] == true) {
			$outGlobal .= "\$this->_useFullPath = true;\n";
		}
		if ($this->_errorConfig[_PHIEND_TAG_USE_CUSTOM_ERROR_CODES] == true) {
			$outGlobal .= "\$this->useCustomErrorCodes = true;\n";
		}
		$outGlobal .= "\n";
		
		//session kickstart
		if ($this->_sessionConfig[_PHIEND_TAG_USE_SESSIONS] == true) {
			$outGlobal .= "session_save_path(SESSION_DIR);\n";
			$outGlobal .= "session_name(" . $this->_writeValue($this->_sessionConfig, _PHIEND_TAG_SESSION_NAME) . ");\n";
			$outGlobal .= "session_start();\n\n";
		}
		
		//write action switch
		$firstIf = true;
		$finished = false;
		foreach ($this->_actions as $action) {
			//resolve dependencies
			if ($this->_authConfig[_PHIEND_TAG_USE_AUTH] == false) {
				unset($action[_PHIEND_TAG_ACTION_CONFIG][_PHIEND_TAG_REQUIRED_ROLES]);
				unset($action[_PHIEND_TAG_ACTION_CONFIG][_PHIEND_TAG_FORCE_LOGOUT]);
				unset($action[_PHIEND_TAG_ACTION_CONFIG][_PHIEND_TAG_ACCEPT_PASSWORD]);
				unset($action[_PHIEND_TAG_ACTION_CONFIG][_PHIEND_TAG_ACCEPT_SID]);
			}
			if ($this->_basicConfig[_PHIEND_TAG_USE_REDIRECTS] == false) {
				$action[_PHIEND_TAG_ACTION_CONFIG][_PHIEND_TAG_USE_HTTPS] = false;
			}
			
			//write action config
			$outAction = $this->_disclaimer;
			if ($action[_PHIEND_TAG_ACTION_CONFIG][_PHIEND_TAG_USE_HTTPS] == true) {
				$outAction .= "if (!isset(\$_SERVER['HTTPS']) || (\$_SERVER['HTTPS'] != 'on') ) {\n";
				$outAction .= "\theader('Location: https://' . \$_SERVER[\"HTTP_HOST\"] . \$_SERVER[\"REQUEST_URI\"]);\n";
				$outAction .= "\techo 'Strona powinna byc odwiedzana przy u¿yciu <a href=\"https://' . \$_SERVER[\"HTTP_HOST\"] . \$_SERVER[\"REQUEST_URI\"] . '\">bezpiecznego po³±czenia</a>.';\n";
				$outAction .= "\tdie();\n}\n";
			}
			$outAction .= "\$this->params = array(\n";
			foreach ($action[_PHIEND_TAG_PARAM] as $paramName => $paramValue) {
					$outAction .= "\t'" . $paramName . "' => '" . $paramValue . "',\n";
				}
			$outAction .= ");\n";
			$outAction .= "\$this->actionConfig = array(\n";
			foreach ($action[_PHIEND_TAG_ACTION_CONFIG] as $key => $value) {
				if (strcmp($key, _PHIEND_TAG_USE_HTTPS) == 0) {
					continue;
				}
				$outAction .= "\t'" . $_phiend_tagNames[$key] . "' => ";
				$outAction .= $this->_writeValue($action[_PHIEND_TAG_ACTION_CONFIG], $key) . ",\n";
			}
			$outAction .= ");\n?>";
			$fileAction = fopen(CONFIG_OUTPUT_DIR . $action[_PHIEND_TAG_NAME] . '.config.php', 'w');
			fwrite($fileAction, $outAction);
			fclose($fileAction);
			
			//write matches for this action
			foreach ($action[_PHIEND_TAG_MATCHES] as $match) {
				if ($finished == true) {
					break 2;
				}
				if ($firstIf == true) {
					$outGlobal .= 'if';
					$firstIf = false;
				} else {
					$outGlobal .= '} elseif';
				}
				switch ($match['type']) {
					case _PHIEND_TAG_ALWAYS :
						$outGlobal .= " (true)";
						$finished = true;
						break;
					case _PHIEND_TAG_CONTAINS:
						$outGlobal .= " (strstr(\$_SERVER['REQUEST_URI'], '" . $match['value'] . "') != false)";
						break;
					case _PHIEND_TAG_EXACTLY:
						$outGlobal .= " (strcmp(\$_SERVER['REQUEST_URI'], '" . $match['value'] . "') == 0)";
						break;
					case _PHIEND_TAG_EREG:
						$outGlobal .= " (ereg('" . $match['value'] . "', \$_SERVER['REQUEST_URI']) == true)";
						break;
					case _PHIEND_TAG_PREG:
						$outGlobal .= " (preg_match('" . $match['value'] . "', \$_SERVER['REQUEST_URI']) == true)";
						break;
					case _PHIEND_TAG_GET_PARAM:
						$outGlobal .= " (isset(\$_GET['" . $match['name'] . "']) && (\$_GET['" . $match['name'] . "'] == '" . $match['value'] . "'))";
						break;
				}
				$outGlobal .= " {\n\t\$actionName = '" . $action[_PHIEND_TAG_NAME] . "';\n";
			}
		}
		$outGlobal .= "}\n\n";
		$fileGlobal = fopen(CONFIG_OUTPUT_DIR . 'phiend-config.php', 'w');
		fwrite($fileGlobal, $outGlobal);
		fwrite($fileGlobal, $outLog);
		fclose($fileGlobal);
	}

//--- variables ---------------------------------------------------------------
	
	/**
	* Name of file parsed.
	* @var string
	* @access private
	*/
	var $_filename;
	
	/**
	* Handle to SAX parser.
	* @var resource
	* @access private
	*/
	var $_parser;
	
	/**
	* Stack of tags parsed.
	* @var array
	* @access private
	*/
	var $_tagStack = array();
	
	/**
	* Attributes of currently parsed tag.
	* @var array
	* @access private
	*/
	var $_attributes;
	
	/**
	* Contains all actions parsed so far.
	* @var array
	* @access private
	*/
	var $_actions = array();
	
	/**
	* Contains currently parsed action.
	* @var array
	* @access private
	*/
	var $_currentAction;

	/**
	* Contains all auth drivers parsed so far.
	* @var array
	* @access private
	*/
	var $_authDrivers = array();

	/**
	* Contains currently parsed auth driver.
	* @var array
	* @access private
	*/
	var $_currentAuthDriver;

	/**
	* Contains all log drivers parsed so far.
	* @var array
	* @access private
	*/
	var $_logDrivers = array();

	/**
	* Contains currently parsed log driver.
	* @var array
	* @access private
	*/
	var $_currentLogDriver;
	
	/**#@+
	* A template for one of internal tables, filled with defaults in the constructor.
	* @var array
	* @access private
	*/
	var $_basicConfig = array(
		_PHIEND_TAG_USE_REDIRECTS			=> null,
	);
	var $_sessionConfig = array(
		_PHIEND_TAG_USE_SESSIONS			=> null,
		_PHIEND_TAG_SESSION_NAME			=> null,
		_PHIEND_TAG_CHECK_IP				=> null,
	);
	var $_authConfig = array(
		_PHIEND_TAG_USE_AUTH				=> null,
		_PHIEND_TAG_CACHE_USER_ROLES		=> null,
	);
	var $_errorConfig = array(
		_PHIEND_TAG_ERROR_REPORTING			=> null,
		_PHIEND_TAG_LOG_LEVEL				=> null,
		_PHIEND_TAG_ECHO_LEVEL				=> null,
		_PHIEND_TAG_DIE_LEVEL				=> null,
		_PHIEND_TAG_STORE_LEVEL				=> null,
		_PHIEND_TAG_USE_FULL_PATH			=> null,
		_PHIEND_TAG_USE_CUSTOM_ERROR_CODES	=> null,
	);
	/**#@-*/
}

?>
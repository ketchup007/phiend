<?php
/**
 * @package phiend
 * @author Maciej Jarzebski
 * @version $Id: TxtLogDriver.class.php,v 1.1 2003/11/11 12:43:13 cryonax Exp $
 */

/**
 * Simple log driver which uses plain text files.
 */
class TxtLogDriver {

	/**
	 * Constructor.
	 * 
	 * Checks if all required parameters are supplied.
	 * @param array $params Contains parameters for this log driver:
	 *  - file: name of file to use, should include full file path
	 */
	function TxtLogDriver($params) {
		if (isset($params['file'])) {
			$this->_file = $params['file'];
		} else {
			trigger_error('Parameter "file" not set, cannot init driver', E_USER_NOTICE);
		}
	}
	
	function log($code, $message, $file, $line) {
		if (!isset($this->_file)) {
			return;
		}
		$entry = sprintf("%s [%d] %s/%d: %s\n", strftime('%b %d %H:%M:%S'),
			$code, $file, $line, $message);
		
		$fd = @fopen($this->_file, 'a');
		if ($fd) {
			fwrite($fd, $entry);
			fclose($fd);
		}
	}
	
	var $_file = null;
}
 
?>
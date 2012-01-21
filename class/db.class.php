<?php

require_once('config.class.php');
require_once('log.class.php');

class DB
{
	private $db_connect_id;
	private $query_result;
	private $oConfig = null;

	private static $oInstance = null;

	private function DB(Config $oConfig) {

		if ( !$this->db_connect_id ) {

			if ( !$this->db_connect_id = mysql_connect($oConfig->db_host . ":" . $oConfig->db_port,
				$oConfig->db_user,
				$oConfig->db_passwd) ) {
				return false;
			}

			if ( !mysql_select_db($oConfig->db_name, $this->db_connect_id) ) {
				$this->sql_close();
				return false;
			}
			$this->sql_query("SET NAMES 'utf8'");
		}
		$this->oConfig = $oConfig;
	}

	static function getInstance() {
		if( !DB::$oInstance ) {
			DB::$oInstance = new DB( Config::getInstance() );
		}
		if( !DB::$oInstance ) {
			Log::critical('Could not create DB singleton.');
		}

		return DB::$oInstance;
	}

	public static function queryAssocAtom($sSql) {
		$db = DB::getInstance();

		$oResult = $db->sql_query($sSql);

		if( $oResult == null)
			Log::critical("Cannot execute query! ($sSql)");
		$aRow = $db->sql_fetchrow($oResult);

		return $aRow;
	}

	public static function queryAssocAtomFirst($sSql) {
		$aResult = DB::queryAssocAtom($sSql);

		return $aResult[0];
	}

	public static function queryAssoc($sSql) {
		$db = DB::getInstance();

		$oResult = $db->sql_query($sSql);
		if( $oResult == null) {
			Log::debug("Cannot execute query! ($sSql)");
			return false;
		}

		$aRows = array();
		while($aRow = $db->sql_fetchrow($oResult)) {
			if( $oResult === false)
				break;
			$aRows[] = $aRow;
		}

		return $aRows;
	}

	function sql_close() {
		if ( $this->db_connect_id )
			return mysql_close($this->db_connect_id);

		return false;
	}

	function sql_insert_id() {
		return mysql_insert_id($this->db_connect_id);
	}

	function sql_query($query) {
		Log::debug($query);
		return $this->query_result = mysql_query($query, $this->db_connect_id);
	}

	function sql_fetchrow($query_result = NULL) {
		if ( !$query_result )
			$query_result = $this->query_result;

		return  mysql_fetch_assoc($query_result);
	}

	function sql_affectedrows() {
		return mysql_affected_rows($this->db_connect_id);
	}

	function sql_error(){
		return mysql_errno($this->db_connect_id) . ": " . mysql_error($this->db_connect_id);
	}

	function begin() {
		$sSql = "START TRANSACTION;";
		$oResult = $this->sql_query($sSql);
		if( $oResult == null)
			Log::critical("Cannot execute query!");

		return true;
	}

	function commit() {
		$sSql = "COMMIT;";
		$oResult = $this->sql_query($sSql);
		if( $oResult == null)
			Log::critical("Cannot execute query!");

		return true;
	}

	function rollback() {
		$sSql = "ROLLBACK;";
		$oResult = $this->sql_query($sSql);
		if( $oResult == null)
			Log::critical("Cannot execute query!");

		return true;
	}

	function mysql_num_rows($query_result = NULL) {
		if ( !$query_result )
			$query_result = $this->query_result;

		return mysql_num_rows($query_result);
	}

	static function checkForTableCont($sql, $expectedFields) {
		$imax = sizeof($expectedFields);
		//assertion: expectedFields are sorted...

		$results = DB::queryAssoc($sql);
		if ($results == false)
			return false;
		else if ($results == null)
			return $expectedFields;
		else {
			$jmax = sizeof($results);
			Log::debug("got some results with size: ".$jmax);
	//		if (sizeof($results) != $imax)
	//			return true;

			$fields = array();
			foreach ($results as $r) {
				$fields[] = $r['Field'];
			}

			Log::debug("found ".sizeof($fields)." fields, expected: ".$count);
			sort($fields);
			$missingFields = array();
			$j=0;$i=0;
			while ($i<$imax && $j<$jmax) {
				if ($res = strcmp(strtolower($expectedFields[$i]), strtolower($fields[$j]))) {
					if ($res < 0) {
						$missingFields[] = $expectedFields[$i];
						$i++;
					}
					else
						$j++;
				}
				else {
					$i++;
					$j++;
				}
			}
			if (sizeof($missingFields) > 0)
				return $missingFields;
			Log::debug("all fields match => we aint need no update...");
			return 0;
		}

		return false;
	}

	/** returns true or false, wether some $tablename has all $expectedFields **/
	static function checkForTable($tablename, $expectedFields) {
		return DB::checkForTableCont("DESCRIBE ".$tablename, $expectedFields);
	}
}

?>

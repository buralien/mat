<?php

class StatsManager {
  /**
  * @var SQLite3 Database object instance
  */
  private $db = null;

  /**
  * @var string Path to DB file
  */
  private $dbpath;

  /**
  * @var string PHP Session ID
  */
  private $session;

  /**
  * Defines the database schema level used in the source code.
  * In order to increase it, first update the respective functions/source,
  * and also update the createDDL() function with the new schema changes
  * Only after all of that is done should this value be increased to match
  * the new version inserted into the database table schemaversion
  * @var integer
  */
  private $SCHEMA_VERSION = 3;

  function __construct($sessionid) {
    $this->session = $sessionid;
    $this->dbpath = realpath(dirname(__FILE__)). '/stats.db';
    try {
      $this->db = new SQLite3($this->dbpath);
    } catch (Exception $e) {
      $this->dbpath = tempnam(realpath(dirname(__FILE__)), 'stats.db');
      $this->db = new SQLite3($this->dbpath);
    }
    $this->db->exec('PRAGMA journal_mode = MEMORY');
    $this->createDDL();
  }

  /**
  * @return integer
  */
  public function getSchemaVersion() {
    $schema = $this->db->querySingle('SELECT MAX(version) from schemaversion');
    if (!$schema) $schema = 0;
    return $schema;
  }

  /**
  * @return integer
  */
  private function getCurrentSessionID() {
    return $this->db->querySingle('SELECT rowid FROM levelstats WHERE sessionid="'. $this->session. '" AND (action="START" OR action="RESTART") ORDER BY rowid DESC LIMIT 1');
  }

  /**
  * Creates the database schema from scratch or upgrades current version
  * to the latest one if such upgrade is needed.
  * @return void
  */
  private function createDDL() {
    $this->db->exec('CREATE TABLE IF NOT EXISTS schemaversion (version INTEGER NOT NULL)');
    $schema = $this->getSchemaVersion();
    if ($schema >= $this->SCHEMA_VERSION) return true;

    switch($schema) {
      case 0:
        $this->db->exec('CREATE TABLE IF NOT EXISTS stats (id INTEGER PRIMARY KEY, sessionid TEXT, submitted INTEGER NOT NULL, formulaclass TEXT NOT NULL, formula TEXT NOT NULL, result TEXT NOT NULL, correct INTEGER NOT NULL)');
        $this->db->exec('CREATE TABLE IF NOT EXISTS levelstats (id INTEGER PRIMARY KEY, sessionid TEXT, submitted INTEGER NOT NULL, levelclass TEXT NOT NULL, action TEXT NOT NULL, leveldata TEXT NOT NULL)');
      case 1:
        $this->db->exec('ALTER TABLE stats ADD COLUMN levelid INTEGER NOT NULL DEFAULT 0');
      case 2:
        $this->db->exec('ALTER TABLE stats ADD COLUMN ip_address TEXT');
    }

    $this->db->exec('INSERT INTO schemaversion (version) VALUES ('. $this->SCHEMA_VERSION. ')');
  }

  /**
  * @param Formula $formula
  * @param array $result
  * @return boolean Query execution status
  */
  public function addRecord($formula, $result) {
    if (is_array($result)) {
      $conv = '('. implode(',', $result). ')';
    } else {
      $conv = $result;
    }
    $data = array(
      'sessionid' => "'". SQLite3::escapeString($this->session). "'",
      'submitted' => "DateTime('now')",
      'levelid' => $this->getCurrentSessionID(),
      'formulaclass' => "'". SQLite3::escapeString(get_class($formula)). "'",
      'formula' => "'". SQLite3::escapeString($formula->toStr(true)). "'",
      'result' => "'". SQLite3::escapeString($conv). "'",
      'correct' => ($formula->validateResult($result) ? '1' : '0'),
      'ip_address' => "'". $_SERVER['REMOTE_ADDR']. "'"
    );
    $query = "INSERT INTO stats (". implode(',', array_keys($data)). ") VALUES (". implode(',', $data). ")";
    return $this->db->exec($query);
  }

  /**
  * @param GenericLevel $level
  * @return boolean Query execution status
  */
  public function addStartLevel($level) {
    return $this->addLevel($level, 'START');
  }

  /**
  * @param GenericLevel $level
  * @return boolean Query execution status
  */
  public function addFinishedLevel($level) {
    return $this->addLevel($level, 'FINISH');
  }

  /**
  * @param GenericLevel $level
  * @return boolean Query execution status
  */
  public function addResetLevel($level) {
    return $this->addLevel($level, 'RESET');
  }

  /**
  * @param GenericLevel $level
  * @return boolean Query execution status
  */
  public function addRestartLevel($level) {
    return $this->addLevel($level, 'RESTART');
  }

  /**
  * @param GenericLevel $level
  * @param string $action
  * @return boolean Query execution status
  */
  private function addLevel($level, $action) {
    $query = "INSERT INTO levelstats (sessionid, submitted, levelclass, action, leveldata) VALUES ('". $this->session. "', DateTime('now'), '". SQLite3::escapeString(get_class($level)). "', '". $action. "', '". SQLite3::escapeString(json_encode($level)). "')";
    return $this->db->exec($query);
  }

  /**
  * @return array Associative array with numbers of correctly and incorrectly solved formulas of each class
  */
  public function getCurrentSessionStats() {
    $query = "SELECT formulaclass, correct, count(*) as cnt FROM stats WHERE levelid=". $this->getCurrentSessionID(). " GROUP BY formulaclass, correct";
    $result = $this->db->query($query);
    $ret = array();
    while($row = $result->fetchArray(SQLITE3_ASSOC)) {
      if($row['correct']) {
        $ret[$row['formulaclass']]['correct'] = $row['cnt'];
      } else {
        $ret[$row['formulaclass']]['failed'] = $row['cnt'];
      }
    }
    return $ret;
  }

  /**
  * Closes the DB connection
  * @return boolean Status
  */
  public function close() {
    if (is_a($this->db, 'SQLite3')) $ret = $this->db->close();
    unset($this->db);
    return $ret;
  }
}

?>

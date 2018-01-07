<?php

class StatsManager {
  private $db;
  private $session;

  private $SCHEMA_VERSION = 2;

  function __construct($sessionid) {
    $this->session = $sessionid;
    $this->db = new SQLite3('include/stats.db');
    $this->createDDL();
  }

  public function getSchemaVersion() {
    $schema = $this->db->querySingle('SELECT MAX(version) from schemaversion');
    if (!$schema) $schema = 0;
    return $schema;
  }

  private function getCurrentSessionID() {
    return $this->db->querySingle('SELECT rowid FROM levelstats WHERE sessionid="'. $this->session. '" AND action="START" ORDER BY rowid DESC LIMIT 1');
  }

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
    }

    $this->db->exec('INSERT INTO schemaversion (version) VALUES ('. $this->SCHEMA_VERSION. ')');
  }

  public function addRecord($formula, $result) {
    if (is_array($result)) {
      $conv = '('. implode(',', $result). ')';
    } else {
      $conv = $result;
    }
    return $this->db->exec("INSERT INTO stats (sessionid, submitted, levelid, formulaclass, formula, result, correct) VALUES ('". $this->session. "', DateTime('now'), ". $this->getCurrentSessionID(). ", '". SQLite3::escapeString(get_class($formula)). "', '". SQLite3::escapeString($formula->toStr(true)). "', '". SQLite3::escapeString($conv). "', ". ($formula->validateResult($result) ? '1' : '0'). ")");
  }

  public function addStartLevel($level) {
    return $this->addLevel($level, 'START');
  }

  public function addFinishedLevel($level) {
    return $this->addLevel($level, 'FINISH');
  }

  public function addResetLevel($level) {
    return $this->addLevel($level, 'RESET');
  }

  private function addLevel($level, $action) {
    $query = "INSERT INTO levelstats (sessionid, submitted, levelclass, action, leveldata) VALUES ('". $this->session. "', DateTime('now'), '". SQLite3::escapeString(get_class($level)). "', '". $action. "', '". SQLite3::escapeString(json_encode($level)). "')";
    return $this->db->exec($query);
  }

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

  public function close() {
    return $this->db->close();
  }
}

?>

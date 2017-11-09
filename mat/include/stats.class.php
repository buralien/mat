<?php

class StatsManager {
  private $db;
  function __construct() {
    $this->db = new SQLite3('include/stats.db');
    $this->db->exec('CREATE TABLE IF NOT EXISTS stats (id INTEGER PRIMARY KEY, sessionid TEXT, submitted INTEGER NOT NULL, formulaclass TEXT NOT NULL, formula TEXT NOT NULL, result TEXT NOT NULL, correct INTEGER NOT NULL)');
  }

  public function addRecord($sessionid, $formula, $result) {
    if (is_array($result)) {
      $conv = '('. implode(',', $result). ')';
    } else {
      $conv = $result;
    }
    return $this->db->exec("INSERT INTO stats (sessionid, submitted, formulaclass, formula, result, correct) VALUES ('". $sessionid. "', DateTime('now'), '". get_class($formula). "', '". $formula->toStr(true). "', '". $conv. "', ". ($formula->validateResult($result) ? '1' : '0'). ")");
  }

  public function close() {
    return $this->db->close();
  }
}

?>

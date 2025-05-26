<?php

require_once __DIR__ . '/../classes/DatabaseUsers.php';

class TermPee
{
    public $term;
    public $pee;

    public function __construct()
    {
        $db = new \App\DatabaseUsers();
        $row = $db->query("SELECT term, pee FROM termpee WHERE id = 1")->fetch();
        if ($row) {
            $this->term = $row['term'];
            $this->pee = $row['pee'];
        } else {
            $this->term = null;
            $this->pee = null;
        }
    }

    public static function getCurrent()
    {
        return new self();
    }
}

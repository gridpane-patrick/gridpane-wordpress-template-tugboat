<?php

namespace SEOPressPro\Services\Table;

defined( 'ABSPATH' ) || exit;

use SEOPressPro\Models\Table\TableInterface;
use SEOPressPro\Core\Table\QueryCreateTable;
use SEOPressPro\Core\Table\QueryExistTable;

class TableManager {

    public function __construct(){
        $this->queryCreateTable = new QueryCreateTable();
        $this->queryExistTable = new QueryExistTable();
    }

    public function exist(TableInterface $table){
        return $this->queryExistTable->exist($table);
    }

    public function create(TableInterface $table){
        if($this->exist($table)){
            return;
        }

        $this->queryCreateTable->create($table);
    }

    public function createTablesIfNeeded($tables){
        foreach ($tables as $key => $table) {
            $this->create($table);
        }
    }

}

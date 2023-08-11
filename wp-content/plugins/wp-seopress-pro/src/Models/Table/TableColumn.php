<?php

namespace SEOPressPro\Models\Table;

defined( 'ABSPATH' ) || exit;

use SEOPressPro\Models\Table\TableColumnInterface;

class TableColumn implements TableColumnInterface {

    public function __construct($name, $data = []){

        $this->name = $name;
        $this->type = isset($data['type']) ? $data['type'] : 'varchar';
        $this->primaryKey = isset($data['primaryKey']) ? (bool) $data['primaryKey'] : false;
        $this->index = isset($data['index']) ? $data['index'] : false;
    }

    /**
	 * @return int
	 */
	public function getType(){
        return $this->type;
    }

	/**
	 * @return string
	 */
	public function getName(){
        return $this->name;
    }

    /**
     * @return bool
     */
	public function getPrimaryKey(){
        return $this->primaryKey;
    }

    public function getIndex(){
        return $this->index;
    }


}

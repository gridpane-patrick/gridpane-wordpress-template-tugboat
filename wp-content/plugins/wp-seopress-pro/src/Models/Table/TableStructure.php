<?php

namespace SEOPressPro\Models\Table;

defined( 'ABSPATH' ) || exit;

use SEOPressPro\Models\Table\TableStructureInterface;

class TableStructure implements TableStructureInterface{

    public function __construct($columns){
        $this->columns = $columns;
    }


    /**
     * @return array
     */
	public function getColumns(){
        return $this->columns;
    }


}

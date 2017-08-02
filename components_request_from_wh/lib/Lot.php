<?php

class Lot {
    public $t_item;
    public $t_loca;
    public $t_clot;
    public $t_strs;
    public $t_date;
    public $t_cwar;
    public $manufacturer;
    
    function __construct($row) {
		    $this->t_item = $row["t_item"];
		    $this->t_loca = $row["t_loca"];
		    $this->t_clot = $row["t_clot"];
		    $this->t_strs = $row["t_strs"];           
		    $this->t_date = $row["t_date"];
		    $this->t_cwar = $row["t_cwar"];		    
		    $this->manufacturer = $row["manufacturer"];
    }
}

?>
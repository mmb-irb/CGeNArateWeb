<?php
namespace App\Models;

class Countries extends Model {

	protected $table = 'countries';

	public function getCountriesList() {
			
		return json_decode(json_encode($this->db->getDocuments($this->table, [], [
			"projection" => [
				"field3" => 0, 
				"field2" => 0
			], 
			"sort" => [
				"country" => 1
			]
		])), true);
		
	}

}

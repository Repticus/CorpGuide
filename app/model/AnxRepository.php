<?php

class AnxRepository extends Nette\Object {

	/** @var Nette\Database\Context */
	protected $connection;

	public function __construct(Nette\Database\Context $db) {
		$this->connection = $db;
	}

	public function update($id, $data) {
		return $this->connection->table('anx')->get($id)->update($data);
	}

}

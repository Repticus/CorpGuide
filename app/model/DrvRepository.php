<?php

class DrvRepository extends Nette\Object {

	/** @var Nette\Database\Context */
	protected $connection;

	public function __construct(Nette\Database\Context $db) {
		$this->connection = $db;
	}

	public function getAllDrv() {
		return $this->connection->table('drv')->order('order');
	}

}

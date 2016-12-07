<?php

class DirRepository extends Nette\Object {

	/** @var Nette\Database\Context */
	protected $connection;

	public function __construct(Nette\Database\Context $db) {
		$this->connection = $db;
	}

	public function getDirectives() {
		return $this->connection->table('directive')->order('order');
	}

}

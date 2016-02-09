<?php

namespace CrudGenerator\Console\Commands;

use Illuminate\Console\Command;

class CrudGeneratorCommand extends Command
{
	protected $signature   = 'make:crud {model-name : Name of the db table you want to make crud for} {--table-name=}';
	protected $description = 'Create CRUD code based on a mysql table.';

	public function __construct()
	{
		parent::__construct();
	}

	public function handle()
	{
		$generator = new \CrudGenerator\CrudGeneratorService(
			$this, $this->argument('model-name'), $this->option('table-name')
		);

		$generator->Generate();
	}
}





















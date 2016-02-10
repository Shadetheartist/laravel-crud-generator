<?php

namespace CrudGenerator\Console\Commands;

use Illuminate\Console\Command;

class CrudGeneratorCommand extends Command
{
	protected $signature   = 'make:crud {model : Name of the db table you want to make crud for} {--table= : provide a custom table name to be used instead of one derived from the model name} {--layout= : provide a custom layout to be used in place of the default}';
	protected $description = 'Create CRUD code based on a mysql table.';

	public function __construct()
	{
		parent::__construct();
	}

	public function handle()
	{
		$generator = new \CrudGenerator\CrudGeneratorService(
			$this, $this->argument('model'), $this->option('table'), $this->option('layout')
		);

		$generator->Generate();
	}
}

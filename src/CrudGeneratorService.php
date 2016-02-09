<?php

namespace CrudGenerator;

use DB;
use Artisan;

class CrudGeneratorService
{
	protected $layout;
	protected $console;

	protected $modelName;
	protected $controllerName;
	protected $tableName;

	protected $templateData;

	public function __construct(\Illuminate\Console\Command $console, $modelName, $customTableName)
	{
		$this->console         = $console;

		$this->modelName       = self::ModelNameConvention($modelName);
		$this->controllerName  = self::ControllerNameConvention($modelName);
		$this->tableName       = self::TableNameConvention(
			$customTableName
				? $customTableName
				: $modelName
		);
	}

	public function Generate()
	{

		$this->console->info('');
		$this->console->info("Creating $this->modelName CRUD from the $this->tableName table ");
		$this->console->info('');

		$columns = self::GetColumns($this->tableName);

		$this->templateData = [
			'UCModel'        => $this->modelName,
			'UCModelPlural'  => str_plural($this->modelName),
			'LCModel'        => strtolower($this->modelName),
			'LCModelPlural'  => strtolower(str_plural($this->modelName)),
			'TableName'      => $this->tableName,
			'ViewTemplate'   => $this->layout,
			'ControllerName' => $this->controllerName,
			'ViewFolder'     => str_plural($this->tableName),
			'RoutePath'      => $this->tableName,
			'Columns'        => $columns,
			'SearchColumn'   => $columns[1]->Field,
			'ColumnCount'    => count($columns),
		];

		self::GenerateController();
		self::GenerateModel();
		self::GenerateViews();

		$routes = "Route::get('$this->tableName/ajaxData', '$this->controllerName@ajaxData');\r\nRoute::resource('$this->tableName', '$this->controllerName');";
		$this->console->info("Adding Routes: \r\n" . $routes);
		file_put_contents(app_path() . '/Http/routes.php', "\r\n" . $routes, FILE_APPEND);

	}

	protected function GenerateModel()
	{
		$this->console->info("Generating model with 'php artisan make:model $this->modelName'");
		Artisan::call('make:model', ['name' => $this->modelName]);
	}

	protected function GenerateViews()
	{
		$path = base_path() . '/resources/views/' . $this->tableName;
		if (!is_dir($path)) {
			$this->console->info('Creating views directory at: ' . $path);
			mkdir($path);
		}

		$this->templateData['Layout']    = 'layouts.app';
		$this->templateData['RoutePath'] = $this->tableName;

		$this->console->info('Generating View: index');
		$controller = \View::file(__DIR__ . '/Templates/index.blade.php', $this->templateData)->render();
		$outPath    = $path . '/index.blade.php';
		file_put_contents($outPath, $controller);

		$this->console->info('Generating View: create');
		$controller = \View::file(__DIR__ . '/Templates/create.blade.php', $this->templateData)->render();
		$outPath    = $path . '/create.blade.php';
		file_put_contents($outPath, $controller);

		$this->console->info('Generating View: show');
		$controller = \View::file(__DIR__ . '/Templates/show.blade.php', $this->templateData)->render();
		$outPath    = $path . '/show.blade.php';
		file_put_contents($outPath, $controller);

	}

	protected function GenerateController()
	{
		$this->console->info('Generating Controller: ' . $this->controllerName);
		$controller = \View::file(__DIR__ . '/Templates/controller.blade.php', $this->templateData)->render();

		$path = app_path() . '/Http/Controllers/' . $this->controllerName . '.php';

		file_put_contents($path, $controller);

	}

	private static function GetHtmlType($dbType)
	{
		if (str_contains($dbType, 'varchar')) {
			return 'text';
		}
		if (str_contains($dbType, 'int') || str_contains($dbType, 'float')) {
			return 'number';
		}
		if (str_contains($dbType, 'date')) {
			return 'date';
		}
		return 'unknown';
	}

	private static function GetColumns($tableName)
	{
		$columns = DB::select("show columns from " . $tableName);
		foreach ($columns as $column) {
			$column->Type = self::GetHtmlType($column->Type);
		}
		return $columns;
	}

	private static function ModelNameConvention($word)
	{
		return ucfirst(strtolower(str_singular($word)));
	}

	private static function TableNameConvention($word)
	{
		return strtolower(str_plural($word));
	}

	private static function ControllerNameConvention($word)
	{
		return ucfirst(strtolower(str_plural($word))) . 'Controller';
	}
}





















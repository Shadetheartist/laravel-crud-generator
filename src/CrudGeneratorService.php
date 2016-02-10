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
		$this->console = $console;

		$this->modelName      = self::ModelNameConvention($modelName);
		$this->controllerName = self::ControllerNameConvention($modelName);
		$this->tableName      = self::TableNameConvention(
			$customTableName
				? $customTableName
				: $modelName
		);
	}
	public function Generate()
	{

		$this->console->line('');
		$this->console->info("Creating $this->modelName CRUD from the $this->tableName table ");
		$this->console->line('');

		$columns            = $this->GetColumns($this->tableName);

		$this->templateData = [
			'UCModel'           => $this->modelName,
			'UCModelPlural'     => str_plural($this->modelName),
			'LCModel'           => strtolower($this->modelName),
			'LCModelPlural'     => strtolower(str_plural($this->modelName)),
			'TableName'         => $this->tableName,
			'ViewTemplate'      => $this->layout,
			'ControllerName'    => $this->controllerName,
			'ViewFolder'        => str_plural($this->tableName),
			'RoutePath'         => $this->tableName,
			'Columns'           => $columns,
			'SearchColumn'      => $columns[1]->Field,
			'ColumnCount'       => count($columns),
		];

		self::GenerateController();
		self::GenerateModel();
		self::GenerateViews();

		$routes = "Route::get('$this->tableName/ajaxData', '$this->controllerName@ajaxTableData');
			   \r\nRoute::resource('$this->tableName', '$this->controllerName');";
		$this->console->line("Adding Routes: \r\n" . $routes);
		file_put_contents(app_path() . '/Http/routes.php', "\r\n\r\n//Made by crud generator tool on " . date('d-m-Y @ H:i:s') . "\r\n" . $routes, FILE_APPEND);

	}

	protected function GenerateModel()
	{
		$this->console->line("Generating model with 'php artisan make:model $this->modelName'");
		Artisan::call('make:model', ['name' => $this->modelName]);
	}

	protected function GenerateViews()
	{
		$path = base_path() . '/resources/views/' . $this->tableName;
		if (!is_dir($path)) {
			$this->console->line('Creating views directory at: ' . $path);
			mkdir($path);
		}

		$this->templateData['Layout']    = 'layouts.app';
		$this->templateData['RoutePath'] = $this->tableName;

		$this->console->line('Generating View: index');
		$controller = \View::file(__DIR__ . '/Templates/index.blade.php', $this->templateData)->render();
		$outPath    = $path . '/index.blade.php';
		file_put_contents($outPath, $controller);

		$this->console->line('Generating View: create');
		$controller = \View::file(__DIR__ . '/Templates/create.blade.php', $this->templateData)->render();
		$outPath    = $path . '/create.blade.php';
		file_put_contents($outPath, $controller);

		$this->console->line('Generating View: show');
		$controller = \View::file(__DIR__ . '/Templates/show.blade.php', $this->templateData)->render();
		$outPath    = $path . '/show.blade.php';
		file_put_contents($outPath, $controller);

	}

	protected function GenerateController()
	{
		$this->console->line('Generating Controller: ' . $this->controllerName);
		$controller = \View::file(__DIR__ . '/Templates/controller.blade.php', $this->templateData)->render();

		$path = app_path() . '/Http/Controllers/' . $this->controllerName . '.php';

		file_put_contents($path, $controller);

	}


	private function GetForeign($columnName)
	{
		$name             = explode('_id', $columnName)[0];
		$foreignModelName = self::ModelNameConvention($name);
		$foreignTableName = self::TableNameConvention($name);
		$this->console->line("Attempting to link '$this->modelName' to '$foreignModelName'");

		//this is not a great way to find the class, as the user could put the model in another folder/namespace and so on
		if (class_exists('\\App\\' . $foreignModelName)) {
			$foreignColumns = DB::select("show columns from " . $foreignTableName);
			foreach ($foreignColumns as $column) {
				if ($column->Field != 'id') {
					continue;
				}

				//model has a column for 'id'
				foreach ($foreignColumns as $column) {

				}

			}
		}

		return 'hidden';
	}

	private function GetLaravelFormType($column)
	{
		//special formatting :)
		//@formatter:off
		$name = strtolower($column->Field);
		$type = strtoupper($column->Type);

		//should not display these by default
		if ($type == 'TIMESTAMP' || $name == 'remember_token') {
			return null;
		}

		//special cases based on name
		if ($name == 'password' || str_contains($name, 'email')) {
			return $name;
		}

		if ($name == 'id') {
			return 'hidden';
		}

		if (str_contains($name, '_id')) {
			return $this->GetForeign($name);
		}

		if (str_contains($type, 'DATE')) {
			return 'date';
		}

		if (str_contains($type, 'CHAR') ||
			str_contains($type, 'TEXT') ||
			str_contains($type, 'BLOB') ||
			str_contains($type, 'ENUM') ||
			str_contains($type, 'SET')) {
			return 'text';
		}

		if (str_contains($type, 'INT') ||
			str_contains($type, 'FLOAT') ||
			str_contains($type, 'DOUBLE') ||
			str_contains($type, 'DECIMAL') ||
			str_contains($type, 'SET') ||
			str_contains($type, 'TIME') ||
			str_contains($type, 'YEAR')) {
			return 'number';
		}

		return null;
		//@formatter:on
	}

	private function GetColumns($tableName)
	{
		$columns = DB::select("show columns from " . $tableName);

		//we're going to put the hidden fields at the top, just for organization
		$hiddenColumns = [];

		foreach ($columns as $key => $column) {
			if ($type = $this->GetLaravelFormType($column)) {
				if ($type == 'hidden') {
					$hiddenColumns[] = $column;
					unset($columns[$key]);
				}
				$column->Type = $type;
			}
			else {
				//if the column is something we shouldn't show, as dictated in "GetLaravelFormType"
				unset($columns[$key]);
			}
		}

		return array_merge($hiddenColumns, $columns);
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





















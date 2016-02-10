<?php

namespace CrudGenerator;

use DB;
use Artisan;

class CrudGeneratorService
{
	//todo: make config for this
	protected $notDisplayableList = ['created_at', 'updated_at', 'remember_token'];
	protected $stringColumnList   = ['name', 'description'];

	protected $layout;
	protected $console;

	protected $modelName;
	protected $controllerName;
	protected $tableName;

	protected $templateData;

	public function __construct(\Illuminate\Console\Command $console, $modelName, $customTableName, $customLayoutName)
	{
		$this->console = $console;

		$this->modelName      = self::ModelNameConvention($modelName);
		$this->controllerName = self::ControllerNameConvention($modelName);
		$this->tableName      = self::TableNameConvention(
			$customTableName
				? $customTableName
				: $modelName
		);

		$this->layout = $customLayoutName
			? $customLayoutName
			: 'layouts.app';
	}

	public function Generate()
	{

		$this->console->line('');
		$this->console->info("Creating $this->modelName CRUD from the $this->tableName table ");
		$this->console->line('');

		$columns = $this->GetColumns($this->tableName);

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
			'Layout'         => $this->layout,
		];

		$this->GenerateController();
		$this->GenerateModel();
		$this->GenerateViews();
		$this->GenerateRoutes();

	}

	protected function GenerateRoutes()
	{
		$routes = "Route::get('$this->tableName/ajaxData', '$this->controllerName@ajaxTableData');\r\nRoute::resource('$this->tableName', '$this->controllerName');";
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
		$outputPath = base_path() . '/resources/views/' . $this->tableName;
		if (!is_dir($outputPath)) {
			$this->console->line('Creating views directory at: ' . $outputPath);
			mkdir($outputPath);
		}

		$this->GenerateView('index', $outputPath);
		$this->GenerateView('create', $outputPath);
		$this->GenerateView('show', $outputPath);

	}

	protected function GenerateView($viewName, $outputPath)
	{
		$this->console->line("Generating View: $viewName");
		$view    = \View::file(__DIR__ . "/Templates/$viewName.blade.php", $this->templateData)->render();
		$outPath = $outputPath . "/$viewName.blade.php";
		file_put_contents($outPath, $view);
	}

	protected function GenerateController()
	{
		$this->console->line('Generating Controller: ' . $this->controllerName);
		$controller = \View::file(__DIR__ . '/Templates/controller.blade.php', $this->templateData)->render();

		$path = app_path() . '/Http/Controllers/' . $this->controllerName . '.php';

		file_put_contents($path, $controller);

	}

	private function GetBestStringColumn($columns)
	{

		//get all the column names
		$columnNames = $catIds = array_map(
			function ($o) {
				return $o->Field;
			},
			$columns
		);

		//get all the columns where there is a match in the string column list defined in config
		$nameIndexes = [];
		foreach ($columnNames as $key => $columnName) {
			$index = array_search($columnName, $this->stringColumnList);
			if ($index !== false) {
				$nameIndexes[$key] = $index;
			}
		}

		//if theres no matches
		if (!count($nameIndexes)) {
			return null;
		}

		//sort the list (asc), and keep keys
		asort($nameIndexes);

		//return the best column
		return $columns[array_keys($nameIndexes)[0]];
	}

	private function GetForeign($columnName)
	{
		$name             = explode('_id', $columnName)[0];
		$foreignModelName = self::ModelNameConvention($name);
		$foreignTableName = self::TableNameConvention($name);
		$this->console->line("Attempting to link '$this->modelName' to '$foreignModelName'");

		//this mess gets the index of best column to use as the main column that users see when selecting something
		if (class_exists('\\App\\' . $foreignModelName)) {
			$foreignColumns = DB::select("show columns from " . $foreignTableName);
			if ($stringColumn = $this->GetBestStringColumn($foreignColumns)) {

				//Todo: integrate this column, and make a config file for the options at the top of the page

				//return $foreignModelName . '->' . $stringColumn->Field;
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
		if (in_array($name, $this->notDisplayableList)) {
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

	private function GetColumns($tableName, $organizeColumns = true)
	{
		$columns = DB::select("show columns from " . $tableName);

		//we're going to put the hidden fields at the top, just for organization
		$hiddenColumns = [];

		foreach ($columns as $key => $column) {
			if ($type = $this->GetLaravelFormType($column)) {
				if ($organizeColumns && $type == 'hidden') {
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





















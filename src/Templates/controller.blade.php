{!!'<'!!}?php

namespace App\Http\Controllers;

use DB;
use App\{{$UCModel}};
use App\Http\Requests;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class {{$ControllerName}} extends Controller
{
    public function __construct()
    {

    }

	/**
	* Display a listing of the resource.
	*
	* {{'@'}}return \Illuminate\Http\Response
	*/
    public function index()
	{
        return \View::make('{{$ViewFolder}}.index');
	}

	/**
	* Show the form for creating a new resource.
	*
	* {{'@'}}return \Illuminate\Http\Response
	*/
	public function create()
	{
        return \View::make('{{$ViewFolder}}.create');
	}

	/**
	* Store a newly created resource in storage.
	*
	* {{'@'}}param  \Illuminate\Http\Request  $request
	* {{'@'}}return \Illuminate\Http\Response
	*/
    public function store(Request $request) {

        ${{$LCModel}} = null;
        if($request->id > 0) {
            ${{$LCModel}} = {{$UCModel}}::findOrFail($request->id); }
        else {
            ${{$LCModel}} = new {{$UCModel}};
        }

@foreach($Columns as $Column)
        ${{$LCModel}}->{{$Column->Field}} = $request->{{$Column->Field}};
@endforeach

        //${{$LCModel}}->user_id = $request->user()->id;
        ${{$LCModel}}->save();
        return redirect('/{{$RoutePath}}');

    }

	/**
	* Display the specified resource.
	*
	* {{'@'}}param  int  $id
	* {{'@'}}return \Illuminate\Http\Response
	*/
    public function show($id)
    {
        ${{$LCModel}} = {{$UCModel}}::findOrFail($id);
        return \View::make('{{$ViewFolder}}.show')
                    ->with('{{$LCModel}}', ${{$LCModel}});
    }

	/**
	* Show the form for editing the specified resource.
	*
	* {{'@'}}param  int  $id
	* {{'@'}}return \Illuminate\Http\Response
	*/
	public function edit($id)
	{
		${{$LCModel}} = {{$UCModel}}::findOrFail($id);
        return \View::make('{{$ViewFolder}}.create')
                    ->with('{{$LCModel}}', ${{$LCModel}});
	}

	/**
	* Update the specified resource in storage.
	*
	* {{'@'}}param  \Illuminate\Http\Request  $request
	* {{'@'}}param  int  $id
	* {{'@'}}return \Illuminate\Http\Response
	*/
    public function update(Request $request, $id)
    {

    }

	/**
	* Remove the specified resource from storage.
	*
	* {{'@'}}param  int  $id
	* {{'@'}}return \Illuminate\Http\Response
	*/
    public function destroy($id) {
        ${{$LCModel}} = {{$UCModel}}::findOrFail($id);
        ${{$LCModel}}->delete();
    }

	/**
	* Returns Data for pagination
	*
	* {{'@'}}param  \Illuminate\Http\Request  $request
	* {{'@'}}return \Illuminate\Http\Response
	*/
	public function ajaxData(Request $request)
	{
		$len = $_GET['length'];
		$start = $_GET['start'];

		$select = "SELECT *,1,2 ";
		$presql = " FROM {{$TableName}} a ";
		if($_GET['search']['value']) {
			$presql .= " WHERE {{$SearchColumn}} LIKE '%".$_GET['search']['value']."%' ";
		}

		$presql .= "  ";

		$sql = $select.$presql." LIMIT ".$start.",".$len;


		$qcount = DB::select("SELECT COUNT(a.id) c".$presql);
		//print_r($qcount);
		$count = $qcount[0]->c;

		$results = DB::select($sql);
		$ret = [];
		foreach ($results as $row) {
			$r = [];
			foreach ($row as $value) {
				$r[] = $value;
			}
			$ret[] = $r;
		}

		$ret['data'] = $ret;
		$ret['recordsTotal'] = $count;
		$ret['iTotalDisplayRecords'] = $count;

		$ret['recordsFiltered'] = count($ret);
		$ret['draw'] = $_GET['draw'];

		echo json_encode($ret);

	}







}

{!!'<'!!}?php

namespace App\Http\Controllers;

use DB;
use App\{{$UCModel}};
use App\Http\Requests;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Input;

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
        ${{$LCModel}} = new {{$UCModel}};
@foreach($Columns as $Column)
        ${{$LCModel}}->{{$Column->Field}} = $request->{{$Column->Field}};
@endforeach
        ${{$LCModel}}->save();
        return redirect('/{{$TableName}}');
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
		${{$LCModel}} = {{$UCModel}}::findOrFail($id);
@foreach($Columns as $Column)
		${{$LCModel}}->{{$Column->Field}} = $request->{{$Column->Field}};
@endforeach
		${{$LCModel}}->update();
		return redirect('/{{$TableName}}');
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
	public function ajaxTableData(Request $request)
	{

		$columns = [@for($i = 0; $i < count($Columns); $i++)'{{$Columns[$i]->Field}}'@if($i != count($Columns) - 1), @endif
	@endfor];

		$search = Input::get('search')['value'];

		$count = DB::table('{{$LCModelPlural}}')->count();
		$data  = {{$UCModel}}::where('{{$SearchColumn}}', 'LIKE', "%$search%")
							 ->skip(Input::get('start'))
							 ->take(Input::get('length'))
							 ->get($columns)
							 ->toArray();

		//https://datatables.net/reference/option/
		$response['data']                 = $data;
		$response['recordsTotal']         = $count;
		$response['iTotalDisplayRecords'] = $count;
		$response['recordsFiltered'] = count($data);

		echo json_encode($response);

	}

}

{{ '@' }}extends('{{$Layout}}')

{{ '@' }}section('content')

<h2 class="page-header">{{$UCModel}}</h2>

<div class="panel panel-default">
    <div class="panel-heading">
        Add/Modify {{$UCModel}}
    </div>

    <div class="panel-body">

        <form action="{{'{{'}} url('/{{$RoutePath}}') }}" method="POST" class="form-horizontal">
            {{'{{'}} csrf_field() }}

@foreach($Columns as $Column)
@if($Column->Type != 'unknown')
                <div class="form-group">
                   <label for="{{$Column->Field}}" class="col-sm-3 control-label">{{$Column->Field}}</label>
                   <div class="col-sm-6">
                       <input type="{{$Column->Type}}" name="{{$Column->Field}}" id="{{$Column->Field}}" class="form-control" value="{{'{{'}} ${{$LCModel}}->{{$Column->Field}}  or ''}}">
                   </div>
               </div>
@endif
@endforeach
            <div class="form-group">
                <div class="col-sm-offset-3 col-sm-6">
                    <button type="submit" class="btn btn-success">
                        <i class="fa fa-plus"></i> Save
                    </button>
                    <a class="btn btn-default" href="{{'{{'}} url('/{{$RoutePath}}') }}"><i class="glyphicon glyphicon-chevron-left"></i> Back</a>
                </div>
            </div>

        </form>
    </div>
</div>

{{ '@' }}endsection
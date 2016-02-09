{{ '@' }}extends('{{$Layout}}')

{{ '@' }}section('content')

<h2 class="page-header">{{$UCModel}}</h2>

<div class="panel panel-default">
    <div class="panel-heading">
        View {{$UCModel}}
    </div>

    <div class="panel-body">

        <form class="form-horizontal">

@foreach($Columns as $Column)
            <div class="form-group">

                <label for="{{$Column->Field}}" class="col-sm-3 control-label">{{$Column->Field}}</label>

                <div class="col-sm-6">

                    <input type="{{$Column->Type}}" name="{{$Column->Field}}" id="{{$Column->Field}}" class="form-control" value="{{'{{'}} ${{$LCModel}}->{{$Column->Field}}  or ''}}" readonly="readonly">

                </div>

            </div>
@endforeach

            <div class="form-group">
                <div class="col-sm-offset-3 col-sm-6">
                    <a class="btn btn-default" href="{{'{{'}} url('/{{$RoutePath}}') }}"><i class="glyphicon glyphicon-chevron-left"></i> Back</a>
                </div>
            </div>

        </form>

    </div>
</div>

{{ '@' }}endsection
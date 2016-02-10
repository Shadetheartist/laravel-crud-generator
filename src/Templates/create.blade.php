{{ '@' }}extends('{{$Layout}}')

{{ '@' }}section('content')

<h2 class="page-header">{{$UCModel}}</h2>

<div class="panel panel-default">
    <div class="panel-heading">
        Add/Modify {{$UCModel}}
    </div>

    <div class="panel-body">

        {{ '@' }}if(isset(${{$LCModel}}))
            {{'{{'}} Form::model(${{$LCModel}}, ['route' => ['{{$LCModelPlural}}.update', ${{$LCModel}}->id], 'method' => 'PUT', 'class' => 'form-horizontal']) }}
        {{ '@' }}else
            {{'{{'}} Form::open(['route' => '{{$LCModelPlural}}.store', 'class' => 'form-horizontal']) }}
        {{ '@' }}endif

            {{'{{'}} Form::token() }}

@foreach($Columns as $Column)
@if($Column->Type == 'hidden')
                {{'{{'}} Form::hidden('{{$Column->Field}}') }}

@else
                <div class="form-group">
                    {{'{{'}} Form::label('{{$Column->Field}}', '{{$Column->Field}}', ['class' => 'col-sm-3 control-label']) }}
                   <div class="col-sm-6">
@if($Column->Type == 'password')
                       {{'{{'}} Form::{{$Column->Type}}('{{$Column->Field}}', ['class' => 'col-sm-3 form-control']) }}
@else
                       {{'{{'}} Form::{{$Column->Type}}('{{$Column->Field}}', null, ['class' => 'col-sm-3 form-control']) }}

@endif
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

        {{ Form::close() }}

    </div>
</div>

{{ '@' }}endsection
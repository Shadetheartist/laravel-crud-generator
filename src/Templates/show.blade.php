{{ '@' }}extends('{{$Layout}}')

{{ '@' }}section('content')

<h2 class="page-header">{{$UCModel}}</h2>

<div class="panel panel-default">
    <div class="panel-heading">
        View {{$UCModel}}
    </div>

    <div class="panel-body">

        <form class="form-horizontal">
            {{'{{'}} Form::model(${{$LCModel}}, ['route' => ['{{$LCModelPlural}}.update', ${{$LCModel}}->id], 'method' => 'PUT', 'class' => 'form-horizontal']) }}

            @foreach($Columns as $Column)
                @if($Column->Type != 'hidden')
                    <div class="form-group">
                        {{'{{'}} Form::label('{{$Column->Field}}', '{{$Column->Field}}', ['class' => 'col-sm-3 control-label disabled', 'readonly' => 'readonly']) }}
                        <div class="col-sm-6">
                            @if($Column->Type == 'password')
                                {{'{{'}} Form::{{$Column->Type}}('{{$Column->Field}}', ['class' => 'col-sm-3 form-control disabled', 'readonly' => 'readonly']) }}

                            @else
                                {{'{{'}} Form::{{$Column->Type}}('{{$Column->Field}}', null, ['class' => 'col-sm-3 form-control disabled', 'readonly' => 'readonly']) }}

                            @endif
                        </div>
                    </div>
                @endif
            @endforeach

            <div class="form-group">
                <div class="col-sm-offset-3 col-sm-6">
                    <a class="btn btn-default" href="{{'{{'}} url('/{{$TableName}}') }}"><i class="glyphicon glyphicon-chevron-left"></i> Back</a>
                </div>
            </div>

        </form>

    </div>
</div>

{{ '@' }}endsection
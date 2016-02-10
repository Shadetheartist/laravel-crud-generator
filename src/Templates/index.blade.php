{{ '@' }}extends('{{$Layout}}')

{{ '@' }}section('content')

<h2 class="page-header">{{$UCModelPlural}}</h2>

<div class="panel panel-default">
    <div class="panel-heading">
        List of {{$UCModelPlural}}
    </div>

    <div class="panel-body">
        <div class="">
            <table class="table table-striped" id="{{$LCModelPlural}}Table">
                <thead>
                <tr>
                    @foreach($Columns as $Column)
                        <th>{{$Column->Field}}</th>
                    @endforeach
                    <th style="width:50px"></th>
                    <th style="width:50px"></th>
                </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        </div>
        <a href="{{'{{'}}url('{{$RoutePath}}/create')}}" class="btn btn-primary" role="button">Add {{$UCModel}}</a>
    </div>
</div>
{{ '@' }}endsection

{{ '@' }}section('scripts')
<script type="text/javascript">
    var {{$LCModelPlural}}Table = null;
    $(document).ready(function(){
        {{$LCModelPlural}}Table = $('#{{$LCModelPlural}}Table').DataTable({
            "processing": true,
            "serverSide": true,
            "ordering"  : false,
            "responsive": true,
            "ajax"      : "{{'{{'}}url('{{$RoutePath}}/ajaxData')}}",
            "columnDefs": [
                {
                    "render" : function(data, type, row){
                        return '<a href="{{'{{'}}url('{{$RoutePath}}')}}/' + row[0] + '">' + data + '</a>';
                    },
                    "targets":  1
                },
                {
                    "render" : function(data, type, row){
                        return '<a href="{{'{{'}}url('{{$RoutePath}}')}}/' + row[0] + '/edit" class="btn btn-default">Edit</a>';
                    },
                    "targets": {{$ColumnCount}}
                },
                {
                    "render" : function(data, type, row){
                        return '<a onclick="return delete{{$UCModel}}(' + row[0] + ')" class="btn btn-danger">Delete</a>';
                    },
                    "targets": {{$ColumnCount}} + 1
                },
            ]
        });
    });

    function delete{{$UCModel}}(id){
        if(confirm('You really want to delete this record?')){

            var url = '{{'{{'}}url('{{$RoutePath}}')}}/';

            $.ajax({
                url: url + id,
                type: 'DELETE'
            }).success(function(){
                {{$LCModelPlural}}Table.ajax.reload();
            });
        }
        return false;
    }

</script>
{{ '@' }}endsection
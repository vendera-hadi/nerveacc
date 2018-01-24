@extends('layouts.app')

@section('htmlheader_title')
    Reminder
@endsection

@section('contentheader_title')
   Reminder
@endsection

@section('htmlheader_scripts')
    <link rel="stylesheet" type="text/css" href="{{ asset('plugins/jquery-easyui/themes/default/easyui.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('plugins/jquery-easyui/themes/icon.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('plugins/jquery-easyui/themes/color.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('plugins/datepicker/datepicker3.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('plugins/bootstrap-wysihtml5/bootstrap3-wysihtml5.min.css') }}">
@endsection

@section('contentheader_breadcrumbs')
    <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
        <li class="active">Invoice Payment Reminder</li>
    </ol>
@stop

@section('main-content')
<div class="row">
        <form action="" method="GET">
        <div class="col-sm-3">
            <div class="form-group">
                <label>Tenant Name</label>
                <input type="text" class="form-control" name="q" value="{{Request::get('q')}}">
            </div>
        </div>

        <div class="col-sm-3">
            <div class="form-group">
                <label>Start date</label>
                <div class="input-group date">
                    <div class="input-group-addon">
                      <i class="fa fa-calendar"></i>
                    </div>
                    <input type="text" name="start" class="form-control pull-right datepicker" data-date-format="yyyy-mm-dd" value="{{Request::get('start')}}">
                </div>
            </div>
        </div>

        <div class="col-sm-3">
            <div class="form-group">
                <label>End date</label>
                <div class="input-group date">
                    <div class="input-group-addon">
                      <i class="fa fa-calendar"></i>
                    </div>
                    <input type="text" name="end" class="form-control pull-right datepicker" data-date-format="yyyy-mm-dd" value="{{Request::get('end')}}">
                </div>
            </div>
        </div>

        <div class="col-sm-3">
            <button class="btn btn-flat btn-info btn-block" style="margin-top: 23px;">Search</button>
        </div>
        </form>
</div>

<div class="row">
    <div class="row">
    <div class="col-sm-12">
        <div class="box box-primary">
            <div class="box-body">
                @if(Session::get('error'))
                    <div class="alert alert-danger">
                      <strong>Error!</strong> {{ Session::get('error') }}
                    </div>
                @endif

                @if(Session::get('success'))
                    <div class="alert alert-success">
                      <strong>Success</strong> {{ Session::get('success') }}
                    </div>
                @endif

                @if (count($errors) > 0)
                  <div class="alert alert-danger">
                    <ul>
                      @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                      @endforeach
                    </ul>
                  </div>
                @endif

                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Tenant Name</th>
                            <th>Unpaid Invoice</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($list as $inv)
                        <tr>
                            <td>{{$inv->tenan_name}}</td>
                            <td>
                                {{$inv->totalinv}} invoice(s)<br>
                                @if(!empty($inv->invoices))
                                @foreach($inv->invoices as $invoice)
                                    {{$invoice->inv_number}} : IDR {{number_format($invoice->inv_outstanding,0)}} <br>
                                @endforeach
                                @endif
                            </td>
                            <td>
                                <button type="button" class="sendReminderCustom" data-id="{{$inv->tenan_id}}"><i class="fa fa-envelope"></i> Message Khusus
                                </button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>

                {{$list->links()}}
            </div>
        </div>
    </div>
</div>

<!-- Modal -->
<div id="reminderCustomModal" class="modal fade" role="dialog">
  <div class="modal-dialog">

    <!-- Modal content-->
    <div class="modal-content">
      <!-- <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title">Modal Header</h4>
      </div> -->
      <form action="{{route('invoice.reminder.custom')}}" id="customReminderForm" method="post">
      <div class="modal-body">
            <input type="hidden" name="id" id="customReminderID">
            <div class="form-group">
                <label>Reminder Title</label>
                <input type="text" name="title" class="form-control">
            </div>
            <div class="form-group">
              <label>Content</label>
              <textarea class="textarea" name="content" class="form-control" style="width: 100%;" required></textarea>
            </div>
      </div>
      <div class="modal-footer">
        <button type="submit" id="submitMessage" class="btn btn-flat btn-primary">Submit</button>
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
      </div>
      </form>
    </div>

  </div>
</div>
@endsection

@section('footer-scripts')
<script src="{{asset('plugins/jQueryUI/jquery-ui.min.js')}}"></script>
<script type="text/javascript" src="{{ asset('plugins/jquery-easyui/jquery.easyui.min.js') }}"></script>
<script type="text/javascript" src="{{ asset('js/datagrid-filter.js') }}"></script>
<!-- datepicker -->
<script type="text/javascript" src="{{ asset('plugins/datepicker/bootstrap-datepicker.js') }}"></script>
<script type="text/javascript" src="{{asset('plugins/bootstrap-wysihtml5/bootstrap3-wysihtml5.all.min.js')}}"></script>
<script type="text/javascript">
$('.datepicker').datepicker({
        autoclose: true
    });

$(".textarea").wysihtml5();

$('.sendReminder').click(function(){
    if (!confirm('Are you sure want to send Reminder 1 ?')) return false;
    var id = $(this).data('id');
    var win = window.open('/invoice/sendreminder?id='+id, '_blank');
});

$('.sendReminderCustom').click(function(){
    $('#reminderCustomModal').modal('show');
    // $(".textarea").wysihtml5();
    $('#customReminderID').val($(this).data('id'));
});

$("#customReminderForm").submit(function(e){
    e.preventDefault();
    $('#submitMessage').attr('disabled','disabled').text('Sedang Mengirim Pesan ...');
    $.post('/invoice/customreminder', $(this).serialize(), function(data){
        alert('Email has been sent successfully');
        $('#reminderCustomModal').modal('hide');
        $('#submitMessage').removeAttr('disabled').text('Submit');
        var w = window.open('about:blank');
        w.document.open();
        w.document.write(data);
        w.document.close();
    });
});
</script>
@endsection
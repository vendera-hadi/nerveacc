@extends('layouts.app2')

@section('htmlheader_title')
    {{ trans('adminlte_lang::message.pagenotfound') }}
@endsection

@section('contentheader_title')
    {{ trans('adminlte_lang::message.404error') }}
@endsection

@section('$contentheader_description')
@endsection

@section('main-content')
<!-- Main content -->
<section class="content" style="min-height: 550px">
<div class="error-page">
    <h2 class="headline text-yellow"> 404</h2>
    <div class="error-content">
        <h3><i class="fa fa-warning text-yellow"></i> Oops! {{ trans('adminlte_lang::message.pagenotfound') }}.</h3>
        <p>
            {{ trans('adminlte_lang::message.notfindpage') }}
            {{ trans('adminlte_lang::message.mainwhile') }} <a href='{{ url('/') }}'>{{ trans('adminlte_lang::message.returndashboard') }}</a>
        </p>
    </div><!-- /.error-content -->
</div><!-- /.error-page -->
</section>
@endsection
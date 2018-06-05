@extends('admin.layouts.master')

@section('content')
    <div class="card">
      <div class="card-header">
        <nav class="navbar navbar-expand-lg">
          <a class="btn btn-primary ml-auto" href="{{ route('admin.staff.edit', $staff->id) }}">編輯</a>
        </nav>
      </div>
      <div class="card-body">
        <div class="row">
            <div class="col-4">
            {{ Form::label('name', '姓名') }}
            </div>
            <div class="col-8">
            {{ $staff->name }}
            </div>
        </div>
        <div class="row">
            <div class="col-4">
            {{ Form::label('email', 'email') }}
            </div>
            <div class="col-8">
            {{ $staff->email }}
            </div>
        </div>
        <div class="row">
            <div class="col-4">
            {{ Form::label('staff_code', '員工編號') }}
            </div>
            <div class="col-8">
            {{ $staff->staff_code }}
            </div>
        </div>
        <div class="row">
            <div class="col-4">
            {{ Form::label('ID_card_number', '身分證字號/居留證號碼') }}
            </div>
            <div class="col-8">
            {{ $staff->profile->ID_card_number }}
            </div>
        </div>
        <div class="row">
            <div class="col-4">
            {{ Form::label('identity', '身份') }}
            </div>
            <div class="col-8">
            {!! strtr($staff->profile->identity, [
               '2' => '離職',
               '1' => '工讀',
               '0' => '全職']) !!}
            </div>
        </div>
        <div class="row">
            <div class="col-4">
            {{ Form::label('subscribed', '訂閱狀態') }}
            </div>
            <div class="col-8">
            {!! strtr($staff->subscribed, [
               '1' => '已訂閱',
               '0' => '未訂閱']) !!}
            </div>
        </div>
        <div class="row">
            <div class="col-4">
            {{ Form::label('gender', '性別') }}
            </div>
            <div class="col-8">
            {!! strtr($staff->profile->gender, [
               '1' => '男',
               '0' => '女']) !!}
            </div>
        </div>
        <div class="row">
            <div class="col-4">
            {{ Form::label('phone_number', '電話號碼') }}
            </div>
            <div class="col-8">
            {{ $staff->profile->phone_number }}
            </div>
        </div>
        <div class="row">
            <div class="col-4">
            {{ Form::label('home_address', '戶籍地址') }}
            </div>
            <div class="col-8">
            {{ $staff->profile->home_address }}
            </div>
        </div>
        <div class="row">
            <div class="col-4">
            {{ Form::label('mailing_address', '通訊地址') }}
            </div>
            <div class="col-8">
            {{ $staff->profile->mailing_address }}
            </div>
        </div>
        <div class="row">
            <div class="col-4">
            {{ Form::label('bank_account', '銀行帳號') }}
            </div>
            <div class="col-8">
            {{ $staff->profile->bank_account }}
            </div>
        </div>
        <div class="row">
            <div class="col-4">
            {{ Form::label('emergency_contact', '緊急聯絡人/關係') }}
            </div>
            <div class="col-8">
            {{ $staff->profile->emergency_contact }}
            </div>
        </div>
        <div class="row">
            <div class="col-4">
            {{ Form::label('emergency_contact_phone', '緊急聯絡電話') }}
            </div>
            <div class="col-8">
            {{ $staff->profile->emergency_contact_phone }}
            </div>
        </div>
        <div class="row">
            <div class="col-4">
            {{ Form::label('position', '職稱') }}
            </div>
            <div class="col-8">
            {{ $staff->profile->position }}
            </div>
        </div>
        <div class="row">
            <div class="col-4">
            {{ Form::label('on_board_date', '到職日') }}
            </div>
            <div class="col-8">
            {{ $staff->profile->on_board_date }}
            </div>
        </div>
        <div class="row">
            <div class="col-4">
            {{ Form::label('birth', '生日') }}
            </div>
            <div class="col-8">
            {{ $staff->profile->birth }}
            </div>
        </div>
        <div class="row">
            <div class="col-4">
            {{ Form::label('salary', '月支薪俸') }}
            </div>
            <div class="col-8">
            {{ $staff->profile->salary }}
            </div>
        </div>
        <div class="row">
            <div class="col-4">
            {{ Form::label('add_insurance_date', '加保日期') }}
            </div>
            <div class="col-8">
            {{ $staff->profile->add_insurance_date }}
            </div>
        </div>
        <div class="row">
            <div class="col-4">
            {{ Form::label('cancel_insurance_date', '退保日期') }}
            </div>
            <div class="col-8">
            {{ $staff->profile->cancel_insurance_date }}
            </div>
        </div>
        <div class="row">
            <div class="col-4">
            {{ Form::label('highest_education', '最高學歷') }}
            </div>
            <div class="col-8">
            {{ $staff->profile->highest_education }}
            </div>
        </div>
        <div class="row">
            <div class="col-4">
            {{ Form::label('experience', '經歷') }}
            </div>
            <div class="col-8">
            {{ $staff->profile->experience }}
            </div>
        </div>
        <div class="row">
            <div class="col-4">
            {{ Form::label('group', '組別') }}
            </div>
            <div class="col-8">
            {{ $staff->profile->group }}
            </div>
        </div>
      </div>
    </div>    
    <nav class="navbar navbar-expand-lg">
      <a class="btn btn-primary mr-auto" href="{{ route('admin.staff.index') }}">返回</a>
    </nav>
    {{ Form::close() }}
@endsection


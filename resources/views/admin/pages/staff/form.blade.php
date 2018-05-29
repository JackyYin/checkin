@extends('admin.layouts.master')

@section('content')
    <style>
        #staffForm .bottom-navbar {
           padding: unset;
        }

        #staffForm .bottom-navbar li {
            margin-right: 10px;
        }

    </style>
    @if( isset($staff) )
        {!! Form::model($staff, [
        'id'     => 'staffForm',
        'method' => 'PUT',
        'url' => route('admin.staff.update', [$staff->id] + request()->query())
    ]) !!}
    @else
    {{ Form::open([
        'id'  => 'staffForm',
        'url' => route('admin.staff.store')
    ]) }}
    @endif
    
    <div class="form-group">
        {{ Form::label('name', '姓名') }}
        {{ Form::text('name', null, ['class' => 'form-control']) }}
    </div>
    <div class="form-group">
        {{ Form::label('email', 'email') }}
        {{ Form::text('email', null, ['class' => 'form-control', 'placeholder' => 'jjyyg1123@gmail.com']) }}
    </div>
    <div class="form-group">
        {{ Form::label('staff_code', '員工編號') }}
        {{ Form::text('staff_code', null, ['class' => 'form-control']) }}
    </div>
    <div class="form-group">
        {{ Form::label('ID_card_number', '身分證字號/居留證號碼') }}
        {{ Form::text('ID_card_number',  isset($staff->profile) ? $staff->profile->ID_card_number : null, ['id' => 'ID_card_number', 'class' => 'form-control']) }}
    </div>
    <div class="form-group">
        {{ Form::label('identity', '身份') }}
        {{ Form::select('identity', $options['identity'], isset($staff->profile) ? $staff->profile->identity : null, ['class' => 'form-control']) }}
    </div>
    <div class="form-check">
        {{ Form::radio('gender', 1, isset($staff->profile) ? $staff->profile->gender : true, ['class' => 'form-check-input']) }}
        {{ Form::label('gender', '男', ['class' => 'form-check-label']) }}
    </div>
    <div class="form-check">
        {{ Form::radio('gender', 0, isset($staff->profile) ? !$staff->profile->gender : false, ['class' => 'form-check-input']) }}
        {{ Form::label('gender', '女', ['class' => 'form-check-label']) }}
    </div>
    <div class="form-group">
        {{ Form::label('phone_number', '電話號碼') }}
        {{ Form::text('phone_number', isset($staff->profile) ? $staff->profile->phone_number : null, ['class' => 'form-control']) }}
    </div>
    <div class="form-group">
        {{ Form::label('home_address', '戶籍地址') }}
        {{ Form::text('home_address', isset($staff->profile) ? $staff->profile->home_address : null, ['class' => 'form-control']) }}
    </div>
    <div class="form-group">
        {{ Form::label('mailing_address', '通訊地址') }}
        {{ Form::text('mailing_address', isset($staff->profile) ? $staff->profile->mailing_address : null, ['class' => 'form-control']) }}
    </div>
    <div class="form-group">
        {{ Form::label('bank_account', '銀行帳號') }}
        {{ Form::text('bank_account', isset($staff->profile) ? $staff->profile->bank_account : null, ['class' => 'form-control']) }}
    </div>
    <div class="form-group">
        {{ Form::label('emergency_contact', '緊急聯絡人/關係') }}
        {{ Form::text('emergency_contact', isset($staff->profile) ? $staff->profile->emergency_contact : null, ['class' => 'form-control']) }}
    </div>
    <div class="form-group">
        {{ Form::label('emergency_contact_phone', '緊急聯絡電話') }}
        {{ Form::text('emergency_contact_phone', isset($staff->profile) ? $staff->profile->emergency_contact_phone : null, ['class' => 'form-control']) }}
    </div>
    <div class="form-group">
        {{ Form::label('position', '職稱') }}
        {{ Form::text('position', isset($staff->profile) ? $staff->profile->position : null, ['class' => 'form-control']) }}
    </div>
    <div class="form-group">
        {{ Form::label('on_board_date', '到職日') }}
        {{ Form::text('on_board_date', isset($staff->profile) ? $staff->profile->on_board_date : null, ['class' => 'form-control']) }}
    </div>
    <div class="form-group">
        {{ Form::label('birth', '生日') }}
        {{ Form::text('birth', isset($staff->profile) ? $staff->profile->birth : null, ['class' => 'form-control']) }}
    </div>
    <div class="form-group">
        {{ Form::label('salary', '月支薪俸') }}
        {{ Form::number('salary', isset($staff->profile) ? $staff->profile->salary : null, ['class' => 'form-control']) }}
    </div>
    <div class="form-row">
        <div class="col">
            {{ Form::label('add_insurance_date', '加保日期') }}
            {{ Form::text('add_insurance_date', isset($staff->profile) ? $staff->profile->add_insurance_date : null, ['class' => 'form-control']) }}
        </div>
        <div class="col">
            {{ Form::label('cancel_insurance_date', '退保日期') }}
            {{ Form::text('cancel_insurance_date', isset($staff->profile) ? $staff->profile->cancel_insurance_date : null, ['class' => 'form-control']) }}
        </div>
    </div>
    <div class="form-group">
        {{ Form::label('highest_education', '最高學歷') }}
        {{ Form::text('highest_education', isset($staff->profile) ? $staff->profile->highest_education : null, ['class' => 'form-control']) }}
    </div>
    <div class="form-group">
        {{ Form::label('experience', '經歷') }}
        {{ Form::text('experience', isset($staff->profile) ? $staff->profile->experience : null, ['class' => 'form-control']) }}
    </div>
    <div class="form-group">
        {{ Form::label('group', '組別') }}
        {{ Form::text('group', isset($staff->profile) ? $staff->profile->group : null, ['class' => 'form-control']) }}
    </div>

    <nav class="navbar navbar-expand-lg bottom-navbar">
        <div class="collapse navbar-collapse">
            <ul class="navbar-nav mr-auto">
                <li class="nav-item">
                    <a class="nav-link btn btn-secondary" href="{{ route('admin.staff.index') }}">返回</a>
                </li>
                <li class="nav-item">
                    {{ Form::submit('送出', ['class' => 'nav-link btn btn-primary']) }}
                </li>
            </ul>
        </div>
    </nav>
    {{ Form::close() }}
@endsection
@section('scripts')
    <script>
        $(document).ready( function () {
            var staff_form = $('#staffForm');
            staff_form.find('input[name="on_board_date"]').daterangepicker({
                locale: {
                  format: 'YYYY-MM-DD'
                },
                singleDatePicker: true,
                showDropdowns: true
            }); 
            staff_form.find('input[name="birth"]').daterangepicker({
                locale: {
                  format: 'YYYY-MM-DD'
                },
                singleDatePicker: true,
                showDropdowns: true
            });
            staff_form.find('input[name="add_insurance_date"]').daterangepicker({
                locale: {
                  format: 'YYYY-MM-DD'
                },
                singleDatePicker: true,
                showDropdowns: true
            });
            staff_form.find('input[name="cancel_insurance_date"]').daterangepicker({
                locale: {
                  format: 'YYYY-MM-DD'
                },
                singleDatePicker: true,
                showDropdowns: true
            });
        
            var validator = staff_form.validate({
                errorClass: "alert alert-danger",
                rules: {
                    email:          { required: true, email: true },
                    name:           { required: true},
                    ID_card_number: { required: true}
                },
                messages: {
                    email: {required: "請輸入email", email: "請填入有效的email"},
                    name:  {required: "請輸入姓名"},
                    ID_card_number: {required: "請輸入身分證字號或居留證號碼"}
                }
            });
            $.validator.addMethod(
                    "regex",
                    function(value, element, regexp) {
                        var re = new RegExp(regexp);
                        return this.optional(element) || re.test(value);
                    }
            );
            staff_form.find("#ID_card_number").rules("add", { 
                regex: function () {
                    return "(^[a-zA-Z]{1}[abcdABCD]{1}[0-9]{8}$|^[a-zA-Z]{1}[1-2]{1}[0-9]{8}$)"; 
                },
                messages: {
                    regex: "請填入有效的身分證字號或居留證號碼"
                }
             });
            validator.showErrors();
        
        });
    </script>
@endsection

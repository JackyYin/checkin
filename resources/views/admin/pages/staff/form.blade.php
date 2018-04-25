@extends('admin.layouts.master')

@section('content')
    {{ Form::open([
        'id'  => 'staffForm',
        'url' => route('admin.staff.store')
    ]) }}

    {{ Form::label('name', '姓名') }}
    {{ Form::text('name') }}
    {{ Form::label('email', 'email') }}
    {{ Form::text('email') }}
    {{ Form::label('staff_code', '員工編號') }}
    {{ Form::text('staff_code') }}
    {{ Form::label('ID_card_number', '身分證字號') }}
    {{ Form::text('ID_card_number', null, ['id' => 'ID_card_number']) }}
    {{ Form::label('gender', '性別') }}
    {{ Form::label('gender', '男') }}
    {{ Form::radio('gender', 1, true) }}
    {{ Form::label('gender', '女') }}
    {{ Form::radio('gender', 0) }}
    {{ Form::label('phone_number', '電話號碼') }}
    {{ Form::text('phone_number') }}
    {{ Form::label('home_address', '戶籍地址') }}
    {{ Form::text('home_address') }}
    {{ Form::label('mailing_address', '通訊地址') }}
    {{ Form::text('mailing_address') }}
    {{ Form::label('bank_account', '銀行帳號') }}
    {{ Form::text('bank_account') }}
    {{ Form::label('emergency_contact', '緊急聯絡人/關係') }}
    {{ Form::text('emergency_contact') }}
    {{ Form::label('emergency_contact_phone', '緊急聯絡電話') }}
    {{ Form::text('emergency_contact_phone') }}
    {{ Form::label('position', '職稱') }}
    {{ Form::text('position') }}
    {{ Form::label('on_board_date', '到職日') }}
    {{ Form::text('on_board_date') }}
    {{ Form::label('birth', '生日') }}
    {{ Form::text('birth') }}
    {{ Form::label('salary', '月支薪俸') }}
    {{ Form::number('salary') }}
    {{ Form::label('add_insurance_date', '加保日期') }}
    {{ Form::text('add_insurance_date') }}
    {{ Form::label('cancel_insurance_date', '退保日期') }}
    {{ Form::text('cancel_insurance_date') }}
    {{ Form::label('highest_education', '最高學歷') }}
    {{ Form::text('highest_education') }}
    {{ Form::label('experience', '經歷') }}
    {{ Form::text('experience') }}
    {{ Form::label('group', '組別') }}
    {{ Form::text('group') }}

    {{ Form::submit('送出') }}
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
                rules: {
                    email:          { required: true, email: true },
                    name:           { required: true},
                    ID_card_number: { required: true}
                },
                messages: {
                    email: {required: "請輸入email", email: "請填入有效的email"},
                    name:  {required: "請輸入姓名"},
                    ID_card_number: {required: "請輸入身分證字號"}
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
                regex: "^[a-zA-Z]{1}[1-2]{1}[0-9]{8}$",
                messages: {
                    regex: "請填入有效的身分證字號"
                }
             });
            validator.showErrors();
        
        });
    </script>
@endsection

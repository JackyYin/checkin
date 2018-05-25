<html>
    <head>
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
        {{ Html::style('https://cdn.jsdelivr.net/bootstrap.daterangepicker/2/daterangepicker.css') }}
        <link href="{!! asset('css/bootstrap-multiselect.css') !!}" media="all" rel="stylesheet" type="text/css" />
    </head>
    <body>
            @if ($admin)
                @include('admin.layouts.header')
            @endif
        <div class="container" style="padding-top: 20px">
            @include('admin.layouts.messages')
            @yield('content')
        </div>
    
        <script src="https://code.jquery.com/jquery-3.3.1.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
        <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
        {{ Html::script('https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.17.0/jquery.validate.min.js') }}
        {{ Html::script('https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.17.0/additional-methods.min.js') }}
        {{ Html::script('https://cdn.jsdelivr.net/momentjs/latest/moment.min.js') }}
        {{ Html::script('https://cdn.jsdelivr.net/bootstrap.daterangepicker/2/daterangepicker.js') }}
        <script type="text/javascript" src="{!! asset('js/bootstrap.js') !!}"></script>
        <script type="text/javascript" src="{!! asset('js/bootstrap-multiselect.js') !!}"></script>
        @yield('scripts')
    </body>
</html>

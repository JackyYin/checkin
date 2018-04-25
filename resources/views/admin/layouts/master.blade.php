<html>
    <head>
            {{ Html::style('https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css') }}
            {{ Html::style('https://cdn.jsdelivr.net/bootstrap.daterangepicker/2/daterangepicker.css') }}
    </head>
    <body>
        <div class="container">
            @if ($admin)
                @include('admin.layouts.header')
            @endif

            @include('admin.layouts.messages')
            @yield('content')
        </div>

        {{ Html::script('https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js') }}
        {{ Html::script('https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.17.0/jquery.validate.min.js') }}
        {{ Html::script('https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.17.0/additional-methods.min.js') }}
        {{ Html::script('https:////cdn.jsdelivr.net/momentjs/latest/moment.min.js') }}
        {{ Html::script('https:////cdn.jsdelivr.net/bootstrap.daterangepicker/2/daterangepicker.js') }}
        @yield('scripts')
    </body>
</html>

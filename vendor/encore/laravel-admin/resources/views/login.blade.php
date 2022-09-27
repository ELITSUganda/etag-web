<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>{{ config('admin.title') }} | {{ trans('admin.login') }}</title>
    <!-- Tell the browser to be responsive to screen width -->
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">

    @if (!is_null($favicon = Admin::favicon()))
        <link rel="shortcut icon" href="{{ $favicon }}">
    @endif

    <!-- Bootstrap 3.3.5.g -->
    <link rel="stylesheet" href="{{ admin_asset('vendor/laravel-admin/AdminLTE/bootstrap/css/bootstrap.min.css') }}">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="{{ admin_asset('vendor/laravel-admin/font-awesome/css/font-awesome.min.css') }}">
    <!-- Theme style -->
    <link rel="stylesheet" href="{{ admin_asset('vendor/laravel-admin/AdminLTE/dist/css/AdminLTE.min.css') }}">
    <!-- iCheck -->
    <link rel="stylesheet" href="{{ admin_asset('vendor/laravel-admin/AdminLTE/plugins/iCheck/square/blue.css') }}">

    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
  <script src="//oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
  <script src="//oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
  <![endif]-->

    <style>
        .login-box {
            overflow: auto;
            color: white;
            position: relative;
            padding: 1rem;
        }
    </style>

</head>

<body class="hold-transition login-page"
    @if (config('admin.login_background_image')) style="background: #EDE0D0;  background-position: center;" @endif>
    <div class="login-box">

        <!-- /.login-logo -->
        <div class="login-box-body" style="text-align: center;">
            <p class="login-box-msg">UGANDA LIVESTOCK IDENTIFICATION & TRACEABILITY SYSTEM</p>
            <img src="{{ url('assets/images/logo.png') }}" width="120">
            <br>
            <br>
            <div style="height: 3px; width:100%; background: black;"> </div>
            <div style="height: 3px; width:100%; background: yellow;"> </div>
            <div style="height: 3px; width:100%; background: red;"> </div>
            <br>

            <p class="login-box-msg"><b>{{ trans('admin.login') }}</b></p>

            <form action="{{ admin_url('auth/login') }}" method="post">
                <div class="form-group has-feedback {!! !$errors->has('username') ?: 'has-error' !!}">

                    @if ($errors->has('username'))
                        @foreach ($errors->get('username') as $message)
                            <label class="control-label" for="inputError"><i
                                    class="fa fa-times-circle-o"></i>{{ $message }}</label><br>
                        @endforeach
                    @endif

                    <input type="text" class="form-control" placeholder="{{ trans('admin.username') }}"
                        name="username" value="{{ old('username') }}">
                    <span class="glyphicon glyphicon-envelope form-control-feedback"></span>
                </div>
                <div class="form-group has-feedback {!! !$errors->has('password') ?: 'has-error' !!}">

                    @if ($errors->has('password'))
                        @foreach ($errors->get('password') as $message)
                            <label class="control-label" for="inputError"><i
                                    class="fa fa-times-circle-o"></i>{{ $message }}</label><br>
                        @endforeach
                    @endif

                    <input type="password" class="form-control" placeholder="{{ trans('admin.password') }}"
                        name="password">
                    <span class="glyphicon glyphicon-lock form-control-feedback"></span>
                </div>
                <div class="row">

                    <div class="col-xs-12">
                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                        <button type="submit"
                            class="btn btn-primary btn-block btn-flat">{{ trans('admin.login') }}</button>
                    </div>
                    <br>
                    <br>
                    <div class="col-xs-12">

                        Don't have account? <a href="{{ url('register') }}">REGISTER HERE</a>
                        <input type="hidden" name="remember" value="1"
                            {{ !old('username') || old('remember') ? 'checked' : '' }}>

                    </div>
                </div>
            </form>

        </div>
        <!-- /.login-box-body -->
    </div>
    <!-- /.login-box -->

    <!-- jQuery 2.1.4 -->
    <script src="{{ admin_asset('vendor/laravel-admin/AdminLTE/plugins/jQuery/jQuery-2.1.4.min.js') }} "></script>
    <!-- Bootstrap 3.3.5 -->
    <script src="{{ admin_asset('vendor/laravel-admin/AdminLTE/bootstrap/js/bootstrap.min.js') }}"></script>
    <!-- iCheck -->
    <script src="{{ admin_asset('vendor/laravel-admin/AdminLTE/plugins/iCheck/icheck.min.js') }}"></script>
    <script>
        $(function() {
            $('input').iCheck({
                checkboxClass: 'icheckbox_square-blue',
                radioClass: 'iradio_square-blue',
                increaseArea: '20%' // optional
            });
        });
    </script>
</body>

</html>

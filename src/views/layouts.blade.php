<html>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <!-- 上述3个meta标签*必须*放在最前面，任何其他内容都*必须*跟随其后！ -->
    <title>数据库字典</title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@3.3.7/dist/css/bootstrap.css" rel="stylesheet">

    <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
    <script src="https://cdn.bootcss.com/html5shiv/3.7.3/html5shiv.min.js"></script>
    <script src="https://cdn.bootcss.com/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->
    <!-- 开发环境版本，包含了有帮助的命令行警告 -->
    <script src="https://cdn.jsdelivr.net/npm/vue/dist/vue.js"></script>
    <!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
    <script src="https://cdn.jsdelivr.net/npm/jquery@1.12.4/dist/jquery.js"></script>
    <!-- Include all compiled plugins (below), or include individual files as needed -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@3.3.7/dist/js/bootstrap.js"></script>
    <style>
        #body {
            margin: 0 5%;
            width: 90%;
        }
        .modal input.form-control{
            width: 70%;
            display: inline-block;
        }
        .plane-fade-enter-active, .plane-fade-leave-active {
            /*transition: opacity 1s;*/
        }
        .plane-fade-enter, .plane-fade-leave-to /* .fade-leave-active below version 2.1.8 */ {
            /*opacity: 0;*/
            /*transition: opacity 0.1s;*/
        }
    </style>

</head>
<body>
<div class="container-fluid">
    <div class="row" id="body">
        @include('database_dictionary::header')
        @include('database_dictionary::navigation')
        <br>
        @include('database_dictionary::panel')
        @include('database_dictionary::modal')
    </div>
</div>
@section('js')@show
</body>
</html>
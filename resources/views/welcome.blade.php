<!DOCTYPE html>
<html lang="{{ config('app.locale') }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>PDF GENERATOR</title>

    <script src="https://code.jquery.com/jquery-3.2.1.min.js"
            integrity="sha256-hwg4gsxgFZhOsEEamdOYGBf13FyQuiTwlAQgxVSNgt4="
            crossorigin="anonymous"></script>

    <!-- Latest compiled and minified CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css"
          integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">

    <!-- Optional theme -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap-theme.min.css"
          integrity="sha384-rHyoN1iRsVXV4nD0JutlnGaslCJuC7uwjduW9SVrLvRYooPp2bWYgmgJQIXwl/Sp" crossorigin="anonymous">

    <!-- Latest compiled and minified JavaScript -->
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"
            integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa"
            crossorigin="anonymous"></script>
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css?family=Raleway:100,600" rel="stylesheet" type="text/css">

    <link href="/font-awesome/css/font-awesome.css" rel="stylesheet">

    <!-- Styles -->
    <style>
        html, body {
            background-color: #fff;
            height: 100vh;
            margin: 0;
        }

        .full-height {
            height: 100vh;
        }

        .flex-center {
            align-items: center;
            display: flex;
            justify-content: center;
        }

        .position-ref {
            position: relative;
        }

        .top-right {
            position: absolute;
            right: 10px;
            top: 18px;
        }

        .content {
            text-align: center;
            position: relative;
            bottom: 100px;
        }

        .title {
            color: #636b6f;
            font-family: 'Raleway', sans-serif;
            font-weight: 100;
            font-size: 60px;
        }

        .links > a {
            color: #636b6f;
            padding: 0 25px;
            font-size: 12px;
            font-weight: 600;
            letter-spacing: .1rem;
            text-decoration: none;
            text-transform: uppercase;
        }

        .m-b-md {
            margin-bottom: 30px;
        }

        .hide {
            display: none !important;
        }
    </style>
</head>
<body>
<div class="container-fluid flex-center position-ref full-height">
    <div class="content">
        <div class="title m-b-md">
            PDF GENERATOR
        </div>
        <div>
            <form data-url="{{ url('convert') }}" id="form">
                <div class="form-group">
                    <input class="form-control" name="url" placeholder="http:://wwww.example.com">
                </div>
                <span class="loader text-success hide" style="font-size: 20px;">
                    <i class="fa fa-spinner fa-spin"></i>
                </span>
            </form>
        </div>
        <div class="hide" id="generate" style="background: rgba(221, 221, 221, 0.31); min-height: 150px;width:100%;text-align: left;
    padding: 15px;">
            <small class="text-muted">
                Your file has been generated. <span id="file_name"></span>
            </small>
            <br/> <br/>
            <a class="btn btn-sm btn-info">
                Download File
            </a>
        </div>
        <div class="hide alert alert-danger" id="error" role="alert">
            <strong>Oh Snap!</strong>. An error occured please make sure your url is valid
        </div>

    </div>
</div>
</body>
</html>
<script>
    $(document).ready(function () {
        $('#form').submit(function (e) {
            e.preventDefault();
            e.stopPropagation();
            var cur = $(this);
            cur.find('.loader').removeClass('hide');
            $('#generate').addClass('hide');
            $('#error').addClass('hide');
            $.ajax({
                url: cur.data('url'),
                method: 'POST',
                data: cur.serialize(),
                success: function (res) {
                    var div = $('#generate').removeClass('hide');
                    div.find('#file_name').html(res.name);
                    div.find('a').attr('href', res.url);
                    cur.find('.loader').addClass('hide');
                },
                error: function (res) {
                    cur.find('.loader').addClass('hide');
                    $('#error').removeClass('hide');
                }

            });
        })
    });
</script>

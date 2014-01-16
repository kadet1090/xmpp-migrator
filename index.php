<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>Starter Template for Bootstrap</title>

    <!-- Bootstrap core CSS -->
    <link href="dist/css/bootstrap.css" rel="stylesheet">

    <!-- Custom styles for this template -->
    <link href="style.css" rel="stylesheet">

    <!-- HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
    <script src="https://oss.maxcdn.com/libs/respond.js/1.3.0/respond.min.js"></script>
    <![endif]-->
</head>

<body>

<div class="navbar navbar-inverse navbar-fixed-top" role="navigation">
    <div class="container">
        <div class="navbar-header">
            <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
                <span class="sr-only">Toggle navigation</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
            <a class="navbar-brand" href="#">Project name</a>
        </div>
        <div class="collapse navbar-collapse">
            <ul class="nav navbar-nav">
                <li class="active"><a href="#">Home</a></li>
                <li><a href="#about">About</a></li>
                <li><a href="#contact">Contact</a></li>
            </ul>
        </div><!--/.nav-collapse -->
    </div>
</div>

<div class="container">
    <h1>Przemigruj swoje konto XMPP/Jabber!</h1>
    <div class="row">
        <div class="col-md-6">
            <h2>Z...</h2>
            <form id="xmpp-from">
                <div class="input-group">
                    <span class="input-group-addon"><i class="glyphicon glyphicon-user"></i></span>
                    <input type="email" class="form-control first" placeholder="użytkownik@serwer.pl" />
                    <input type="password" class="form-control last" placeholder="hasło" />
                </div>
            </form>
        </div>
        <div class="col-md-6">
            <h2>Na...</h2>
            <form id="xmpp-to">
                <div class="input-group">
                    <input type="email" class="form-control first" placeholder="użytkownik@serwer.pl" />
                    <input type="password" class="form-control last" placeholder="hasło" />
                    <span class="input-group-addon"><i class="glyphicon glyphicon-user"></i></span>

                </div>
            </form>
        </div>
    </div>
    <div class="row settings">
        <div class="col-md-6 col-md-offset-3 well">
            <form id="xmpp-settings">
                <label for="message">
                    <abbr title="Możesz używać %new, które zostanie zastąpione Twoim nowym jid oraz %old, które zostanie zastąpione Twoim starym jid.">Wysyłana wiadomość (zostaw puste aby użyć domyślną wiadomość):</abbr>
                </label>
                <div class="input-group">
                    <span class="input-group-addon">
                        <input type="checkbox" checked="checked" id="message-c">
                    </span>
                    <input type="text" class="form-control" placeholder="Np. Moje nowe jid to %new!" id="message">
                </div><!-- /input-group -->
                <br />
                <label for="status">
                    <abbr title="Możesz używać %new, które zostanie zastąpione Twoim nowym jid oraz %old, które zostanie zastąpione Twoim starym jid.">Ustawiany opis (zostaw puste aby użyć domyślnego opisu):</abbr>
                </label>
                <div class="input-group">
                    <span class="input-group-addon">
                        <input type="checkbox" checked="checked" id="status-c">
                    </span>
                    <input type="text" class="form-control" placeholder="Np. Moje nowe jid to %new!" id="status">
                </div><!-- /input-group -->
                <br />
                <input type="checkbox" checked="checked" id="roster"> <label for="roster">Przekopiuj <abbr title="Lista kontaktów">roster</abbr></label>
            </form>
        </div>
    </div>
    <div class="row">
        <div class="col-md-6 col-md-offset-3" id="messages">
        </div>
    </div>
    <div class="row">
        <div class="col-md-6 col-md-offset-3">
            <button id="migrate" class="btn btn-success btn-lg btn-block">Migruj</button>
        </div>
    </div>
</div><!-- /.container -->


<!-- Bootstrap core JavaScript
================================================== -->
<!-- Placed at the end of the document so the pages load faster -->
<script src="https://code.jquery.com/jquery-1.10.2.min.js"></script>
<script src="dist/js/bootstrap.min.js"></script>
<script type="text/javascript">
    $(document).ready(function() {
        mid = '';

        $('#migrate').click(function () {
            $.ajax({
                type: "POST",
                url: "migrate.php",
                data: {
                    from:    $('#xmpp-from .first').val() + ':' + $('#xmpp-from .last').val(),
                    to:      $('#xmpp-to .first').val()   + ':' + $('#xmpp-to .last').val(),
                    message: $('#message-c').is(':checked') ? ($('#message').length > 0 ? $('#message').val() : '') : 'no',
                    status:  $('#status-c').is(':checked') ? ($('#status').length > 0 ? $('#status').val() : '') : 'no',
                    roster:  $('#roster').is(':checked') ? 'yes' : 'no'
                }
            }).done(function(msg) {
                mid = msg;
                wait();
            });
        });

        wait = null;
        wait = function() {
            $.ajax({
                type: "GET",
                url: "log.php",
                async: true, /* If set to non-async, browser shows page as "Loading.."*/
                cache: false,
                timeout: 2880000, /* Timeout in ms set to 8 hours */
                data: {
                    mid: mid
                }
            }).done(function(data){ /* called when request to barge.php completes */
                $("#messages").html(data); /* Add response to a .msg div (with the "new" class)*/
                setTimeout(
                    'wait()', /* Request next message */
                    1000
                );
            });
        }
    });
</script>
</body>
</html>

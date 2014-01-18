<div class="container">
    <h1>Kontakt</h1>
    Możesz się ze mną skontaktować poprzez:
    <ul>
        <li>Jabber/XMPP: <a href="xmpp:kadet@wtw.im">kadet@wtw.im</a></li>
        <li>GG: <a href="gg:12881099">12881099</a></li>
        <li>e-mail: <a href="mailto:kadet1090@gmail.com">kadet1090@gmail.com</a></li>
    </ul>
    Bądź też skorzystać z poniższego formularza:
    <?php
        function send() {
            if(isset($_POST['subject'])) {
                $error = false;

                if(empty($_POST['subject'])) {
                    echo '<div class="alert alert-danger">Temat nie został podany.</div>';
                    $error = true;
                };
                if(empty($_POST['mail'])) {
                    echo '<div class="alert alert-danger">E-mail nie został podany.</div>';
                    $error = true;
                };
                if(empty($_POST['content'])) {
                    echo '<div class="alert alert-danger">Treść nie została podana.</div>';
                    $error = true;
                };

                if($error) return;

                if(!filter_var($_POST['mail'], FILTER_VALIDATE_EMAIL)) {
                    echo '<div class="alert alert-danger">Temat nie został podany</div>';
                    $error = true;
                };

                if($error) return;

                if(mail(
                    'kadet1090@gmail.com',
                    "[Migrator] ".$_POST['subject'],
                    $_POST['content'],
                    'Content-type: text/plain; charset=utf-8' . "\r\n" .
                    "Reply-To: {$_POST['mail']}" . "\r\n" .
                    'X-Mailer: PHP/' . phpversion()
                )) {
                    echo '<div class="alert alert-success">Wiadomość została wysłana, wkrótce postaram się odpowiedzieć :)</div>';
                } else {
                    echo '<div class="alert alert-danger">Wiadomość nie została wysłana z powodu błędu, spróbuj ponownie później, bądź napisz do mnie samodzielnie.</div>';
                }
            }
        }

        send();
    ?>
    <form method="post" action="index.php?q=contact">
        <div class="row">
            <div class="col-sm-4">
                <div class="input-group">
                    <span class="input-group-addon"><i class="glyphicon glyphicon-user"></i></span>
                    <input type="email" class="form-control" placeholder="użytkownik@serwer.pl" name="mail"/>
                </div>
            </div>
            <div class="col-sm-8">
                <div class="input-group">
                    <span class="input-group-addon">Temat</span>
                    <input type="text" class="form-control" placeholder="Temat" name="subject"/>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-12">
                <textarea class="form-control" name="content"></textarea>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-12">
                <button type="submit" class="pull-right btn btn-primary">Wyślij</button>
            </div>
        </div>
    </form>
</div>
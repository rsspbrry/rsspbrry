<!DOCTYPE html>
<html
    <?php if (Model\Config\is_language_rtl()): ?>
        dir="rtl"
    <?php endif ?>
>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width">
        <meta name="mobile-web-app-capable" content="yes">
        <meta name="robots" content="noindex,nofollow">
        <meta name="referrer" content="no-referrer">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">

        <link rel="icon" type="image/png" href="assets/img/favicon.png">
        <link rel="shortcut icon" href="favicon.ico">
        <link rel="apple-touch-icon" href="assets/img/touch-icon-iphone.png">
        <link rel="apple-touch-icon" sizes="72x72" href="assets/img/touch-icon-ipad.png">
        <link rel="apple-touch-icon" sizes="114x114" href="assets/img/touch-icon-iphone-retina.png">
        <link rel="apple-touch-icon" sizes="144x144" href="assets/img/touch-icon-ipad-retina.png">
        <title>RSSPBRRY</title>
        <link href="<?= Helper\css() ?>" rel="stylesheet" media="screen">
    </head>
    <body id="login-page">
        <section class="page" id="login">
            <?php if (isset($errors['login'])): ?>
                <p class="alert alert-error"><?= Helper\escape($errors['login']) ?></p>
            <?php endif ?>

            <form method="post" action="?action=login">

                <?= Helper\form_hidden('csrf', $values) ?>

                <?= Helper\form_label(t('Username'), 'username') ?>
                <?= Helper\form_text('username', $values, $errors, array('autofocus', 'required')) ?><br/>

                <?= Helper\form_label(t('Password'), 'password') ?>
                <?= Helper\form_password('password', $values, $errors, array('required')) ?>

                <?= Helper\form_checkbox('remember_me', t('Remember Me'), 1) ?><br/>

                <?php if (ENABLE_MULTIPLE_DB && count($databases) > 1): ?>
                <div id="database-selector">
                    <h4><?= t('Select another database') ?></h4>
                    <?php foreach ($databases as $filename => $dbname): ?>
                        <?= Helper\form_radio('database', $dbname, $filename, ($current_database === $filename)) ?>
                    <?php endforeach ?>
                </div>
                <?php endif ?>


                <div class="form-actions">
                    <input type="submit" value="<?= t('Sign in') ?>" class="btn btn-purple"/>
                </div>
            </form>

        </section>
    </body>
</html>

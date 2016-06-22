<div class="page-header">
    <h2><?= $title ?></h2>
    <nav>
        <ul>
            <li><a href="?action=config"><?= t('general') ?></a></li>
            <li><a href="?action=services"><?= t('external services') ?></a></li>
            <li><a href="?action=api"><?= t('api') ?></a></li>
            <li><a href="?action=database"><?= t('database') ?></a></li>
            <li class="active"><a href="?action=help"><?= t('help') ?></a></li>
            <li><a href="?action=about"><?= t('about') ?></a></li>
        </ul>
    </nav>
</div>
<section>
    <?= \Template\load('keyboard_shortcuts') ?>
</section>
<div class="page-section">
    <h2><?= t('Documentation') ?></h2>
</div>
<section>
<ul>
    <li><a href="https://miniflux.net/documentation/installation" rel="noreferrer" target="_blank"><?= t('Installation instructions') ?></a></li>
    <li><a href="https://miniflux.net/documentation/upgrade" rel="noreferrer" target="_blank"><?= t('Upgrade to a new version') ?></a></li>
    <li><a href="https://miniflux.net/documentation/cronjob" rel="noreferrer" target="_blank"><?= t('Cronjob') ?></a></li>
    <li><a href="https://miniflux.net/documentation/config" rel="noreferrer" target="_blank"><?= t('Advanced configuration') ?></a></li>
    <li><a href="https://miniflux.net/documentation/full-article-download" rel="noreferrer" target="_blank"><?= t('Full article download') ?></a></li>
    <li><a href="https://miniflux.net/documentation/multiple-users" rel="noreferrer" target="_blank"><?= t('Multiple users') ?></a></li>
    <li><a href="https://miniflux.net/documentation/themes" rel="noreferrer" target="_blank"><?= t('Themes') ?></a></li>
    <li><a href="https://miniflux.net/documentation/json-rpc-api" rel="noreferrer" target="_blank"><?= t('Json-RPC API') ?></a></li>
    <li><a href="https://miniflux.net/documentation/fever" rel="noreferrer" target="_blank"><?= t('Fever API') ?></a></li>
    <li><a href="https://miniflux.net/documentation/translations" rel="noreferrer" target="_blank"><?= t('Translations') ?></a></li>
    <li><a href="https://miniflux.net/documentation/docker" rel="noreferrer" target="_blank"><?= t('Run with Docker') ?></a> (Miniflux)</li>
    <li><a href="https://miniflux.net/documentation/faq" rel="noreferrer" target="_blank"><?= t('FAQ') ?></a></li>
</ul>
</section>
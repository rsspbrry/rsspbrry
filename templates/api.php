<div class="page-header">
    <h2><?= $title ?></h2>
    <nav>
        <ul>
            <li><a href="?action=config"><?= t('general') ?></a></li>
            <li><a href="?action=services"><?= t('external services') ?></a></li>
            <li class="active"><a href="?action=api"><?= t('api') ?></a></li>
            <li><a href="?action=database"><?= t('database') ?></a></li>
            <li><a href="?action=help"><?= t('help') ?></a></li>
            <li><a href="?action=about"><?= t('about') ?></a></li>
        </ul>
    </nav>
</div>
<section>
    <div class="panel panel-default">
        <h3 id="fever"><?= t('Fever API') ?></h3>
        <ul>
            <li><?= t('API endpoint:') ?> <strong><?= Helper\get_current_base_url().'fever/' ?></strong></li>
            <li><?= t('API username:') ?> <strong><?= Helper\escape($config['username']) ?></strong></li>
            <li><?= t('API token:') ?> <strong><?= Helper\escape($config['fever_token']) ?></strong></li>
        </ul>
    </div>
    <div class="panel panel-default">
        <h3 id="api"><?= t('RSSPBRRY API') ?></h3>
        <ul>
            <li><?= t('API endpoint:') ?> <strong><?= Helper\get_current_base_url().'jsonrpc.php' ?></strong></li>
            <li><?= t('API username:') ?> <strong><?= Helper\escape($config['username']) ?></strong></li>
            <li><?= t('API token:') ?> <strong><?= Helper\escape($config['api_token']) ?></strong></li>
        </ul>
    </div>
</section>

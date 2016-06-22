<div class="page-header">
    <h2><?= t('OPML Import') ?></h2>
    <nav>
        <ul>
            <li><a href="?action=add"><?= t('add') ?></a></li>
            <li><a href="?action=feeds"><?= t('feeds') ?></a></li>
            <li class="active"><a href="?action=import"><?= t('import') ?></a></li>
            <li><a href="?action=export"><?= t('export') ?></a></li>
        </ul>
    </nav>
</div>

<form method="post" action="?action=import" enctype="multipart/form-data">
    <label for="file"><?= t('OPML file') ?></label>
    <input type="file" name="file" required/>
    <div class="form-actions">
        <button type="submit" class="btn btn-purple"><?= t('Import') ?></button>
        <?= t('or') ?> <a href="?action=feeds"><?= t('cancel') ?></a>
    </div>
</form>
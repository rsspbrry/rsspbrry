<div class="page-header">
    <h2><?= t('Subscriptions') ?></h2>
    <nav>
        <ul>
            <li><a href="?action=add"><?= t('add') ?></a></li>
            <li class="active"><a href="?action=feeds"><?= t('feeds') ?></a></li>
            <li><a href="?action=refresh-all" data-action="refresh-all" data-concurrent-requests="<?= SUBSCRIPTION_CONCURRENT_REQUESTS ?>"><?= t('refresh') ?></a></li>
            <li><a href="?action=import"><?= t('import') ?></a></li>
            <li><a href="?action=export"><?= t('export') ?></a></li>
        </ul>
    </nav>
</div>

<?php if (empty($feeds)): ?>

    <p class="alert alert-info"><?= t('No subscription') ?></p>

<?php else: ?>

    <?php if ($nb_failed_feeds > 0): ?>
        <?php // the following error line has been removed on request from customer ?>
        <!-- <p class="alert alert-error"><?php //<?= tne('An error occurred during the last check. Refresh the feed manually and check the %sconsole%s for errors afterwards!', '<a href="?action=console">', '</a>') ?></p> -->
    <?php elseif ($nothing_to_read): ?>
        <p class="alert alert-info"><?= tne('Nothing to read, do you want to %supdate your subscriptions%s?','<a href="?action=refresh-all" data-action="refresh-all">','</a>') ?></p>
    <?php endif ?>

    <section class="items cols-2">
    <?php foreach ($feeds as $feed): ?>
        <article data-feed-id="<?= $feed['id'] ?>" <?= (! $feed['enabled']) ? 'data-feed-disabled="1"' : '' ?> <?= ($feed['parsing_error']) ? 'data-feed-error="1"' : '' ?>>
            <h2>
                <?php if (! $feed['enabled']): ?>
                    <span title="<?= t('Subscription disabled') ?>">✖</span>
                <?php endif ?>

                <?= Helper\favicon($favicons, $feed['id']) ?>

                <a href="?action=feed-items&amp;feed_id=<?= $feed['id'] ?>" title="<?= t('Show only this subscription') ?>"><?= Helper\escape($feed['title']) ?></a>
                &lrm;<span class="items-count"><?= $feed['items_unread'] .'/' . $feed['items_total'] ?></span>

                <?php if ($feed['enabled']): ?>

                    <br/>

                    <?php if ($feed['last_checked']): ?>
                        <time class="feed-last-checked" data-after-update="<?= t('updated just now') ?>">
                            <?= t('checked at').' '.dt('%e %B %Y %k:%M', $feed['last_checked']) ?>
                        </time>
                    <?php else: ?>
                        <span class="feed-last-checked" data-after-update="<?= t('updated just now') ?>">
                            <?= t('never updated after creation') ?>
                        </span>
                    <?php endif ?>

                    <span class="feed-parsing-error">
                            <?= t('(error occurred during the last check)') ?>
                    </span>

                <?php endif ?>
            </h2>
            <ul class="item-menu">

                <li>
                    <a href="<?= $feed['site_url'] ?>" rel="noreferrer" target="_blank"><?= Helper\get_host_from_url($feed['site_url']) ?></a>
                </li>

                <?php if ($feed['enabled']): ?>
                <li>
                    <a href="?action=refresh-feed&amp;feed_id=<?= $feed['id'] ?>" data-action="refresh-feed"><?= t('refresh') ?></a>
                </li>
                <?php endif ?>

                <li><a href="?action=edit-feed&amp;feed_id=<?= $feed['id'] ?>"><?= t('edit') ?></a></li>


            </ul>


                
            </div>

        </article>
    <?php endforeach ?>

 


    </section>

<?php endif ?>
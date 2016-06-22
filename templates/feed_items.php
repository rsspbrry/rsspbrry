<?php if (empty($items)): ?>
    <p class="alert alert-info">
        <?= tne('This subscription is empty, %sgo back to unread items%s','<a href="?action=unread">','</a>') ?>
    </p>
<?php else: ?>

    <div class="page-header">
        <h2><?= Helper\escape($feed['title']) ?>&lrm;<span id="page-counter"><?= isset($nb_items) ? $nb_items : '' ?></span></h2>
       
        <ul>
            <li>
                <a href="?action=refresh-feed&amp;feed_id=<?= $feed['id'] ?>&amp;redirect=feed-items"><?= t('refresh') ?></a>
            </li>
        </ul>
        
        <ul>
            <li>
                <a href="?action=feed-items&amp;feed_id=<?= $feed['id'] ?>&amp;order=updated&amp;direction=<?= $direction == 'asc' ? 'desc' : 'asc' ?>"><?= tne('sort by date %s(%s)%s', '<span class="hide-mobile">', $direction == 'desc' ? t('older first') : t('most recent first'), '</span>') ?></a>
            </li>
            </ul>
            <ul>
            <li>
                <a href="?action=mark-feed-as-read&amp;feed_id=<?= $feed['id'] ?>" data-action="mark-feed-read"><?= t('mark all as read') ?></a>
            </li>
        </ul>

    </div>

    <?php if ($feed['parsing_error']): ?>
        <p class="alert alert-error">
            <?= tne('An error occurred during the last check. Refresh the feed manually and check the %sconsole%s for errors afterwards!','<a href="?action=console">','</a>') ?>
        </p>
    <?php endif; ?>

    <section class="items" id="listing" data-feed-id="<?= $feed['id'] ?>">
        <?php foreach ($items as $item): ?>
            <?= \Template\load('item', array(
                'feed' => $feed,
                'item' => $item,
                'menu' => $menu,
                'offset' => $offset,
                'hide' => false,
                'display_mode' => $display_mode,
                'favicons' => $favicons,
                'original_marks_read' => $original_marks_read,
            )) ?>
        <?php endforeach ?>

        <div id="bottom-menu">
            <a href="?action=mark-feed-as-read&amp;feed_id=<?= $feed['id'] ?>" data-action="mark-feed-read"><?= t('mark all as read') ?></a>
        </div>
         <div class="right">
                visit <a href="http://rsspbrry.com"><u>rsspbrry.com</u></a> for the best in rss
            </div>

              <div class="right">
                <a href="http://rsspbrry.com"><img src="assets/img/wordmark.png"></a>
            </div>

        <?= \Template\load('paging', array('menu' => $menu, 'nb_items' => $nb_items, 'items_per_page' => $items_per_page, 'offset' => $offset, 'order' => $order, 'direction' => $direction, 'feed_id' => $feed['id'])) ?>
    </section>

<?php endif ?>
<?php if (empty($items)): ?>
    <p class="alert alert-info"><?= t('No bookmark') ?></p>
<?php else: ?>

    <div class="page-header">
        <h2><?= t('Bookmarks') ?><span id="page-counter"><?= isset($nb_items) ? $nb_items : '' ?></span></h2>
    </div>

    <?php if ($nothing_to_read): ?>
        <p class="alert alert-info"><?= t('There is nothing new to read, enjoy your favorites articles!') ?></p>
    <?php endif ?>

    <section class="items" id="listing">
        <?php foreach ($items as $item): ?>
            <?= \Template\load('item', array(
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
                <a href="?action=mark-all-read<?= is_null($group_id) ? '' : '&amp;group_id='.$group_id ?>"><?= t('') ?></a>
            </div>
            
            <div class="right">
                <a href="http://rsspbrry.com"><img src="assets/img/wordmark.png"></a>
            </div>

        <?= \Template\load('paging', array('menu' => $menu, 'nb_items' => $nb_items, 'items_per_page' => $items_per_page, 'offset' => $offset, 'order' => $order, 'direction' => $direction)) ?>
    </section>

<?php endif ?>
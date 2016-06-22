

    <div class="page-header">
        <h2><?= t('Unread') ?><span id="page-counter"><?= isset($nb_items) ? $nb_items : '' ?></span></h2>
        <?php if (!empty($groups)): ?>
        <nav>
            <ul id="grouplist">
                <?php foreach ($groups as $group): ?>
                <li  <?= $group['id'] == $group_id ? 'class="active"' : '' ?>>
                    <a class="btn" href="?action=unread&group_id=<?=$group['id']?>"><?=$group['title']?></a>
                </li>
                <?php endforeach ?>
            </ul>
        </nav>
        <?php endif ?>

       
        <ul>
            <li>
                <a href="?action=unread<?= is_null($group_id) ? '' : '&amp;group_id='.$group_id ?>&amp;order=updated&amp;direction=<?= $direction == 'asc' ? 'desc' : 'asc' ?>"><?= tne('sort by date %s%s%s', '<span class="hide-mobile">',$direction == 'desc' ? t('(older first)') : t('most recent first'), '</span>') ?></a>
            </li>

        </ul>
        <ul>
            <li>
                <a href="?action=mark-all-read<?= is_null($group_id) ? '' : '&amp;group_id='.$group_id ?>"><?= t('mark all as read') ?></a>
            </li>
        </ul>
         
        
    </div>

    <section class="items" id="listing">
        <?php if (empty($items)): ?>
            <p class="alert alert-info"><?= t('Nothing to read') ?></p>
        <?php else: ?>
            <?php foreach ($items as $item): ?>
                <?= \Template\load('item', array(
                    'item' => $item,
                    'menu' => $menu,
                    'offset' => $offset,
                    'hide' => true,
                    'display_mode' => $display_mode,
                    'favicons' => $favicons,
                    'original_marks_read' => $original_marks_read,
                    'group_id' => $group_id,
                )) ?>
            <?php endforeach ?>

            <div id="bottom-menu">
                <a href="?action=mark-all-read<?= is_null($group_id) ? '' : '&amp;group_id='.$group_id ?>"><?= t('mark all as read') ?></a>
            </div>
            
            <div class="right">
                <a href="http://rsspbrry.com"><img src="assets/img/wordmark.png"></a>
            </div>

            <?= \Template\load('paging', array('menu' => $menu, 'nb_items' => $nb_items, 'items_per_page' => $items_per_page, 'offset' => $offset, 'order' => $order, 'direction' => $direction, 'group_id' => $group_id)) ?>
        <?php endif ?>
    </section>

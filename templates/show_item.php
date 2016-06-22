<?php if (empty($item)): ?>
    <p class="alert alert-error"><?= t('Item not found') ?></p>
<?php else: ?>
    <article
        class="item"
        id="current-item"
        data-item-id="<?= $item['id'] ?>"
        data-item-status="<?= $item['status'] ?>"
        data-item-bookmark="<?= $item['bookmark'] ?>"
    >

        <?php if (isset($item_nav)): ?>
        <nav class="top">
            <span class="nav-left">
                <?php if ($item_nav['previous']): ?>
                    <a href="?action=show&amp;menu=<?= $menu ?><?= $group_id ? '&amp;group_id='.$group_id : '' ?>&amp;id=<?= $item_nav['previous']['id'] ?>" id="previous-item" title="<?= Helper\escape($item_nav['previous']['title']) ?>"><?= t('Previous') ?></a>
                <?php else: ?>
                    <?= t('Previous') ?>
                <?php endif ?>
            </span>

            <span class="nav-right">
                <?php if ($item_nav['next']): ?>
                    <a href="?action=show&amp;menu=<?= $menu ?><?= $group_id ? '&amp;group_id='.$group_id : '' ?>&amp;id=<?= $item_nav['next']['id'] ?>" id="next-item" title="<?= Helper\escape($item_nav['next']['title']) ?>"><?= t('Next') ?></a>
                <?php else: ?>
                    <?= t('Next') ?>
                <?php endif ?>
            </span>
        </nav>
        <?php endif ?>

        <h1 <?= Helper\is_rtl($item + array('rtl' => $feed['rtl'])) ? 'dir="rtl"' : 'dir="ltr"' ?>>
            <a href="<?= $item['url'] ?>" rel="noreferrer" target="_blank" class="original"><?= Helper\escape($item['title']) ?></a>
        </h1>

        <ul class="item-infos">
            <li>
                <a
                    class="bookmark-icon icon"
                    href="?action=bookmark&amp;value=<?= (int)!$item['bookmark'] ?>&amp;id=<?= $item['id'] ?>&amp;source=show&amp;menu=<?= $menu ?>"
                    title="<?= ($item['bookmark']) ? t('remove bookmark') : t('bookmark') ?>"
                    data-reverse-title="<?= ($item['bookmark']) ? t('bookmark') :t('remove bookmark') ?>"
                    data-action="bookmark"
                ></a>
            </li>
            <li>
                <a href="?action=feed-items&amp;feed_id=<?= $feed['id'] ?>"><?= Helper\escape($feed['title']) ?></a>
            </li>
            <?php if (!empty($item['author'])): ?>
                <li>
                    <?= Helper\escape($item['author']) ?>
                </li>
            <?php endif ?>
            <li class="hide-mobile">
                <span title="<?= dt('%e %B %Y %k:%M', $item['updated']) ?>"><?= Helper\relative_time($item['updated']) ?></span>
            </li>
            <?php if ($item['enclosure']): ?>
            <li>
                <a href="<?= $item['enclosure'] ?>" rel="noreferrer" target="_blank"><?= t('attachment') ?></a>
            </li>
            <?php endif ?>
            <li class="hide-mobile">
                <span id="download-item"
                      data-failure-message="<?= t('unable to fetch content') ?>"
                      data-before-message="<?= t('in progress...') ?>"
                      data-after-message="<?= t('content downloaded') ?>">
                    <a href="#" data-action="download-item"><?= t('download content') ?></a>
                </span>
            </li>
            <?php if ($group_id): ?>
            <li>
                <a href="?action=unread&amp;group_id=<?= $group_id ?>"><?= t('Back to the group') ?></a>
            </li>
            <?php endif; ?>
        </ul>

        <div id="item-content" <?= Helper\is_rtl($item + array('rtl' => $feed['rtl']))  ? 'dir="rtl"' : 'dir="ltr"' ?>>

            <?php if ($item['enclosure']): ?>
                <?php if (strpos($item['enclosure_type'], 'audio') !== false): ?>
                <div id="item-content-enclosure">
                    <audio controls>
                        <source src="<?= $item['enclosure'] ?>" type="<?= $item['enclosure_type'] ?>">
                    </audio>
                </div>
                <?php elseif (strpos($item['enclosure_type'], 'video') !== false): ?>
                <div id="item-content-enclosure">
                    <video controls>
                        <source src="<?= $item['enclosure'] ?>" type="<?= $item['enclosure_type'] ?>">
                    </video>
                </div>
                <?php elseif (strpos($item['enclosure_type'], 'image') !== false && $item['content'] === ''): ?>
                <div id="item-content-enclosure">
                    <img src="<?= $item['enclosure'] ?>" alt="enclosure"/>
                </div>
                <?php endif ?>
            <?php endif ?>

            <?= $item['content'] ?>
        </div>

        <?php if (isset($item_nav)): ?>
        <nav class="bottom">
            <span class="nav-left">
                <?php if ($item_nav['previous']): ?>
                    <a href="?action=show&amp;menu=<?= $menu ?><?= $group_id ? '&amp;group_id='.$group_id : '' ?>&amp;id=<?= $item_nav['previous']['id'] ?>" id="previous-item" title="<?= Helper\escape($item_nav['previous']['title']) ?>"><?= t('Previous') ?></a>
                <?php else: ?>
                    <?= t('Previous') ?>
                <?php endif ?>
            </span>

            <span class="nav-right">
                <?php if ($item_nav['next']): ?>
                    <a href="?action=show&amp;menu=<?= $menu ?><?= $group_id ? '&amp;group_id='.$group_id : '' ?>&amp;id=<?= $item_nav['next']['id'] ?>" id="next-item" title="<?= Helper\escape($item_nav['next']['title']) ?>"><?= t('Next') ?></a>
                <?php else: ?>
                    <?= t('Next') ?>
                <?php endif ?>
            </span>
        </nav>
        <?php endif ?>
    </article>

<?php endif ?>

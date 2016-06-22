<article
    id="item-<?= $item['id'] ?>"
    class="feed-<?= $item['feed_id'] ?>"
    data-item-id="<?= $item['id'] ?>"
    data-item-status="<?= $item['status'] ?>"
    data-item-bookmark="<?= $item['bookmark'] ?>"
    <?= $hide ? 'data-hide="true"' : '' ?>
    >
    <h2 <?= Helper\is_rtl($item) ? 'dir="rtl"' : 'dir="ltr"' ?>>
        <span class="bookmark-icon"></span>
        <span class="read-icon"></span>
        <?= Helper\favicon($favicons, $item['feed_id']) ?>
        <?php if ($display_mode === 'full'): ?>
            <a class="original" rel="noreferrer" target="_blank"
               href="<?= $item['url'] ?>"
               <?= ($original_marks_read) ? 'data-action="mark-read"' : '' ?>
               title="<?= Helper\escape($item['title']) ?>"
            ><?= Helper\escape($item['title']) ?></a>
        <?php else: ?>
            <a
                href="?action=show&amp;menu=<?= $menu ?><?= isset($group_id) ? '&amp;group_id='.$group_id : '' ?>&amp;id=<?= $item['id'] ?>"
                class="show"
                title="<?= Helper\escape($item['title']) ?>"
            ><?= Helper\escape($item['title']) ?></a>
        <?php endif ?>
    </h2>
    <ul class="item-menu">
         <?php if ($menu !== 'feed-items'): ?>
        <li>
            <?php if (! isset($item['feed_title'])): ?>
                <?= Helper\get_host_from_url($item['url']) ?>
            <?php else: ?>
                <a href="?action=feed-items&amp;feed_id=<?= $item['feed_id'] ?>" title="<?= t('Show only this subscription') ?>"><?= Helper\escape($item['feed_title']) ?></a>
            <?php endif ?>
        </li>
        <?php endif ?>
        <?php if (!empty($item['author'])): ?>
            <li>
                <?= Helper\escape($item['author']) ?>
            </li>
        <?php endif ?>
        <li class="hide-mobile">
            <span title="<?= dt('%e %B %Y %k:%M', $item['updated']) ?>"><?= Helper\relative_time($item['updated']) ?></span>
        </li>
        <?php if ($display_mode === 'full'): ?>
            <li>
                <a
                    href="?action=show&amp;menu=<?= $menu ?><?= isset($group_id) ? '&amp;group_id='.$group_id : '' ?>&amp;id=<?= $item['id'] ?>"
                    class="show"
                ><?= t('view') ?></a>
            </li>
        <?php else: ?>
            <li class="hide-mobile">
                <a href="<?= $item['url'] ?>" class="original" rel="noreferrer" target="_blank" <?= ($original_marks_read) ? 'data-action="mark-read"' : '' ?>><?= t('original link') ?></a>
            </li>
        <?php endif ?>
        <?php if ($item['enclosure']): ?>
            <li>
            <?php if (strpos($item['enclosure_type'], 'video/') === 0): ?>
                <a href="<?= $item['enclosure'] ?>" class="video-enclosure" rel="noreferrer" target="_blank"><?= t('attachment') ?></a>
            <?php elseif(strpos($item['enclosure_type'], 'audio/') === 0): ?>
                <a href="<?= $item['enclosure'] ?>" class="audio-enclosure" rel="noreferrer" target="_blank"><?= t('attachment') ?></a>
            <?php elseif(strpos($item['enclosure_type'], 'image/') === 0): ?>
                <a href="<?= $item['enclosure'] ?>" class="image-enclosure" rel="noreferrer" target="_blank"><?= t('attachment') ?></a>
            <?php else: ?>
                <a href="<?= $item['enclosure'] ?>" class="enclosure" rel="noreferrer" target="_blank"><?= t('attachment') ?></a>
            <?php endif ?>
            </li>
        <?php endif ?>
        <?= \Template\load('bookmark_links', array('item' => $item, 'menu' => $menu, 'offset' => $offset, 'source' => '')) ?>
        <?= \Template\load('status_links', array('item' => $item, 'redirect' => $menu, 'offset' => $offset)) ?>
    </ul>
    <?php if ($display_mode === 'full'): ?>
        <div class="preview-full-content" <?= Helper\is_rtl($item) ? 'dir="rtl"' : 'dir="ltr"' ?>><?= $item['content'] ?></div>
    <?php else: ?>
        <p class="preview" <?= Helper\is_rtl($item) ? 'dir="rtl"' : 'dir="ltr"' ?>><?= Helper\escape(Helper\summary(strip_tags($item['content']), 50, 300)) ?></p>
    <?php endif ?>
</article>

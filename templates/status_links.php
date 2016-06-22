<li class="hide-mobile">
    <a
        href="?action=mark-item-removed&amp;id=<?= $item['id'] ?>&amp;offset=<?= $offset ?>&amp;redirect=<?= $redirect ?>&amp;feed_id=<?= $item['feed_id'] ?>"
        data-action="mark-removed"
        class="delete"
    ><?= t('remove') ?></a>
</li>
<li>
<?php if ($item['status'] == 'unread'): ?>
    <a
        class="mark"
        href="?action=mark-item-read&amp;id=<?= $item['id'] ?>&amp;offset=<?= $offset ?>&amp;redirect=<?= $redirect ?>&amp;feed_id=<?= $item['feed_id'] ?>"
        data-action="mark-read"
        data-reverse-label="<?= t('mark as unread') ?>"
    ><?= t('mark as read') ?></a>
<?php else: ?>
    <a
        class="mark"
        href="?action=mark-item-unread&amp;id=<?= $item['id'] ?>&amp;offset=<?= $offset ?>&amp;redirect=<?= $redirect ?>&amp;feed_id=<?= $item['feed_id'] ?>"
        data-action="mark-unread"
        data-reverse-label="<?= t('mark as read') ?>"
    ><?= t('mark as unread') ?></a>
<?php endif ?>
</li>
<div class="section">
    <h2><?php p($l->t('RSS feed')); ?></h2>
    <p><?php p($l->t('Use this URL in your feed reader. Treat it like a password — anyone with the link can read your feed.')); ?></p>
    <input type="text" readonly value="<?php p($feedUrl); ?>" style="width: 100%; font-family: monospace;" onclick="this.select()">
</div>
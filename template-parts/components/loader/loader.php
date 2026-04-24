<?php
$light_mode = get_field('loader_light_mode', 'option');
$spinner    = get_field('loader_spinner_enabled', 'option');
?>

<div class="loader active <?= $light_mode ? 'light' : 'dark'; ?>" id="ajax-loader">
    <div class="emerald-wrap">

        <div class="emerald-glow"></div>
        <?php if ($spinner):?>
            <div class="loader__spinner"></div>
        <?php endif;?>
        <div class="emerald">
            <div class="emerald__core"></div>
            <div class="emerald__particles"></div>
        </div>

    </div>
</div>
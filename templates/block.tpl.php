<div id="<?php print $block_html_id; ?>" class="<?php print $classes; ?>">
  <?php if ($block->subject): ?>
  <div class="head"><?php print $block->subject ?></div>
  <?php endif;?>  
  <?php print $content ?>  
</div>

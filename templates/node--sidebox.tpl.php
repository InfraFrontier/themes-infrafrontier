<div id="node-<?php print $node->nid; ?>" class="<?php print $classes; ?> autoheight">
	
    <div class="head"><?php print $title; ?></div>
    <div class="boxcontent">
		<?php print render($content); ?>
    	<?php if (user_access('administer nodes')): ?>
            <p class="editlink"><a class="editlink" href="/node/<?php print $node->nid; ?>/edit" title="Edit">edit</a></p>
        <?php endif; ?>
    </div>

</div>
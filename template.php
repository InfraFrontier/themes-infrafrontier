<?php

// preprocess_html
function infrafrontier_preprocess_html(&$vars) {
	
	// look, if page is has a menu item, and if so, what level
	$tree = menu_tree_page_data('main-menu');
	$level = 0;
	while ($tree) {
		while ($item = array_shift($tree)) {
			if ($item['link']['in_active_trail']) {	$level++; if (!empty($item['below'])) { $tree = $item['below']; break; } break 2; }
		}
	}
	if ($level > 0) { $vars['classes_array'][] = 'level-'.$level; }
}

// preprocess_page
function infrafrontier_preprocess_page(&$vars) {
	
	// get node in page.tpl.php
	if (($node = menu_get_object()) && $node->type == 'page') {
		$view = node_view($node);
		$vars['field_headimage'] = drupal_render($view['field_headimage']);
	}
		
}

// clean head
function infrafrontier_html_head_alter(&$head_elements) {	
	unset($head_elements['system_meta_generator']);
	unset($head_elements['system_meta_content_type']);
}

// remove unnecessary css files
function infrafrontier_css_alter(&$css) {
		
    $css_to_remove = array(); 
	
	// Core module css
    $css_to_remove[] = 'modules/system/system.base.css'; 
    $css_to_remove[] = 'modules/system/system.menus.css';
	$css_to_remove[] = 'modules/system/system.messages.css'; 
	$css_to_remove[] = 'modules/system/system.theme.css';
	$css_to_remove[] = 'modules/user/user.css';
	$css_to_remove[] = 'modules/node/node.css';
	$css_to_remove[] = 'modules/field/theme/field.css';
	
	// Views
	$css_to_remove[] = 'sites/all/modules/views/css/views.css';
		
	// Chaos Tools
	$css_to_remove[] = 'sites/all/modules/ctools/css/ctools.css';
		
	// Date
	$css_to_remove[] = 'sites/all/modules/date/date_api/date.css';
	
	// Field collection
	$css_to_remove[] = 'sites/all/modules/field_collection/field_collection.theme.css';
	 
    foreach ($css_to_remove as $index => $css_file) { unset($css[$css_file]); }
	
}


function infrafrontier_breadcrumb($variables) {
	$breadcrumb = $variables['breadcrumb'];
	if (!empty($breadcrumb)) {	
		$crumbs = '<div class="breadcrumb">';	
		foreach($breadcrumb as $value) { $crumbs .= $value.' / '; }
		$crumbs = substr($crumbs,0,-3);
		$crumbs .= '</div>';
		return $crumbs;
	}
}
<!DOCTYPE HTML>
<!--[if lt IE 7 ]> <html lang="<?php print $language->language; ?>" class="no-js ie6"> <![endif]-->
<!--[if IE 7 ]>    <html lang="<?php print $language->language; ?>" class="no-js ie7"> <![endif]-->
<!--[if IE 8 ]>    <html lang="<?php print $language->language; ?>" class="no-js ie8"> <![endif]-->
<!--[if IE 9 ]>    <html lang="<?php print $language->language; ?>" class="no-js ie9"> <![endif]-->
<!--[if (gt IE 9)|!(IE)]><!--><html lang="<?php print $language->language; ?>" class="no-js"><!--<![endif]-->
<head>

<!-- Infrafrontier -->
<title><?php print $head_title; ?></title>
<meta charset="utf-8">
<meta name="author" content="Infrafrontier" />
<?php print $head; ?>

<!-- css -->
<link href="//fonts.googleapis.com/css?family=Open+Sans:400,600" rel="stylesheet" type="text/css" />
<?php print $styles; ?>

<!-- js -->
<?php print $scripts; ?>

</head>

<body class="<?php print $classes; ?>">	
<?php print $page_top; ?>
<?php print $page; ?>
<?php print $page_bottom; ?>
</body>
</html>
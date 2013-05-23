<?
session_start();

if (isset($_GET['mystyle'])) {
 $_SESSION['mystyle'] = $_GET['mystyle'];
}

if(!isset($_GET['style']) && isset($_SESSION['mystyle'])){
	$pstyle = $_SESSION['mystyle'];
}elseif(isset($_GET['style'])){
	$pstyle = $_GET['style'];
}else{
	$pstyle = "styles";
}


?>

<!-- ------------------------------------------------------------ -->
<!-- NOTE:  if changes need to be done in the file, restrain them -->
<!--    to the area between the START and END of the content only -->
<!-- ------------------------------------------------------------ -->

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
"http://www.w3.org/TR/html4/loose.dtd">
<html>

<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<title>EMMA - the European Mouse Mutant Archive</title>
<link rel="stylesheet" type="text/css" href="<?php echo $pstyle; ?>.css" media="screen" title="Standard">
<link rel="stylesheet" href="print.css" type="text/css" media="print">
<!-- external alternate style sheets - "theme specific"-->
<link rel="alternate stylesheet" type="text/css" title="Druckvorschau" href="print.css" media="screen">
<link rel="shortcut icon" href="images/favicon.ico" >
</head>

<body>
<?php include("nav/top_menue.php"); ?>
<div id="container-header">
<div class="row-orange1"></div>
<div id="pic-start"></div>
<div class="row-orange2"></div>
</div><!-- end container header -->
<div id="container-nav-main">
<?php include("nav/main_menue.php"); ?>

<div id="content">            <!-- START of the content -->

<h1>The European Mouse Mutant Archive &#45; EMMA</h1>
<p>&nbsp;</p>
<p>is a non-profit repository for the collection, archiving (via cryopreservation) and distribution of relevant mutant 
      strains essential for basic biomedical research.The laboratory mouse is the most important mammalian 
      model for studying genetic and multi-factorial diseases in man. Thus the work of EMMA will play a crucial role in 
      exploiting the tremendous potential benefits to human health presented by the current research in mammalian genetics.</p>

<table
 style='margin-top: 10px 0 16px 0; border-collapse:collapse;text-align: center'>
 <tr>
  <td style='border:none'>
  <p style='font-size:8.0pt; font-family:Verdana;color:#333333'>
      EMMA is supported by the partner institutions, national research programmes and by the <a href="http://cordis.europa.eu/fp7/capacities/home_en.html" target="WIN_VIEW" title="to the ECs FP7 Capacities Specific Programme - a new window opens">EC's FP7 Capacities Specific Programme</a>.</p>
  </td>
  <td style='border:none'>
  <p style='font-size:8.0pt;font-family:Verdana; color:#333333'><img src="/images/logos/logo_ec_fp7.jpg"></p>
  </td>
 </tr>  
</table>


  <table border=0 cellspacing=0 cellpadding=0 width=auto style='margin-top: 10px 0 16px 0;border-collapse:collapse;text-align:center; margin:0 auto;'>

 <tr>
  <td style='border:none'>
  <p  align="center" class=MsoNormal><span class=SpellE><span style='font-size:8.0pt;font-family:Verdana;color:#333333'>
     <h1 align="center"> <a href="submissions.php" target="_blank" title="Submission of mice - a new window opens">Deposit mice</a></h1>  
  <p  align="center" class=MsoNormal><span class=SpellE><span style='font-size:8.0pt; font-family:Verdana;color:#333333'>
     <h1 align="center"><a href="submissions.php" target="_blank" title="Submissions of mice - a new window opens"><img src="/images/image_deposit_mice.jpg" style='padding:0 50px'></a></h1><br>
     </p>
  </td>
  <td style='border:none'>
  <p  align="center" class=MsoNormal><span class=SpellE><span style='font-size:8.0pt; font-family:Verdana;color:#333333;'>
     <h1 align="center"><a href="mutant_types.php" target="_blank" title="Requests for mice - a new window opens">Order mice</a></h1>  
  <p  align="center" class=MsoNormal><span class=SpellE><span style='font-size:8.0pt; font-family:Verdana;color:#333333;'>
     <h1 align="center"><a href="mutant_types.php" target="_blank" title="Requests for mice - a new window opens"><img src="/images/image_order_mice.jpg" style='padding:0 50px'></a></h1><br>
     </p>
  </td>
 </tr>  

</table>
<p>&nbsp;</p>


</div>                        <!-- END of the content -->

</div><!-- end container for nav and content -->
<?php include("nav/footer.php"); 
if (file_exists("statistics.php")) include("statistics.php");
include("http://emmanet.org/global_js.php");
?>
</body>
</html>

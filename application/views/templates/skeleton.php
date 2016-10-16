<?php
/**
 * skeleton.php
 *
 * @package mailapp
 * @author johny
 */

defined('BASEPATH') OR exit('No direct script access allowed');
?><!DOCTYPE html>
<!--[if lt IE 7]>      <html class="no-js lt-ie9 lt-ie8 lt-ie7"> <![endif]-->
<!--[if IE 7]>         <html class="no-js lt-ie9 lt-ie8"> <![endif]-->
<!--[if IE 8]>         <html class="no-js lt-ie9"> <![endif]-->
<!--[if gt IE 8]><!--> <html class="no-js"> <!--<![endif]-->
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
  <title><?php echo !empty($page_title) ? $page_title : ""; ?></title>
  <meta name="description" content="">
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <link rel="stylesheet" href="<?php echo base_url(RESOURCELIB . "bootstrap/css/bootstrap.min.css"); ?>">

  <?php if(!empty($css)):?>
    <?php foreach($css as $css_file): ?>
      <link rel="stylesheet" href="<?php echo base_url($css_file); ?>">
    <?php endforeach; ?>
  <?php endif; ?>

  <style>
    body {
      padding-top: 50px;
      padding-bottom: 20px;
    }
  </style>
  <link rel="stylesheet" href="<?php echo base_url(CSS . "main.css"); ?>">

</head>
<body>
<!--[if lt IE 7]>
<p class="browsehappy">You are using an <strong>outdated</strong> browser. Please <a href="http://browsehappy.com/">upgrade your browser</a> to improve your experience.</p>
<![endif]-->

<?php if(!empty($nav_top)) { echo $nav_top; } ?>

<?php if(!empty($breadcrumbs_component)) { echo $breadcrumbs_component; } ?>

<?php if(!empty($notifications)) { echo $notifications; } ?>

<?php echo $content_body; ?>

<?php echo $footer; ?>

<script src="//ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
<script>window.jQuery || document.write('<script src="<?php echo base_url(RESOURCELIB . "jquery/jquery.min.js"); ?>"><\/script>')</script>

<script src="<?php echo base_url(RESOURCELIB . "bootstrap/js/bootstrap.min.js"); ?>"></script>
<?php if(!empty($javascript)):?>
  <?php foreach($javascript as $javascript_file): ?>
  <script src="<?php echo base_url($javascript_file); ?>"></script>

  <?php endforeach; ?>
<?php endif; ?>

<!--<script src="<?php /*echo base_url(JS . "main.js"); */?>"></script>-->

<!-- Google Analytics: change UA-XXXXX-X to be your site's ID. -->
<script>
  (function(b,o,i,l,e,r){b.GoogleAnalyticsObject=l;b[l]||(b[l]=
    function(){(b[l].q=b[l].q||[]).push(arguments)});b[l].l=+new Date;
    e=o.createElement(i);r=o.getElementsByTagName(i)[0];
    e.src='//www.google-analytics.com/analytics.js';
    r.parentNode.insertBefore(e,r)}(window,document,'script','ga'));
  ga('create','UA-XXXXX-X');ga('send','pageview');
</script>
</body>
</html>

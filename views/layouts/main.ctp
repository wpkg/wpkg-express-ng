<?php
/*
 * wpkgExpress : A web-based frontend to wpkg
 * Copyright 2009 Brian White
 *
 * This file is part of wpkgExpress.
 *
 * wpkgExpress is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * wpkgExpress is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with wpkgExpress.  If not, see <http://www.gnu.org/licenses/>.
 *
 */
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en-AU">

<head>
  <meta http-equiv="content-type" content="application/xhtml+xml; charset=UTF-8" />
  <meta name="author" content="Brian White" />
  <meta name="description" content="Web-based graphical frontend to the open source project wpkg software deployment management system" />
  <meta name="keywords" content="wpkg, wpkg express, wpkgexpress, wpkg frontend, open source" />

  <title>wpkgExpress-ng :: <?php echo $title_for_layout; ?></title>

  <?php echo $html->css('plain'); ?>
  <!--[if IE 6]>
	<?php echo $html->script('DD_belatedPNG_007a-min.js'); ?>
    <?php echo $html->scriptBlock('DD_belatedPNG.fix("img");', array('safe' => false)); ?>
  <![endif]-->
  <?php echo $scripts_for_layout; ?>
</head>

<body>

<!-- Main site container starts -->
<div id="siteBox">

  <!-- Header box starts: page title and top link bar -->
  <div id="headerBox">
    <div id="headerLeft">
      <?php echo $html->link('wpkgExpress-ng', '/', array('title'=>'Return to main summary page')); ?>
      <div class="small">A web-based frontend to <?php echo $html->link('WPKG', 'http://www.wpkg.org', array('title'=>'Official home for the WPKG software deployment management system')); ?></div>
    </div>
    <div id="headerRight">
	  <div style="float: right">
      <?php echo $html->link('Packages', '/packages', array('title'=>'Manage packages and associated information', 'class'=>'packages ' . $navigate->highlight("^/packages.*$"))); ?> <span class="noDisplay"> | </span>
      <?php echo $html->link('Profiles', '/profiles', array('title'=>'Manage profiles', 'class'=>'profiles ' . $navigate->highlight("^/profiles.*$"))); ?> <span class="noDisplay"> | </span>
      <?php echo $html->link('Hosts', '/hosts', array('title'=>'Manage hosts', 'class'=>'hosts ' . $navigate->highlight("^/hosts.*$"))); ?> <span class="noDisplay"> | </span>
      <?php echo $html->link('Admin', '/admin', array('title'=>'Perform other administrative tasks', 'class'=>'admin ' . $navigate->highlight("^/admin.*$"))); ?>
	  <?php echo $html->link('Logout', '/logout', array('title'=>'Log out of the current session', 'class'=>'logout')); ?>
	  </div>
	  <div style="float: right">
		<?php echo $form->create('Search', array('url' => '/search', 'style' => 'float: right; margin-right: 10px')); ?>
		<?php echo $form->input('type', array('label' => false, 'div' => false, 'class' => 'input', 'options' => array('all' => 'All', 'packages' => 'Packages', 'profiles' => 'Profiles', 'hosts' => 'Hosts'), 'selected' => $curType)); ?>
		<?php echo $form->input('query', array('label' => false, 'div' => false, 'class'=>'input', 'size' => '15')) ?>
		<?php echo $form->end(array('label' => 'Search', 'div' => false)); ?>
	  </div>
    </div>
  </div>

  <!-- Content starts: -->

  <div class="bar">&nbsp;</div>
  <div id="content">
    <?php if ($session->check('Message.flash')) echo $session->flash(); ?>
    <?php echo $content_for_layout; ?>
  </div>

  <!-- Footer starts: -->
  <div id="footerBox">

    <div id="footerLeft"></div>

    <div id="footerRight">
		Copyright &copy; 2013 <?php echo $html->link('Probesys', 'http://www.probesys.com', array('title'=>'Probesys - Open Source specialist')); ?> - Based on Brian White's wpkgExpress | UI based on a design by fullahead.org
    </div>

  </div>

</div>

</body>
</html>

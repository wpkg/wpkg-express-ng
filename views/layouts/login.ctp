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
  <meta name="keywords" content="wpkg, wpkg express, wpgkexpress, wpkg frontend, open source" />

  <title>wpkgExpress :: Login</title>

  <?php echo $html->css('plain'); ?>
  <?php echo $html->css('installer'); ?>
  <?php echo $scripts_for_layout; ?>
</head>

<body>

<!-- Main site container starts -->
<div id="siteBox">

  <div id="content">
    <?php if ($session->check('Message.flash')) { $session->flash(); echo "<br />"; } ?>
    <?php echo $content_for_layout; ?>
  </div>

  <?php echo $cakeDebug; ?>

</div>

</body>
</html>
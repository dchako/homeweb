<?php defined('MW_INSTALLER_PATH') || exit('No direct script access allowed');

/**
 * This file is part of the MailWizz EMA application.
 * 
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com> 
 * @link http://www.mailwizz.com/
 * @copyright 2013-2016 MailWizz EMA (http://www.mailwizz.com)
 * @license http://www.mailwizz.com/license/
 * @since 1.0
 */
 
?>

<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <title>MailWizz Installer</title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        
        <link rel="stylesheet" type="text/css" href="../assets/css/bootstrap.min.css" />
        <link rel="stylesheet" type="text/css" href="../assets/css/font-awesome.min.css" />
        <link rel="stylesheet" type="text/css" href="../assets/css/ionicons.min.css" />
        <link rel="stylesheet" type="text/css" href="../assets/css/adminlte.css" />
        <link rel="stylesheet" type="text/css" href="../assets/css/skin-blue.css" />
        <link rel="stylesheet" type="text/css" href="../assets/css/common.css" />
        
        <script type="text/javascript" src="http://code.jquery.com/jquery-1.8.3.min.js"></script>
        <script type="text/javascript" src="../assets/js/bootstrap.min.js"></script>
        <script type="text/javascript" src="../assets/js/notify.js"></script>
        <script type="text/javascript" src="../assets/js/adminlte.js"></script>
        <script type="text/javascript" src="../customer/assets/js/app.js"></script>
    </head>
  
    <body class="skin-blue">
        <header class="header">
            <a href="index.php?route=welcome" class="logo icon">
                MailWizz
            </a>
            <nav class="navbar navbar-static-top" role="navigation">
                <div class="navbar-right"></div>
            </nav>
        </header>
        <div class="wrapper row-offcanvas row-offcanvas-left">
            <aside class="left-side sidebar-offcanvas">
                <section class="sidebar">
                    <ul class="sidebar-menu">
                        <li class="<?php echo ($context instanceof WelcomeController) ? 'active':'';?>"><a href="javascript:;"><i class="glyphicon glyphicon-circle-arrow-right"></i> Welcome</a></li>
                        <li class="<?php echo ($context instanceof RequirementsController) ? 'active':'';?>"><a href="javascript:;"><i class="glyphicon glyphicon-circle-arrow-right"></i> Requirements</a></li>
                        <li class="<?php echo ($context instanceof FilesystemController) ? 'active':'';?>"><a href="javascript:;"><i class="glyphicon glyphicon-circle-arrow-right"></i> File system checks</a></li>
                        <li class="<?php echo ($context instanceof DatabaseController) ? 'active':'';?>"><a href="javascript:;"><i class="glyphicon glyphicon-circle-arrow-right"></i> Database import</a></li>
                        <li class="<?php echo ($context instanceof AdminController) ? 'active':'';?>"><a href="javascript:;"><i class="glyphicon glyphicon-circle-arrow-right"></i> Admin account</a></li>
                        <li class="<?php echo ($context instanceof CronController) ? 'active':'';?>"><a href="javascript:;"><i class="glyphicon glyphicon-circle-arrow-right"></i> Cron jobs</a></li>
                        <li class="<?php echo ($context instanceof FinishController) ? 'active':'';?>"><a href="javascript:;"><i class="glyphicon glyphicon-circle-arrow-right"></i> Finish</a></li>
                    </ul>
                </section>
            </aside>
            <aside class="right-side">
                <section class="content-header">
                    <h1><?php echo !empty($pageHeading) ? $pageHeading : '&nbsp;';?></h1>
                    <?php if (!empty($breadcrumbs)) { $bcount = count($breadcrumbs);?>
                    <ul class="breadcrumb">
                        <li><a href="index.php?route=welcome">Install</a><span class="divider"></span></li>
                        <?php $i = 0; foreach ($breadcrumbs as $text => $href) { ++$i; ?>
                        <li><a href="<?php echo $href;?>"><?php echo $text;?></a> <?php if ($i < $bcount) {?> <span class="divider"></span><?php }?></li>
                        <?php } ?>
                    </ul>
                    <?php } ?>
                </section>
                <section class="content">
                    <?php if ($error = $context->getError('general')) { ?>
                    <div class="alert alert-danger alert-block">
                        <?php echo $error;?>
                        <button type="button" class="close" data-dismiss="alert">×</button>
                    </div>
                    <?php } ?>
                    {{CONTENT}}
                </section>
            </aside>
        </div>
    </body>
</html>
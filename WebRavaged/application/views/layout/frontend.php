<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="pl" lang="pl">
    <head>
        <base href="<?php echo URL::base() ?>" />
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
        <title><?php echo $title ?></title>
        <link rel="stylesheet" type="text/css" href="<?php echo URL::base() ?>style.css" />
        <script type="text/javascript" src="<?php echo URL::base() ?>jquery.js"></script>
    </head>
    <body>
        <div id="logo" style="margin-bottom: 10px">
            <h1><?php echo __('Black Ops Remote Console') ?></h1>
        </div><?php if($notice): ?>
        <div class="message"><?php echo $notice ?></div><?php endif; ?>
<?php echo $content ?>
        <div id="footer">
            Created by <a href="mailto:me2.legion@gmail.com">EpicLegion</a><br />
            Some Icons are Copyright &copy; <a href="http://p.yusukekamiyamane.com/">Yusuke Kamiyamane</a>. All rights reserved
        </div>
    </body>
</html>
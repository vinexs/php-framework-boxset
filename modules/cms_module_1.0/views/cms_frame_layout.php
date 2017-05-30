<!DOCTYPE html>
<!--{ Copyright 2017 Vin Wong @ vinexs.com }-->
<!--[if IE 8]>
<html class="lt-ie9"><![endif]-->
<!--[if (IE 9|gt IE 9|!(IE))]><!-->
<html><!--<![endif]-->
<head>
    <meta charset="utf-8"/>
    <title><?php echo $this->lang(isset($TITLE) ? $TITLE : 'cms_title'); ?></title>
    <!--[if lt IE 9]>
    <script type="text/javascript" src="//cdnjs.cloudflare.com/ajax/libs/html5shiv/3.7.2/html5shiv.min.js"></script>
    <![endif]-->
    <link class="respond" type="text/css" rel="stylesheet"
          href="//maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
    <link type="text/css" rel="stylesheet" href="//maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
    <link type="text/css" rel="stylesheet" class="respond" href="<?php echo $URL_RSC; ?>css/common.min.css"/>
    <script type="text/javascript" src="//code.jquery.com/jquery-1.12.4.min.js"></script>
    <script type="text/javascript" src="<?php echo $URL_REPOS; ?>jextender/1.0.8/jExtender.js"></script>
    <link type="image/x-icon" rel="shortcut icon" href="<?php echo $URL_REPOS; ?>favicon.ico"/>
    <link type="image/x-icon" rel="icon" href="<?php echo $URL_REPOS; ?>favicon.ico"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0"/>
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1"/>
    <meta name="format-detection" content="telephone=no"/>
    <meta name="data-url"
          content="root=<?php echo $URL_ROOT; ?>, activity=<?php echo $URL_ACTIVITY; ?>, repos=<?php echo $URL_REPOS; ?>, rsc=<?php echo $URL_RSC; ?>"/>
</head>
<body data-lang="<?php echo $LANGUAGE; ?>">
<nav id="cms-navbar">
    <button class="button pull-right" data-action="logout" title="<?php echo $this->lang('logout') ?>">
        <i class="fa fa-power-off"></i>
    </button>
    <div class="button pull-right dropdown">
        <button class="dropdown-toggle" data-toggle="dropdown" data-action="setting"
                title="<?php echo $this->lang('logout') ?>">
            <i class="fa fa-cog"></i>
        </button>
        <ul class="dropdown-menu" role="menu">
            <li>
                <a role="menuitem" href="#"><i class="fa fa-language"></i> <?php echo $this->lang('language') ?></a>
            </li>
        </ul>
    </div>
    <button class="button pull-right" data-action="search_record">
        <i class="fa fa-search"></i>
    </button>
    <button type="button pull-left" data-action="menu_toogle" title="<?php echo $this->lang('menu') ?>">
        <i class="fa fa-navicon"></i>
    </button>
    <div id="search_bar" class="hide">
        <div class="input-group">
            <span class="input-group-addon"><i class="fa fa-search"></i></span>
            <input class="form-control" type="text" name="key"/>
        </div>
    </div>
</nav>
<div id="side-navbar"></div>
<div id="main-container">
    <?php
    if (isset($CONTAIN_VIEW)) {
        if (!is_array($CONTAIN_VIEW)) {
            $this->load_view($CONTAIN_VIEW, null);
        } else {
            foreach ($CONTAIN_VIEW as $VIEW) {
                $this->load_view($VIEW, null);
            }
        }
    }
    ?>
</div>
<!--[if lt IE 9]>
<script type="text/javascript" src="//cdnjs.cloudflare.com/ajax/libs/respond.js/1.4.2/respond.min.js"></script>
<link id="respond-proxy" rel="respond-proxy" href="<?php echo $URL_REPOS;?>respond/1.4.2/respond-proxy.html"/>
<link id="respond-redirect" rel="respond-redirect"
      href="<?php echo $URL_ROOT;?>/assets/respond/1.4.2/respond.proxy.gif"/>
<script type="text/javascript" src="<?php echo $URL_ROOT;?>/assets/respond/1.4.2/respond.proxy.min.js"></script>
<![endif]-->
<script type="text/javascript" src="//maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
<script type="text/javascript" src="<?php echo $URL_REPOS; ?>bootstrap-plus/1.0.0/bootstrap+.min.js"></script>
<script type="text/javascript" src="<?php echo $URL_RSC; ?>js/common.js"></script>
<script>
    $.lang(<?php echo json_encode(array(
	'confirm' => $this->lang('confirm'),
	'cancel' => $this->lang('cancel'),
	'confirm_to_logout' => $this->lang('confirm_to_logout'),
)); ?>);
    $(function () {
        if (( $('#main-container').height() - $('#cms-navbar').height() ) < $(window).height()) {
            $('#main-container').height($(window).height() - $('#cms-navbar').outerHeight());
        }
        $('button[data-action=logout]').click(function (e) {
            e.preventDefault();
            CMS.logout();
        });
    });
</script>
<!-- Completed: <?php echo number_format(microtime(true) - INIT_TIME_START, 5); ?> -->
</body>
</html>

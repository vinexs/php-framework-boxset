<!DOCTYPE html>
<!--{ Copyright 2017 Vin Wong @ vinexs.com }-->
<!--[if IE 8]>
<html class="lt-ie9"><![endif]-->
<!--[if (IE 9|gt IE 9|!(IE))]><!-->
<html><!--<![endif]-->
<head>
    <meta charset="utf-8"/>
    <title><?php echo $this->lang(isset($TITLE) ? $TITLE : 'website_title'); ?></title>
    <?php if (isset($DESCRIPTION)) {
        echo '<meta name="description" content="' . $DESCRIPTION . '" />';
    } ?>
    <?php if (isset($KEYWORDS)) {
        echo '<meta name="keywords" content="' . $KEYWORDS . '" />';
    } ?>
    <!--[if lt IE 9]>
    <script type="text/javascript" src="//cdnjs.cloudflare.com/ajax/libs/html5shiv/3.7.2/html5shiv.min.js"></script>
    <![endif]-->
    <link class="respond" type="text/css" rel="stylesheet"
          href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
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
<nav class="navbar navbar-static-top navbar-vinexs">
    <div class="container">
        <!-- Top bar -->
        <div class="top-bar">
            <div class="lang-opt">
                <div class="btn-group">
                    <button type="button" class="btn btn-link dropdown-toggle btn-xs" data-toggle="dropdown">
                        <img src="<?php echo $URL_RSC; ?>img/en.png"/> English <span class="caret"></span>
                    </button>
                    <ul class="dropdown-menu">
                        <li><a href="#"><img src="<?php echo $URL_RSC; ?>img/en.png"/> English</li>
                        <li><a href="#"><img src="<?php echo $URL_RSC; ?>img/zt.png"/> 繁體中文</a></li>
                        <li><a href="#"><img src="<?php echo $URL_RSC; ?>img/zs.png"/> 简体中文</a></li>
                        <li><a href="#"><img src="<?php echo $URL_RSC; ?>img/jp.png"/> にほんご</a></li>
                    </ul>
                </div>
            </div>
            <div class="search-box">
                <div class="input-group input-group-sm">
                    <input type="text" class="form-control" placeholder="Search...">
					<span class="input-group-btn">
						<button class="btn btn-default" type="button"><i class="fa fa-search"></i></button>
					</span>
                </div>
            </div>
        </div>
        <!-- Logo -->
        <div class="navbar-header">
            <button class="navbar-toggle collapsed" type="button" data-toggle="collapse" data-target=".navbar-collapse">
                <i class="fa fa-navicon"></i>
            </button>
            <a href="index.html" class="navbar-brand">
                <img src="<?php echo $URL_RSC; ?>img/vinexs_logo.png" alt="Vinexs"/>
            </a>
        </div>
        <!-- Navigation -->
        <div class="navbar-collapse collapse">
            <ul class="nav navbar-nav navbar-right">
                <li class="active">
                    <a href="#">Home</a>
                </li>
                <li class="dropdown">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown">Pages <i
                            class="fa fa-angle-down"></i></a>
                    <ul class="dropdown-menu" role="menu">
                        <li>
                            <a href="#">Item Page 1</a>
                        </li>
                        <li>
                            <a href="#">Item Page 1</a>
                        </li>
                        <li class="dropdown ">
                            <a href="#">Extend <i class="fa fa-angle-right"></i></a>
                            <ul class="dropdown-menu" role="menu">
                                <li>
                                    <a href="#">Extend Item 1</a>
                                </li>
                                <li>
                                    <a href="#">Extend Item 2</a>
                                </li>
                                <li>
                                    <a href="#">Extend Item 3</a>
                                </li>
                            </ul>
                        </li>
                    </ul>
                </li>
                <li>
                    <a href="#"> About </a>
                </li>
                <li>
                    <a href="#"> Contact Us</a>
                </li>
            </ul>
        </div>
    </div>
</nav>
<article id="main-container">
    <?php
    if (!is_array($CONTAIN_VIEW)) {
        $this->load_view($CONTAIN_VIEW);
    } else {
        foreach ($CONTAIN_VIEW as $VIEW) {
            $this->load_view($VIEW);
        }
    }
    ?>
</article>
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
<!-- Completed: <?php echo number_format(microtime(true) - INIT_TIME_START, 5); ?>s -->
</body>
</html>

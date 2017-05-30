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
<body class="login" data-lang="<?php echo $LANGUAGE; ?>">
<div class="login-header">
    <div class="brand">
        <img class="logo"/>
        <span><?php echo $this->lang(isset($TITLE) ? $TITLE : 'cms_title'); ?></span>
        <small><?php echo $this->lang(isset($DESC) ? $DESC : 'cms_desc'); ?></small>
    </div>
    <div class="icon">
        <i class="fa fa-sign-in"></i>
    </div>
</div>
<div class="login-strip-container">
    <div class="login-content">
        <form action="<?php echo $URL_ACTIVITY ?>session/login" method="POST">
            <div class="form-group">
                <input type="text" class="form-control input-lg" name="login_id"
                       placeholder="<?php echo $this->lang('login_id') ?>" value="">
            </div>
            <div class="form-group">
                <input type="password" class="form-control input-lg" name="password"
                       placeholder="<?php echo $this->lang('password') ?>" value="">
            </div>
            <div class="checkbox">
                <label><input type="checkbox" name="keep_login"> <?php echo $this->lang('remember_me') ?></label>
            </div>
            <div class="login-buttons">
                <button type="submit"
                        class="btn btn-primary btn-block btn-lg"><?php echo $this->lang('login') ?></button>
            </div>
        </form>
    </div>
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
	'confirm' => 'confirm',
	'cancel' => 'cancel',
	'confirm_to_logout' => 'confirm_to_logout',
)); ?>);
    $('.login .login-content form').submit(function (e) {
        e.preventDefault();
        $('.login .login-content .form-group').removeClass('has-error');
        var $login_id = $('.login .login-content input[name=login_id]');
        var $password = $('.login .login-content input[name=password]');
        if ($login_id.isEmpty()) {
            $login_id.hasError();
            return;
        }
        if ($password.isEmpty()) {
            $password.hasError();
            return;
        }
        $.ajax({
            url: $(this).attr('action'),
            type: $(this).attr('method'),
            data: {
                login_id: $login_id.val(),
                password: $password.val(),
                keep_login: ( $('.login input[type=checkbox][name=keep_login]:checked').length == 1 )
            },
            dataType: 'json',
            success: function (json) {
                if (json.status != 'OK') {
                    return $.alert('<?php echo $this->lang('login_fail') ?>');
                }
                location.reload();
            }
        });
    });
</script>
<!-- Completed: <?php echo number_format(microtime(true) - INIT_TIME_START, 5); ?> -->
</body>
</html>

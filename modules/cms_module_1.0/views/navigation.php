<!--{ Copyright 2017 Vin Wong @ vinexs.com }-->
<div class="side-header"></div>
<div class="side-block">
    <div class="title"><?php echo $this->lang('profile') ?></div>
    <div id="user-info" class="content">
        <img class="avatar" src="<?php echo $URL_RSC; ?>img/avatar_placeholder.jpg"/>
        <span class="name"><?php echo $login_id ?></span>

        <div class="btn-group">
            <button type="button" class="btn btn-default btn-xs dropdown-toggle" data-toggle="dropdown"><i
                    class="fa fa-cog"></i> <?php echo $this->lang('settings') ?> <span class="caret"></span></button>
            <ul class="dropdown-menu right">
                <li>
                    <a href="<?php echo $URL_ACTIVITY; ?>profile"><i
                            class="fa fa-edit"></i> <?php echo $this->lang('edit_profile') ?></a>
                </li>
                <li class="divider"></li>
                <li>
                    <a href="#" data-action="logout"><i class="fa fa-power-off"></i> <?php echo $this->lang('logout') ?>
                    </a>
                </li>
            </ul>
        </div>
    </div>
</div>
<?php
if (isset($menu)) {
    ?>
    <div id="category" class="side-block">
        <div class="title"><?php echo $this->lang('category') ?></div>
        <div class="content">
            <ul class="item-list">
                <?php
                foreach ($menu as $url => $item) {
                    echo '<li class="item"><a href="' . $URL_ACTIVITY . $url . '" >';
                    if (!empty($item['icon_class'])) {
                        echo '<i class="' . $item['icon_class'] . '"></i> ';
                    }
                    echo '<span>' . $this->lang($item['text']) . '</span>';
                    echo '</a></li>';
                }
                ?>
            </ul>
        </div>
    </div>
    <?php
}
?>

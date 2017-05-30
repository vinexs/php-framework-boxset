<!--{ Copyright 2017 Vin Wong @ vinexs.com }-->
<div id="cate-ctrl" class="container-fluid" data-part="<?php echo $part; ?>">
    <div class="row">
        <div class="cate-child">
            <label class="category-title"><?php echo $this->lang($cate_name); ?>
                <small><?php echo $this->lang($cate_desc); ?></small>
            </label>
        </div>
        <div class="cate-child sub text-right">
            <div class="row-cnt"><span class="total-row">0</span> <?php echo $this->lang('records') ?></div>
            <a class="btn btn-primary" href="<?php echo $URL_ACTIVITY . $part; ?>/add"><i
                    class="fa fa-plus"></i> <?php echo $this->lang('add') ?></a>
        </div>
    </div>
</div>
<div class="table-responsive">
    <table id="data-list-table" class="table table-hover">
        <thead>
        <tr>
            <?php
            foreach ($list_field as $filed => $prop) {
                echo '<th>' . $this->lang($part . '_' . $filed) . '</th>';
            }
            ?>
            <th>&nbsp;</th>
        </tr>
        </thead>
        <tbody>
        <tr class="loading" style="display: none;">
            <td colspan="100"><i class="fa fa-refresh fa-spin"></i> <?php echo $this->lang('loading') ?></td>
        </tr>
        <tr class="no-record" style="display: none;">
            <td colspan="100"><i class="fa fa-times"></i> <?php echo $this->lang('no_record') ?></td>
        </tr>
        </tbody>
        <tfoot style="display: none;">
        <tr data-pri="{PRIMARY}">
            <?php
            foreach ($list_field as $filed => $prop) {
                if (isset($prop['layout'])) {
                    switch ($prop['layout']) {
                        case UI::UPLOAD_IMG:
                            echo '<td><img class="thumbnail" data-src="' . $URL_ACTIVITY . 'file/' . $part . '_' . $filed . '/{PRIMARY}/{' . strtoupper(str_replace('-', '_', $filed)) . '}" /></td>';
                            break;
                        default:
                            echo '<td>{' . strtoupper(str_replace('-', '_', $filed)) . '}</td>';
                    }
                } else {
                    echo '<td>{' . strtoupper(str_replace('-', '_', $filed)) . '}</td>';
                }
            }
            ?>
            <td>
                <a class="btn btn-xs btn-warning" href="<?php echo $URL_ACTIVITY . $part; ?>/edit/{PRIMARY}">
                    <i class="fa fa-pencil-square-o"></i><span
                        class="xs-hidden"> <?php echo $this->lang('edit') ?></span>
                </a>
                <button class="btn btn-xs btn-danger" data-action="remove">
                    <i class="fa fa-trash"></i><span class="xs-hidden"> <?php echo $this->lang('remove') ?></span>
                </button>
            </td>
        </tr>
        </tfoot>
    </table>
</div>
<div class="page-selector"></div>
<script>
    $.lang(<?php echo json_encode(array(
	'confirm_to_delete' => $this->lang('confirm_to_delete'),
	'unable_to_delete' => $this->lang('unable_to_delete'),
));?>);
    $(function () {
        CMS.loadList(1);
    });
</script>

<!--{ Copyright 2017 Vin Wong @ vinexs.com }-->
<div id="cate-ctrl" class="container-fluid" data-part="<?php echo $part; ?>">
    <div class="row">
        <div class="cate-child sub">
            <label
                class="category-title"><?php echo ($mode == 'add') ? $this->lang('add') : $this->lang('edit'); ?><?php echo $this->lang($cate_name); ?></label>
        </div>
        <div class="cate-child sub text-right">
            <a class="btn btn-default" href="<?php echo $URL_ACTIVITY . $part; ?>/list"><i
                    class="fa fa-arrow-left "></i> <?php echo $this->lang('back') ?></a>
        </div>
    </div>
</div>
<div id="form" class="container-fluid">
    <div class="form-horizontal">
        <?php
        foreach ($field as $name => $prop) {
            if ($prop['type'] != FieldType::PRIMARY_KEY) {
                ?>
                <div class="form-group">
                    <label class="col-sm-3 control-label"><?php echo $this->lang($part . '_' . $name) ?></label>

                    <div class="col-sm-9">
                        <?php
                        switch ($prop['layout']) {
                            case UI::TEXT:
                                echo '<input class="form-control" type="text" data-type="text" name="' . $name . '" ' . (!empty($prop['required']) ? ' required ' : '') . ' />';
                                break;
                            case UI::TEXTAREA:
                                echo '<textarea class="form-control" data-type="textarea" name="' . $name . '" ' . (!empty($prop['required']) ? ' required ' : '') . ' ></textarea>';
                                break;
                            case UI::HTML:
                                echo '<input class="form-control" name="' . $name . '" data-type="html" ' . (!empty($prop['required']) ? ' required ' : '') . '/>';
                                break;
                            case UI::NUMBER:
                                echo '<input class="form-control" type="number" data-type="number" name="' . $name . '" ' . (!empty($prop['required']) ? ' required ' : '') . ' ';
                                if (isset($prop['min'])) {
                                    echo 'min="' . $prop['min'] . '"';
                                }
                                if (isset($prop['max'])) {
                                    echo 'max="' . $prop['max'] . '"';
                                }
                                echo ' />';
                                break;
                            case UI::DROPDOWN:
                                echo '<select class="form-control" name="' . $name . '" data-type="dropdown" ' . (!empty($prop['required']) ? ' required ' : '') . ' ' . (!empty($prop['data']) ? ' dynamic-source ' : '') . ' placeholder="' . $this->lang('select_an_option') . '" >';
                                if (!empty($prop['option'])) {
                                    foreach ($prop['option'] as $value => $display) {
                                        echo '<option value="' . $value . '">' . $this->lang($display) . '</option>';
                                    }
                                }
                                echo '</select>';
                                break;
                            case UI::DATE_PICK:
                                echo '
										<div class="input-group date">
											<input class="form-control" type="text" data-type="date" name="' . $name . '" ' . (!empty($prop['required']) ? ' required ' : '') . ' />
											<span class="input-group-addon"><i class="fa fa-calendar "></i></span>
										</div>';
                                break;
                            case UI::DATETIME_PICK:
                                echo '
										<div class="input-group datetime">
											<input class="form-control" type="text" data-type="datetime" name="' . $name . '" ' . (!empty($prop['required']) ? ' required ' : '') . ' />
											<span class="input-group-addon"><i class="fa fa-calendar "></i></span>
										</div>';
                                break;
                            case UI::UPLOAD_IMG:
                                echo '
										<div class="upload-box"/>
											<img class="thumbnail" />
											<input type="hidden" data-type="image-upload" name="' . $name . '" ' . (!empty($prop['required']) ? ' required ' : '') . ' />
											<button class="btn btn-primary" data-mime="image/*" data-name="' . $name . '" data-action="upload"><i class="fa fa-upload"></i><span> ' . $this->lang('upload_image') . '</span></button>
										</div>';
                                break;
                            case UI::UPLOAD_FILE:
                                echo '
										<div class="upload-box"/>
											<span class="filename"></span>
											<input type="hidden" data-type="file-upload" name="' . $name . '" ' . (!empty($prop['required']) ? ' required ' : '') . ' />
											<button class="btn btn-primary" data-mime="*/*" data-name="' . $name . '" data-action="upload"><i class="fa fa-upload"></i><span> ' . $this->lang('upload_file') . '</span></button>
										</div>';
                                break;
                            case UI::JSON:
                                echo '<input class="form-control" type="text" data-type="json" name="' . $name . '" ' . (!empty($prop['required']) ? ' required ' : '') . ' />';
                                break;
                            case UI::MULTILANG:
                                echo '<input class="form-control" type="text" data-type="multi-lang" name="' . $name . '" ' . (!empty($prop['required']) ? ' required ' : '') . ' />';
                                break;
                        }
                        ?>
                    </div>
                </div>
                <?php
            }
        }
        ?>
        <div class="form-group">
            <div class="form-ctrl-btn col-sm-9 col-sm-offset-3">
                <?php
                if ($mode == 'add') {
                    echo '<button class="btn btn-primary" data-action="add"><i class="fa fa-plus"></i> ' . $this->lang('add') . '</button>';
                } else {
                    echo '<button class="btn btn-primary" data-action="edit"><i class="fa fa-save"></i> ' . $this->lang('save') . '</button>';
                    echo '<button class="btn btn-danger" data-action="remove"><i class="fa fa-trash"></i> ' . $this->lang('remove') . '</button>';
                }
                ?>
            </div>
        </div>
    </div>
</div>
<script>
    $(function () {
        CMS.editor.mode = '<?php echo $mode; ?>';
        CMS.editor.primary_key = '<?php echo $primary_key ?>';
        CMS.editor.record = {};
        <?php
        if( $mode == 'edit' and !empty( $record ) )
        {
            echo "CMS.editor.record = ".json_encode( $record ).";\n";
        }
        ?>
        $.getResource([
            // Json 3
            '//cdnjs.cloudflare.com/ajax/libs/json3/3.3.2/json3.min.js',
            // Select 2
            '//cdnjs.cloudflare.com/ajax/libs/select2/4.0.0/js/select2.min.js',
            '//cdnjs.cloudflare.com/ajax/libs/select2/4.0.0/css/select2.min.css',
            // Bootstrap 3 Datetime Picker
            '//cdnjs.cloudflare.com/ajax/libs/moment.js/2.9.0/moment-with-locales.min.js',
            '//cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.0.0/css/bootstrap-datetimepicker.min.css',
            // Summer Note
            '//cdnjs.cloudflare.com/ajax/libs/summernote/0.6.16/summernote-bs3.min.css',
            '//cdnjs.cloudflare.com/ajax/libs/summernote/0.6.16/summernote.min.css',
            '//cdnjs.cloudflare.com/ajax/libs/summernote/0.6.16/summernote.min.js'
        ], function () {
            // bootstrap-datetimepicker require moment.js
            $.getResource(['//cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.0.0/js/bootstrap-datetimepicker.min.js'], function () {
                CMS.initFields();
            });
        });
        $('#cms-navbar button[data-action=search_record]').hide();
        $('#form button[data-action=add], #form button[data-action=edit]').click(function (e) {
            e.preventDefault();
            CMS.saveRecord();
        });
        $('#form button[data-action=remove]').click(function (e) {
            e.preventDefault();
            CMS.removeRecord($('#cate-ctrl').attr('data-part'), CMS.editor.record[CMS.editor.primary_key]);
        });
    });
</script>

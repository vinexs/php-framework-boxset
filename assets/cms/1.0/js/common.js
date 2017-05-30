/*
 * Copyright 2017 Vin Wong @ vinexs.com	(MIT License)
 *
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 * 1. Redistributions of source code must retain the above copyright
 *    notice, this list of conditions and the following disclaimer.
 * 2. Redistributions in binary form must reproduce the above copyright
 *    notice, this list of conditions and the following disclaimer in the
 *    documentation and/or other materials provided with the distribution.
 * 3. All advertising materials mentioning features or use of this software
 *    must display the following acknowledgement:
 *    This product includes software developed by the <organization>.
 * 4. Neither the name of the <organization> nor the
 *    names of its contributors may be used to endorse or promote products
 *    derived from this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY <COPYRIGHT HOLDER> ''AS IS'' AND ANY
 * EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
 * WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 * DISCLAIMED. IN NO EVENT SHALL <COPYRIGHT HOLDER> BE LIABLE FOR ANY
 * DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
 * (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND
 * ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS
 * SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 */

(function(jQuery) {

	jQuery.extend({
		url: (function() {
			var data = {};
			var $meta = $('meta[name=data-url]');
			if ($meta.length > 0) {
				var content = $meta.attr('content').split(',');
				for (var i=0; i < content.length; i++) {
					var variable = content[i].trim().split('=');
					data[variable[0]] = variable[1];
				}
			}
			return data;
		})()
	});

})(jQuery);

CMS = {
	loading: false,
	editor: {
		primary_key: '',
		mode: '',
		record: {}
	},
	getMenu: function() {
		$.ajax({
			url: $.url.activity + 'nav?'+ $.now(),
			success: function(html) {
				$('#side-navbar').html(html);
				$('#side-navbar #user-info [data-action=logout]').click(function(e) {
					e.preventDefault();
					CMS.logout();
				});
			}
		});
	},
	toggleMenu: function() {
		$('body').toggleClass('menu-expend');
	},
	search: {
		delaySearch: null,
		toggle: function() {
			var $searchBar = $('#cms-navbar #search_bar');
			if ($searchBar.hasClass('hide')) {
				$searchBar.removeClass('hide');
			} else {
				$searchBar.addClass('hide');
				$searchBar.find('input').attr('value', '');
				if ($('#data-list-table').length > 0) {
					CMS.loadList(1);
				}
			}
		},
		refresh: function($this) {
			clearTimeout(CMS.search.delaySearch);
			setTimeout(function() {
				if ($('#data-list-table').length == 0) {
					return;
				}
				CMS.loadList(1, $this.val());
			}, 800);
		}
	},
	logout: function() {
		$.confirm('confirm_to_logout', {
			confirm: function(e) {
				$.ajax({
					url: $.url.activity + 'session/logout?'+ $.now(),
					type: 'POST',
					dataType: 'json',
					success: function(json) {
						if (json.status == 'ERROR') {
							$.alert('logout_error');
						}
						location.reload();
					}
				});
			}
		});
	},
	loadList: function(page, keyword) {
		if (CMS.loading) {
			return;
		}
		CMS.loading = true;
		if (!isset(keyword)) {
			keyword = '';
		}
		var part = $('#cate-ctrl').attr('data-part');
		var $table = $('#data-list-table');
		var $holder = $table.find('tbody');
		$holder.find('tr:not(.loading, .no-record)').remove();
		$('tr.loading').show();
		$('tr.no-record').hide();
		$.ajax({
			url: $.url.activity + part + '/get_list?'+ $.now(),
			type: 'POST',
			data: {
				page: page,
				keyword: keyword
			},
			dataType: 'json',
			success: function(json) {
				CMS.loading = false;
				$('tr.loading').hide();
				var $page = $('.page-selector');
				$page.html('');
				if (json.status != 'OK') {
					$('tr.no-record').show();
					if (json.data == null || json.data == 'no_record_found') {
						return ;
					}
					return $.alert(json.data);
				} else {
					$('#cate-ctrl .total-row').text(json.data.total);
					var template = $table.find('tfoot').html();
					var list = json.data.list;
					for (var i =0; i < list.length ; i++) {
						var tr = template.replace('{}','');
						for (var key in list[i]) {
							if (isNaN(list[i][key])) {
								// Support preview of JSON
								try{
									var json = JSON.parse(list[i][key]);
									var html = "";
									for (var f in json) {
										html+= "["+ f +"]: "+ json[f] +"<br/>";
									}
									list[i][key] = html;
								}catch(e) {}
								// Support preview of MULTILANG
								list[i][key] = list[i][key].replace('[en]:','<img src="'+ $.url.rsc +'img/en.png" />')
												.replace('[zt]:','<img src="'+ $.url.rsc +'img/zt.png" />')
												.replace('[zs]:','<img src="'+ $.url.rsc +'img/zs.png" />')
												.replace('[jp]:','<img src="'+ $.url.rsc +'img/jp.png" />');
							}
							tr = tr.replace(new RegExp('{'+ key +'}', 'g'), list[i][key]);
						}
						$holder.append(tr);
					}
					$holder.find('tr[data-pri] button[data-action=edit]').click(function(e) {
						e.preventDefault();
						location.href = $.url.activity + part +'/edit/'+ $(this).attr('data-pri');
					});
					$holder.find('tr[data-pri] button[data-action=remove]').click(function(e) {
						e.preventDefault();
						return CMS.removeRecord(part, $(this).parents('tr[data-pri]'));
					});
					$('img[data-src]').loadDataSrc();
					$page.html(json.data.pager);
					$page.find('li a').click(function(e) {
						e.preventDefault();
						CMS.loadList($(this).attr('data-page'), keyword);
					});
				}
			}
		});
	},
	initFields: function() {
		var part = $('#cate-ctrl').attr('data-part');
		var $inputs = $('#form :input[name]');
		$inputs.each(function(i,o) {
			var $input = $(o);
			var name = $input.attr('name');
			var datatype = $input.attr('data-type');
			if (isset(CMS.editor.record[name])) {
				$input.val(CMS.editor.record[name]);
			} else if (CMS.editor.mode == 'add') {
				CMS.editor.record[name] = '';
			}
			switch(datatype) {
				case 'dropdown':
					if (isset($input.attr('dynamic-source'))) {
						if (!CMS.editor.record[$input.attr('name')].isEmpty()) {
							$.ajax({
								url: $.url.activity + part + '/get_default',
								type: 'POST',
								data: {
									fieldname: $input.attr('name'),
									value: CMS.editor.record[$input.attr('name')]
								},
								dataType: 'json',
								success: function(json) {
									$input.append('<option selected value="'+ json.data.id +'">'+ json.data.text +'</option>');
									CMS.bindSelect2Ajax($input).on('change',function(e) {
										CMS.editor.record[$input.attr('name')] = $input.val();
									});
								}
							});
						} else {
							CMS.bindSelect2Ajax($input).on('change',function(e) {
								CMS.editor.record[$input.attr('name')] = $input.val();
							});
						}
					} else {
						$input.select2().on('change',function(e) {
							CMS.editor.record[$input.attr('name')] = $input.val();
						});
					}
					break;
				case 'date':
				case 'datetime':
					var dateFormat = (datatype=='date')? 'YYYY-MM-DD' : 'YYYY-MM-DD HH:mm:ss';
					$input.parent('.input-group').datetimepicker({
						format: dateFormat,
						sideBySide: true,
						icons: {
							time: 'fa fa-clock-o',
							date: 'fa fa-calendar',
							up: 'fa fa-chevron-up',
							down: 'fa fa-chevron-down',
							previous: 'fa fa-chevron-left',
							next: 'fa fa-chevron-right',
							today: 'fa fa-crosshairs',
							clear: 'fa fa-trash',
						}
					}).on('dp.change', function(e) {
						CMS.editor.record[$input.attr('name')] = $input.val();
					});
					break;
				case 'html':
					$input.summernote({
						height: 250,
						onChange: function(contents, $editable) {
							CMS.editor.record[$input.attr('name')] = $input.code();
						},
						onKeyup: function(e) {
							CMS.editor.record[$input.attr('name')] = $input.code();
						}
					}).code($input.val());
					break;
				case 'image-upload':
				case 'file-upload':
					var $upload_box = $(this).parents('.upload-box');
					$upload_box.find('button[data-action=upload]').click(function(e) {
						var accept = $upload_box.find('[data-mime]').attr('data-mime');
						return CMS.uploadDialog.load(part, name, accept);
					});
					if (datatype == 'image-upload') {
						var url = $.url.activity +'file/'+ part +'_'+ name +'/'+ CMS.editor.record[ CMS.editor.primary_key ] +'/'+ CMS.editor.record[name];
						if (!CMS.editor.record[name].isEmpty()) {
							$upload_box.find('.thumbnail').css('cursor', 'pointer');
						}
						$upload_box.find('.thumbnail').attr('data-src', url).click(function(e) {
							e.preventDefault();
							var imgName = $(this).siblings('[data-type=image-upload]').val();
							if (imgName.isEmpty()) {
								return;
							}
							$.alert('<div class="text-center"><img src="'+ $(this).attr('src') +'" style="max-width: 100%; max-height: 500px;" /></div>');
						});

					}
					$upload_box.find('.filename').text(CMS.editor.record[name]);
					break;
				case 'multi-lang':
					$input.hide();
					$input.bind('change', function(e) {
						e.preventDefault();
						var $chInput = $(this);
						$chInput.siblings('div.form-control').remove();
						var $inputTag = CMS.multilang.obj2Html($chInput.val());
						$inputTag.click(function(e) {
							CMS.multilang.editDialog($chInput);
						});
						$input.after($inputTag);
						CMS.editor.record[$input.attr('name')] = $chInput.val();
					}).trigger('change');
					break;
				default:
					$input.bind('change keyup', function(e) {
						CMS.editor.record[$input.attr('name')] = $input.val();
					});
			}
		});
		$('#form img[data-src]').loadDataSrc();
	},
	bindSelect2Ajax: function($input) {
		var name = $input.attr('name');
		$input.select2({
			ajax: {
				url: $.url.activity + $('#cate-ctrl').attr('data-part') +'/get_options',
				type: 'POST',
				dataType: 'json',
				delay: 250,
				data: function (params) {
					var dataObj = {
						fieldname: name,
						page: params.page
					};
					if (!CMS.editor.record[name].isEmpty()) {
						dataObj['default_value'] = CMS.editor.record[name];
					}
					return dataObj;
				},
				processResults: function (json, page) {
					if (json.status != 'OK') {
						return {results: []};
					}
					var items = [];
					for (var val in json.data.list) {
						items.push({ id: json.data.list[val], text: val  });
					}
					return {
						results: items
					};
				},
				cache: true
			}
		});
		return $input;
	},
	saveRecord: function() {
		var hasError = false;
		$('#form .has-error').removeClass('has-error');
		for (var key in CMS.editor.record) {
			var $input = $('#form :input[name='+key+']');
			if (isset($input.attr('required')) && CMS.editor.record[key].isEmpty()) { $input.hasError(); hasError= true; }
		}
		if (hasError) {
			return;
		}
		var part = $('#cate-ctrl').attr('data-part');
		$.ajax({
			url: $.url.activity + part +'/save?'+ $.now(),
			type: 'POST',
			data:{
				mode: CMS.editor.mode,
				json: JSON.stringify(CMS.editor.record)
			},
			dataType: 'json',
			success: function(json) {
				if (json.status == 'ERROR') {
					if (json.data.error == 'invalid_format' || json.data.error == 'field_required') {
						var $badInput = $('#form :input[name='+ json.data.fieldname +']');
						if ($badInput.length > 0) {
							$badInput.hasError();
						}
						return;
					}
				}
				return $.alert('record_saved_success', {
					close: function(e) {
						location.href = $.url.activity + part +'/list';
					}
				});
			}
		});
	},
	removeRecord: function(part, $row) {
		$.confirm('confirm_to_delete', {
			confirm: function(e) {
				$.ajax({
					url: $.url.activity + part + '/remove?'+ $.now(),
					type: 'POST',
					data: {
						primary_key_val: $row.attr('data-pri')
					},
					dataType: 'json',
					success: function(json) {
						if (json.status != 'OK') {
							return $.alert(json.data.error);
						}
						$row.remove();
					}
				});
			}
		});
	},
	uploadDialog: {
		load: function(part, name, accept) {
			var html = '<form id="upload" class="form-horizontal" action="'+ $.url.activity + part +'/upload" method="POST" enctype="multipart/form-data" target="fileajax">';
				html+= '	<div class="form-group">';
				html+= '		<div class="col-xs-8">';
				html+= '			<label class="hide"><i class="fa fa-circle-o-notch fa-spin" /> '+ $.lang('uploading') +'</label>';
				html+= '			<input type="hidden" name="fieldname" value="'+ name +'" >';
				html+= '			<input type="hidden" name="accept" value="'+ accept +'" >';
				html+= '			<input id="upload_temp_file" type="file" name="file" accept="'+ accept +'">';
				html+= '		</div>';
				html+= '		<div class="col-xs-4">';
				html+= '			<button class="btn btn-primary pull-right upload_temp">Upload</button>';
				html+= '		</div>';
				html+= '		<iframe id="fileajax" name="fileajax" style="display:none;"></iframe>';
				html+= '	</div>';
				html+= '</form>';
			$alert = $.alert(html);
			$alert.find('button.upload_temp').click(function(e) {
				e.preventDefault();
				var $inputfile = $alert.find('#upload_temp_file');
				if ($inputfile.val() == '') {
					return;
				}
				$(this).remove();
				$inputfile.hide();
				$alert.find('label.hide').removeClass('hide');
				$alert.find('button[data-type=close], button.close').hide();
				$alert.find('#upload').submit();
			});
		},
		response: function(json) {
			if (json.status == 'ERROR') {
				$('form#upload label').html($.lang(json.data.error)).addClass('text-danger')
					.parents('#alertbox').find('button[data-type=close], button.close').show();
				return;
			}
			var $uploadBox = $('#form .upload-box :input[name='+ json.data.fieldname +']').parents('.upload-box');
			if (json.data.img_url && $uploadBox.find('.thumbnail').length ==1) {
				$uploadBox.find('.thumbnail').attr('data-src', $.url.activity +'file/'+ json.data.img_url).loadDataSrc();
			}
			if ($uploadBox.find('.filename').length == 1) {
				$uploadBox.find('.filename').text(json.data.filename);
			}
			$uploadBox.find(':input[name]').val(json.data.filename);
			CMS.editor.record[json.data.fieldname] = json.data.filename;
			$('form#upload label').parents('#alertbox').modal('hide');
		}
	},
	multilang: {
		obj2Html: function(json) {
			var $inputTag = $('<div class="form-control multi-lang"></div>');
			try{
				var mLang = JSON.parse(json);
				for (var lang in mLang) {
					$inputTag.append('<div class="each-lang"><img src="'+ $.url.rsc +'img/'+ lang +'.png" /><span>'+ mLang[lang] +'</span></div>');
				}
				return $inputTag;
			}catch (e) {
				return $inputTag;
			}
		},
		editDialog: function($input) {
			var $confirm = $.confirm('<i class="fa fa-circle-o-notch fa-spin"></i> '+ $.lang('loading'), {
				confirm: function(e) {
					e.preventDefault();
					var langObj = {};
					$('#multi-lang-dialog-content textarea[data-lang]').each(function() {
						var $textarea = $(this);
						var val = $textarea.val()
						if (!val.isEmpty()) {
							langObj[ $textarea.attr('data-lang') ] = val;
						}
					});
					$input.val(JSON.stringify(langObj)).trigger('change');
					$confirm.dismiss();
				}
			});
			try{
				var aLang = {'en':0, 'zt':1, 'zs':2, 'jp':3, 'ko': 4, 'de': 5, 'ru': 6};
				var val = $input.val();
				var mLang = (val == "") ? {} :JSON.parse(val);
				var $div = $('<div id="multi-lang-dialog-content" name="'+ $input.attr('name') +'" class="form-horizontal"><div>');
				for (var lang in mLang) {
					$div.append('<div class="form-group"><div class="col-xs-2"><img src="'+ $.url.rsc +'img/'+ lang +'.png" /></div><div class="col-xs-10"><textarea class="form-control" data-lang="'+ lang +'">'+ mLang[lang] +'</textarea></div></div>');
					delete(aLang[lang]);
				}
				var $langSet = $('<div class="form-group add-lang-buttons"><div class="col-xs-10 col-xs-offset-2"></div></div>');
				for (var lang in aLang) {
					$langSet.find('>div').append('<button class="btn btn-default" data-action="add-lang" data-lang="'+ lang +'"><img src="'+ $.url.rsc +'img/'+ lang +'.png" /></button>');
				}
				$div.append($langSet);
				$confirm.setContent($div);
				$confirm.find('button[data-action=add-lang][data-lang]').click(function(e) {
					e.preventDefault();
					var $btn = $(this);
					$('.add-lang-buttons').before('<div class="form-group"><div class="col-xs-2"><img src="'+ $.url.rsc +'img/'+ $btn.attr('data-lang') +'.png" /></div><div class="col-xs-10"><textarea class="form-control" data-lang="'+ $btn.attr('data-lang')  +'"></textarea></div></div>');
					$btn.remove();
				});
			}catch (e) {
				console.error(e);
				$confirm.dismiss();
			}
		}
	}

};


$(function() {
	CMS.getMenu();
	$('#cms-navbar button[data-action=menu_toogle]').click(function(e) {
		CMS.toggleMenu();
	});
	$('#cms-navbar button[data-action=search_record]').click(function(e) {
		CMS.search.toggle();
	});
	$('#cms-navbar #search_bar input').keyup(function(e) {
		CMS.search.refresh($(this));
	});
	$('#cms-navbar button[data-action=logout]').click(function(e) {
		CMS.logout();
	});

});

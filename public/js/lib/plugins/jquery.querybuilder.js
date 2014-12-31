// Advanced search features
(function($) {
	
	$.fn.queryBuilder = function(options) {
		var opts = $.extend({}, $.fn.queryBuilder.defaults, options);
		
		return this.each(function() {
			var $qb = $(this);
			var typeSingular = {'org':'Organisation', 'per':'Person', 'opp':'Opportunity', 'act':'Activity'};
			var qb = Tactile.qb;
			$qb.$filterList = $qb.find('ul');
			$qb.$filterSelect = $qb.find('select.add');
			$qb.$filterSelect.change(function(){
				var fname = $qb.$filterSelect.val();
				var fvalue = ((fname === 'gen_assigned_to' || fname === 'gen_created_by') ? (Tactile.Account.username + '//' + Tactile.Account.site_address) : null);
				$qb.addFilter(fname, null, fvalue);
				$qb.refreshFilterSelect();
				$qb.$filterSelect.find('option[value=""]').attr('selected', 'selected');
			});
			
			// Keeps track of which filters to display in the select box
			$qb.refreshFilterSelect = function() {
				var type = $('#qb_record_type').val();
				$qb.$filterSelect.empty().append('<option value="">-- Add Filter --</option>');
				var $generalGroup = $('<optgroup />').attr('label', 'General Fields');
				var $typeGroup = $('<optgroup />').attr('label', typeSingular[type] + ' Fields');
				var $customGroup = $('<optgroup />').attr('label', typeSingular[type] + ' Custom Fields');
				$.each(Tactile.qb, function(fname, fdata){
					if (!$qb.$filterList.find('#qb_'+fname).length && (fname.match(/^gen_/) || fname.match(new RegExp('^'+type+'_')))) {
						var $opt = $('<option />').val(fname).text(fdata.label);
						if (fname.match(new RegExp('^'+type+'_[0-9]+$'))) {
							$customGroup.append($opt);
						} else if (fname.match(new RegExp('^'+type))) {
							$typeGroup.append($opt);
						} else {
							$generalGroup.append($opt);
						}
						$qb.$filterSelect.append($generalGroup);
						if ($typeGroup.find('option').length) {
							$qb.$filterSelect.append($typeGroup);
						}
						if ($customGroup.find('option').length) {
							$qb.$filterSelect.append($customGroup);
						}
					}
				});
			};
			
			// Add a filter to the list
			$qb.addFilter = function(fname, fop, fvalue) {
				if (fname === '' || Tactile.qb[fname] === undefined) {
					return false;
				}
				var f = Tactile.qb[fname];
				var type = fname.substring(0,3);
				var $li = $('<li class="row '+type+'" />').attr('id', fname).appendTo($qb.$filterList);
				
				// Label
				$li.append($('<label />').attr('for', 'qb_'+fname).text(f.label));
				
				// Operators
				if (f.operators) {
					var $ops = $('<select class="ops" />').attr('name','q['+fname+'][op]').appendTo($li);
					$.each(f.operators, function(i, e){
						$ops.append($('<option />').val(e).text(e));
					});
					$ops.val(fop);
				}
				
				// Value(s)
				var $value = $('<input />').val(fvalue).attr({'id':'qb_'+fname, 'name':'q['+fname+'][value]'});
				switch(f.accept) {
					case 'select':
						$value = $('<select />').attr({'id':'qb_'+fname, 'name':'q['+fname+'][value]'}).appendTo($li);
						$.each(f.options, function(k,v){
							$value.append($('<option />').text(v).val(k));
						});
						$value.val(fvalue);
						break;
					case 'date':
						$value.attr('type','text').addClass('datefield').appendTo($li);
						$value.datepicker();
						break;
					case 'boolean':
						$value = $('<select />').attr({'id':'qb_'+fname, 'name':'q['+fname+'][value]'}).appendTo($li);
						$value.append($('<option />').val('TRUE').text('TRUE')).append($('<option />').val('FALSE').text('FALSE'));
						$value.val(fvalue);
						break;
					case 'numeric':
						$value.attr('type','text').appendTo($li).bind('mouseup, keyup', function(){
							var num = parseFloat($(this).val());
							num = isNaN(num) ? 0 : val;
							$(this).val(num);
						});
						break;
					case 'text':
					default:
						$value.attr('type','text').appendTo($li);
						break;
				}
				$value.addClass('value');
				
				// Filter destructor
				var $del = $('<a class="delete sprite sprite-remove" />').click(function(){
					$qb.removeFilter(fname);
				}).prependTo($li);
			};
			
			// Remove a filter from the list
			$qb.removeFilter = function(fname) {
				$qb.$filterList.find('#qb_'+fname).parent().fadeOut(function(){
					$(this).remove();
					$qb.refreshFilterSelect();
				});
			}
			
			// Handle switching of record type
			$('#qb_record_type').change(function(){
				var newType = $(this).val();
				$qb.$filterList.find('li.org, li.per, li.opp, li.act').not('li.'+newType).remove();
				$qb.refreshFilterSelect();
			});
			
			if (Tactile.q === undefined) {
				// Add a field by default
				$qb.addFilter('gen_name', 'CONTAINS');
			}
			
			// Add options to filter select
			$qb.refreshFilterSelect();
			
			// Attach event handlers to any existing page elements
			$qb.find('li.row a.delete').click(function(){
				$qb.removeFilter($(this).parent().attr('id'));
			});
			$qb.find('div.row a.action').click(function(){
				var $firstFilter = $qb.find('li').not('.record_type').first();
				var searchName = '';
				if ($firstFilter.length) {
					searchName = $firstFilter.find('label').text(); 
					if ($firstFilter.find('select.ops').length) {
						searchName += ' ' + $firstFilter.find('select.ops').val().toLowerCase();
					}
					if ($firstFilter.find('.value').is('select')) {
						searchName += ' ' + $firstFilter.find('.value').find('option:selected').text();
					} else {
						searchName += ' ' + $firstFilter.find('.value').val();
					}
				}
				Om.ModalForm.init('/search/save', 'Save Search', 'Search saved successfully');
				var $fs = Om.ModalForm.$fieldset;
				var q = $qb.parent().serialize();
				var $name = $('<input id="qa_search_name" class="text" type="text" name="name" />').val(searchName);
				$fs.append($('<div class="form_help" />').append($('<p />').text("Saving a search will allow you to store a set of filters for later use. Give the search a name to describe it.")))
					.append($('<input type="hidden" name="form" />').val(q))
					.append(Om.ModalForm.formRow($name));
				Om.ModalForm.postSuccess = function(json){
					// Update foldable?
					$('#saved_searches').find('li.empty').remove();
					var $li = $('<li />').append($('<a href="/search/recall/'+json.id+'" class="sprite sprite-search" />').text(json.name)).append($('<a href="/search/delete/'+json.id+'" class="delete action" />').text('Delete'));
					$('#saved_searches').find('ul').append($li);
				};
				
				Om.ModalForm.show();
			});
		});
	};
	
	$.fn.queryBuilder.defaults = {};
	
}) (jQuery);

$('#queryBuilder').queryBuilder();

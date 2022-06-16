$(document).ready(function(){
	$('input, textarea').placeholder();
	$('input').iCheck();
	
	
	// $(".inputmask").inputmask();
	// $(".mask-phone").inputmask("99 99 99 99 99");
	
		
	isMobile = false; //initiate as false
	// device detection
	if(/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|ipad|iris|kindle|Android|Silk|lge |maemo|midp|mmp|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows (ce|phone)|xda|xiino/i.test(navigator.userAgent) 
    || /1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i.test(navigator.userAgent.substr(0,4))) isMobile = true;
	
	if(isMobile){
		$(document).on('click', '#main-menu nav >ul >li >a', function(e){
			if($(this).parent().find('>ul').length > 0){
				return false;
			}
		});
		
	}
	
	menu_mobile();
	
	$(document).on('click', '.alert-info .close, .alert-popup', function(){
		$('.alert-popup').remove();
	});
		
	var isIE11 = !!navigator.userAgent.match(/Trident.*rv[ :]*11\./)
	if($.browser['msie'] || isIE11){
		$('html').addClass('ie');
	}

	/**** TABLE SORTING ****/
	$('.table-employee thead th').click(function(){
		var table = $(this).parents('table').eq(0)
		var rows = table.find('tr:gt(0)').toArray().sort(comparer($(this).index()))
		this.asc = !this.asc
		$('.table-employee thead th').removeClass('active reverse');
		$(this).addClass('active');
		if (!this.asc){rows = rows.reverse(); $(this).addClass('reverse');}
		for (var i = 0; i < rows.length; i++){table.append(rows[i])}
	})
	function comparer(index) {
		return function(a, b) {
			var valA = getCellValue(a, index), valB = getCellValue(b, index);
			return $.isNumeric(valA) && $.isNumeric(valB) ? valA - valB : valA.toString().localeCompare(valB)
		}
	}
	function getCellValue(row, index){ return $(row).children('td').eq(index).text() }
	
	$('.filter-date .right-button a.bt-pdf').click(function(){
		var dateRange = GetURLParameter('dateStart');
		if($('.table-employee thead th.active').length > 0){
			var field = $('.table-employee thead th.active').attr('data-field');
			var reverse = 0;
			if($('.list-employee .icon-employee').hasClass('inactive')){
				var user = $('.list-employee .icon-employee').not('.inactive').attr('data-user');
			}else{
				var user = "all";
			}
			if($('.table-employee thead th.reverse').length > 0){
				var reverse = 1;
			}
			if(dateRange){
				window.open('http://' + window.location.hostname + $(this).attr('href') +"&fieldName="+field + "&reverse="+reverse + "&user="+user);
			}else{
				window.open('http://' + window.location.hostname + $(this).attr('href') +"?fieldName="+field + "&reverse="+reverse + "&user="+user);
			}
			return false;
		}else{
			if($('.list-employee .icon-employee').hasClass('inactive')){
				var user = $('.list-employee .icon-employee').not('.inactive').attr('data-user');
			}else{
				var user = "all";
			}
			if(dateRange){
				window.open('http://' + window.location.hostname + $(this).attr('href') + "&user="+user);
			}
			else{
				window.open('http://' + window.location.hostname + $(this).attr('href') + "?user="+user);
			}
			return false;
		}
	});
	/**** END TABLE SORTING ****/
	
	
	/**** ADD NEW LINE PACK ****/
	$(document).on('click', '.add-newPack a', function(){
		if($('.input-newPack').hasClass('input-hidden')){
			$('.input-newPack').removeClass('input-hidden');
		}else{
			$('.input-newPack .input-newLine .input-select select').selectmenu('destroy');
			var newLine = $('.input-newPack .input-newLine:first').clone();
			newLine.find('.input-select select').val('');
			newLine.find('#new_quantity').val('');
			newLine.find('#new_price').val('');
			newLine.appendTo('.input-newPack');
			$('.input-newPack .input-newLine .input-select select').selectmenu({
				create: function( event, ui ) {
					$(this).after($('#'+$(this).attr('id')+'-menu').parent());
					$('#'+$(this).attr('id')+'-button').addClass($(this).find('option:selected').attr('class'));
				},
				open: function( event, ui ) {
					var obj = $(this);
					var classArray = [];
					
					$(this).find('option').each(function(){
						if($(this).attr('class')){
							classArray.push($(this).attr('class'));
						}else{
							classArray.push('');
						}
					});
					$('#'+$(this).attr('id')+'-menu li').each(function(e){
						$(this).addClass(classArray[e]);
					});
				},
				change: function( event, ui ) {
					$('#'+$(this).attr('id')+'-button').removeClass('velvet green').addClass($(this).find('option:selected').attr('class'));
					if(ui.item.value != 0 && $(this).attr('id') == 'select-id'){
						document.location.href=ui.item.value;
					}
				}
			});
		}
		return false;
	});
	/**** FIN ADD NEW LINE PACK ****/


	$(document).on('click', '.input-newPack .input-newLine .bt-delete', function(){
		if(confirm('Voulez-vous supprimer cette ligne ?')){
			$(this).parents('.input-newLine').remove();
		}
		return false;
	});
	/**** REMOVE LINE PACK ****/
	
	/**** FIN REMOVE LINE PACK ****/

	/**** ADD NEW LINE Prestation ****/
	$(document).on('click', '.add-newPrestation a', function(){
		if($('.input-newPrestation').hasClass('input-hidden')){
			$('.input-newPrestation').removeClass('input-hidden');
		}else{
			$('.input-newPrestation .input-newLine .input-select select').selectmenu('destroy');
			var newLine = $('.input-newPrestation .input-newLine:first').clone();
			newLine.find('.input-select select').val('');
			newLine.find('#new_quantity').val('');
			newLine.find('#new_price').val('');
			newLine.appendTo('.input-newPrestation');
			$('.input-newPrestation .input-newLine .input-select select').selectmenu({
				create: function( event, ui ) {
					$(this).after($('#'+$(this).attr('id')+'-menu').parent());
					$('#'+$(this).attr('id')+'-button').addClass($(this).find('option:selected').attr('class'));
				},
				open: function( event, ui ) {
					var obj = $(this);
					var classArray = [];
					
					$(this).find('option').each(function(){
						if($(this).attr('class')){
							classArray.push($(this).attr('class'));
						}else{
							classArray.push('');
						}
					});
					$('#'+$(this).attr('id')+'-menu li').each(function(e){
						$(this).addClass(classArray[e]);
					});
				},
				change: function( event, ui ) {
					$('#'+$(this).attr('id')+'-button').removeClass('velvet green').addClass($(this).find('option:selected').attr('class'));
					if(ui.item.value != 0 && $(this).attr('id') == 'select-id'){
						document.location.href=ui.item.value;
					}
				}
			});
		}
		return false;
	});
	/**** FIN ADD NEW LINE Prestation ****/


	$(document).on('click', '.input-newPrestation .input-newLine .bt-delete', function(){
		if(confirm('Voulez-vous supprimer cette ligne ?')){
			$(this).parents('.input-newLine').remove();
		}
		return false;
	});
	/**** REMOVE LINE Prestation ****/

	/**** ADD NEW LINE Service ****/
	$(document).on('click', '.add-newService a', function(){
		if($('.input-newService').hasClass('input-hidden')){
			$('.input-newService').removeClass('input-hidden');
		}else{
			$('.input-newService .input-newLine .input-select select').selectmenu('destroy');
			var newLine = $('.input-newService .input-newLine:first').clone();
			newLine.find('.input-select select').val('');
			newLine.find('#new_quantity').val('');
			newLine.find('#new_price').val('');
			newLine.appendTo('.input-newService');
			$('.input-newService .input-newLine .input-select select').selectmenu({
				create: function( event, ui ) {
					$(this).after($('#'+$(this).attr('id')+'-menu').parent());
					$('#'+$(this).attr('id')+'-button').addClass($(this).find('option:selected').attr('class'));
				},
				open: function( event, ui ) {
					var obj = $(this);
					var classArray = [];
					
					$(this).find('option').each(function(){
						if($(this).attr('class')){
							classArray.push($(this).attr('class'));
						}else{
							classArray.push('');
						}
					});
					$('#'+$(this).attr('id')+'-menu li').each(function(e){
						$(this).addClass(classArray[e]);
					});
				},
				change: function( event, ui ) {
					$('#'+$(this).attr('id')+'-button').removeClass('velvet green').addClass($(this).find('option:selected').attr('class'));
					if(ui.item.value != 0 && $(this).attr('id') == 'select-id'){
						document.location.href=ui.item.value;
					}
				}
			});
		}
		return false;
	});
	/**** FIN ADD NEW LINE Service ****/

	$(document).on('click', '.input-newService .input-newLine .bt-delete', function(){
		if(confirm('Voulez-vous supprimer cette ligne ?')){
			$(this).parents('.input-newLine').remove();
		}
		return false;
	});
	/**** REMOVE LINE Service ****/

	/**** ADD NEW LINE Material ****/
	$(document).on('click', '.add-newMaterial a', function(){
		if($('.input-newMaterial').hasClass('input-hidden')){
			$('.input-newMaterial').removeClass('input-hidden');
		}else{
			$('.input-newMaterial .input-newLine .input-select select').selectmenu('destroy');
			var newLine = $('.input-newMaterial .input-newLine:first').clone();
			newLine.find('.input-select select').val('');
			newLine.find('#new_quantity').val('');
			newLine.find('#new_price').val('');
			newLine.appendTo('.input-newMaterial');
			$('.input-newMaterial .input-newLine .input-select select').selectmenu({
				create: function( event, ui ) {
					$(this).after($('#'+$(this).attr('id')+'-menu').parent());
					$('#'+$(this).attr('id')+'-button').addClass($(this).find('option:selected').attr('class'));
				},
				open: function( event, ui ) {
					var obj = $(this);
					var classArray = [];
					
					$(this).find('option').each(function(){
						if($(this).attr('class')){
							classArray.push($(this).attr('class'));
						}else{
							classArray.push('');
						}
					});
					$('#'+$(this).attr('id')+'-menu li').each(function(e){
						$(this).addClass(classArray[e]);
					});
				},
				change: function( event, ui ) {
					$('#'+$(this).attr('id')+'-button').removeClass('velvet green').addClass($(this).find('option:selected').attr('class'));
					if(ui.item.value != 0 && $(this).attr('id') == 'select-id'){
						document.location.href=ui.item.value;
					}
				}
			});
		}
		return false;
	});
	/**** FIN ADD NEW LINE Material ****/

	$(document).on('click', '.input-newMaterial .input-newLine .bt-delete', function(){
		if(confirm('Voulez-vous supprimer cette ligne ?')){
			$(this).parents('.input-newLine').remove();
		}
		return false;
	});
	/**** REMOVE LINE Material ****/


	/**** ENTRY/LEAVING USER ****/
	$('.filter-date .bt-green').click(function(){
		$('.filter-date .icon-employee').removeClass('inactive');
		$('.table-employee tbody tr').show();
		return false;
	});
	$('.filter-date .icon-employee').click(function(){
		var obj = $(this);
		$('.filter-date .icon-employee').addClass('inactive');
		$(this).removeClass('inactive');
		$('.table-employee tbody tr').hide();
		$('.table-employee tbody tr').filter(function(){
			return $(this).attr('data-user') == obj.attr('data-user')
		}).show();
		return false;
	});
	
	/**** END ENTRY/LEAVING USER ****/
	
	
	/**** SCROLL MENU FIXED ****/
	// var positionMenu = $('#main-menu').offset().top;
	var positionMenu = 0;
	$(window).scroll(function(e){
		var st = $(this).scrollTop();
		if($(window).scrollTop() > positionMenu){
			$('body').addClass('scrolled');
		}
		else{
			$('body').removeClass('scrolled');
		}
	});
	/**** FIN SCROLL MENU FIXED ****/
	
	
	$('.detail-task .date').each(function(){
		if(!$(this).next().hasClass('task') || $(this).next().hasClass('isdone')){
			$(this).hide();
		}
	});
	$('.detail-task .task-date').hide();
	$('.detail-task .task-date').each(function(){
		// $(this).filter(function(){return $(this).find('.task').attr('class') == 'task '}).show();
		var filterDate = $(this).filter(function(){
			return $(this).find('.task').hasClass('isvalid')
		});
		// $(this).find('.date').each(function(){
			// if($(this).next().hasClass('task') && filterDate.length >= 1){
				console.log($(this).nextAll('.task').hasClass('isvalid'));
				// if($(this).nextAll('.task').hasClass('isvalid')){
					// $(this).show();
				// }
			// }
		// });
		filterDate.show();
	});
	$('.task-done').click(function(){
		$('.detail-task .task').hide();
		$('.detail-task .task.isdone').show();
		$('.detail-task .task-date').show();
		$('.detail-task .date').show();
		$('.detail-task .task-date .date').removeClass('hidden');
		$('.detail-task .date').each(function(){
			if(!$(this).next().hasClass('task') || $(this).next().hasClass('isvalid')){
				$(this).hide();
			}
		});
		$('.detail-task .task-date').each(function(){
			var filterDate = $(this).filter(function(){return !$(this).find('.task').hasClass('isdone')});
			// $(this).find('.date').each(function(){
				// if($(this).next().hasClass('task') && filterDate.length >= 1){
					// if($(this).nextAll('.task').hasClass('isdone')){
						// $(this).show();
					// }
				// }
			// });
			filterDate.hide();
		});
	});
	$('.task-active').click(function(){
		$('.detail-task .task').show();
		$('.detail-task .task.isdone').hide();
		$('.detail-task .task-date').hide();
		$('.detail-task .date').show();
		$('.detail-task .task-date .date').removeClass('hidden');
		$('.detail-task .date').each(function(){
			if(!$(this).next().hasClass('task') || $(this).next().hasClass('isdone')){
				$(this).hide();
			}
		});
		$('.detail-task .task-date').each(function(){
			var filterDate = $(this).filter(function(){return $(this).find('.task').hasClass('isvalid')});
			// $(this).find('.date').each(function(){
				// if($(this).next().hasClass('task') && filterDate.length >= 1){
					// if($(this).nextAll('.task').hasClass('isvalid')){
						// $(this).show();
					// }
				// }
			// });
			filterDate.show();
		});
	});
	
	$('.detail-task.user-task .task-date .task').click(function(){
		$('.detail-task-user').fadeIn();
	});
	
	$('.input-description textarea').keydown(function(e){
		if(this.value.length <= 200){
			$(this).parent().find('.character').text(this.value.length+'/200');
		}else{
			if(e.keyCode !== 8) {
				e.preventDefault();
			}
		}
	});
	
	$('.input-prestation .input-add .bt-add2').on('click', function(){
		$('.input-prestation .input-add .popup-prestation').stop().fadeOut();
		$(this).parents('.input-add').find('.popup-prestation').stop().fadeIn();
		return false;
	});
	$('.input-prestation .popup-prestation .bt-close').on('click', function(){
		$('.input-prestation .input-add .popup-prestation').stop().fadeOut();
		return false;
	});
	
	$('.input-group #group').on('ifChecked', function(){
		$('.option-group').show();
	});
	$('.input-group #group').on('ifUnchecked', function(){
		$('.option-group').hide();
	});
	
	
	
	$('.input-prestation input').on('ifChecked', function(){
		$(this).parents('.input-checkbox').addClass('active');
	});
	$('.input-prestation input').on('ifUnchecked', function(){
		$(this).parents('.input-checkbox').removeClass('active');
	});
	$('.input-number .number .minus').click(function(){
		if($(this).parents('.number').find('input').val() > 0){
			$(this).parents('.number').find('input').val(parseInt($(this).parents('.number').find('input').val()) -1);
		}
		return false;
	});
	$('.input-number .number .plus').click(function(){
		$(this).parents('.number').find('input').val(parseInt($(this).parents('.number').find('input').val()) +1);
		return false;
	});

	/*
	$('#main-left .list-task .bt-view').click(function(){
		if($('#detail-appartment').hasClass('active')){
			$('#detail-appartment').removeClass('active');
		}else{
			$('#detail-appartment').addClass('active');
		}
	});
	$('#detail-appartment .bt-close').click(function(){
		$('#detail-appartment').removeClass('active');
	});
	*/
	
	
	$('.fixed-right .bt-owner').click(function(){
		if($('#detail-appartment').hasClass('active')){
			$('#detail-owner').removeClass('active');
		}else{
			$('#detail-owner').addClass('active');
		}
	});
	$('#detail-owner .bt-close').click(function(){
		$('#detail-owner').removeClass('active');
	});
	
	
	
	/** INPUT FILE NAME **/
	$('.input-file input').change(function(e){
		$(this).parents('.input-file').next('.list-photo').empty();
		for(var i = 0; i<e.target.files.length; i++){
			$(this).parents('.input-file').next('.list-photo').append('<span class="txt">'+e.target.files[i].name+'</span>');
		}
	});
	/** FIN INPUT FILE NAME **/
	
	$(document).on('click', '.list-photo .photo', function(){
		obj = $(this);
		$('.list-images .image').hide();
		$('.list-images .image').filter(function(){
			return $(this).attr('data-number') == obj.attr('data-number')
		}).show();
	});	
	
	/** ADD/REMOVE INVOICE LINE **/
	$(document).on('click', '.detail-invoice .table-invoice .bt-delete', function(){
		if(confirm('Voulez-vous supprimer cette ligne ?')){
			$(this).parents('tr').remove();
		}
		return false;
	});
	
	$('#add-invoice-line').submit(function(e){
		e.preventDefault();
		var amount = parseInt($(this).find('#quantity').val() * $(this).find('#price').val());
		$('.detail-invoice .table-invoice tbody .last').before('<tr>'+
		'<td class="first"><a class="bt-delete" href="#">Supprimer</a></td>'+
		'<td>'+$(this).find('#quantity').val()+'</td>'+
		'<td>'+$(this).find('#prestation').val()+'</td>'+
		'<td>'+$(this).find('#price').val()+' €</td>'+
		'<td>'+amount+' €</td>'+
		'</tr>');
		$('.ui-dialog .lightbox-std').dialog('destroy');
	});
	/** FIN ADD/REMOVE INVOICE LINE **/
	
	/****** SELECT $ UI ******/
	$('.input-select select').selectmenu({
		create: function( event, ui ) {
			$(this).after($('#'+$(this).attr('id')+'-menu').parent());
			$('#'+$(this).attr('id')+'-button').addClass($(this).find('option:selected').attr('class'));
		},
		open: function( event, ui ) {
			var obj = $(this);
			var classArray = [];
			
			$(this).find('option').each(function(){
				if($(this).attr('class')){
					classArray.push($(this).attr('class'));
				}else{
					classArray.push('');
				}
			});
			$('#'+$(this).attr('id')+'-menu li').each(function(e){
				$(this).addClass(classArray[e]);
			});
		},
		change: function( event, ui ) {
			$('#'+$(this).attr('id')+'-button').removeClass('velvet green').addClass($(this).find('option:selected').attr('class'));
			if(ui.item.value != 0 && $(this).attr('id') == 'select-id'){
				document.location.href=ui.item.value;
			}
		}
	});
	
	$('.input-select-multiple select').select2({
	});
	$('.input-select-multiple select').on('select2:select', function (evt) {
		$(evt.currentTarget).find('option[value='+evt.params.data.id+']').attr('selected', 'selected');
	});
	$('.input-select-multiple select').on('select2:unselect', function (evt) {
		$(evt.currentTarget).find('option[value='+evt.params.data.id+']').attr('selected', '');
	});
	/****** SELECT $ UI ******/
	
	$('.list-user >.item').click(function(){
		if($(this).hasClass('active')){
			$(this).removeClass('active');
		}else{
			$(this).addClass('active');
		}
		$(this).find('.list-task').stop().slideToggle();
	});
	
	
	/****** DATEPICKER $ UI ******/
	$('.datepicker').datepicker({
		autoSize: false,
		dayNames: [ "Lundi", "Mardi", "Mercredi", "Jeudi", "Vendredi", "Samedi", "Dimanche" ],
		dayNamesMin: [ "Lun", "Mar", "Mer", "Jeu", "Ven", "Sam", "Dim" ],
		monthNames: [ "Janvier", "Fevrier", "Mars", "Avril", "Mai", "Juin", "Juillet", "Août", "Septembre", "Octobre", "Novembre", "Decembre" ],
		monthNamesShort: [ "Jan", "Fev", "Mar", "Avr", "Mai", "Jui", "Jui", "Aoû", "Sep", "Oct", "Nov", "Dec" ],
		showOtherMonths: true,
		dateFormat: "dd/mm/yy",
		firstDay: 1
	});
	/****** DATEPICKER $ UI ******/	
	
	
	/************************************
		Plugin for date range at 
		https://www.daterangepicker.com/
	************************************/
	
	if($('#main-left .left-owner').length > 0){
		if(GetURLParameter('dateStart')){
			var start = GetURLParameter('dateStart');
			start = moment(new Date(parseInt(start) * 1000));
		}else{
			var start = moment();
		}
		if(GetURLParameter('dateEnd')){
			var end = GetURLParameter('dateEnd');
			end = moment(new Date(parseInt(end) * 1000));
		}else{
			var end = moment().startOf('day').add(62, 'day');
		}
		// var start = moment();
		// var end = moment().startOf('day').add(62, 'day');

		function cb(start, end) {
			$('.daterange span').html(start.format('DD. MM. YYYY') + ' - ' + end.format('DD. MM. YYYY'));
		}

		$('.daterange').daterangepicker({
			 "locale": {
				"format": "DD/MM/YYYY",
				"separator": " - ",
				"applyLabel": "Confirmer",
				"cancelLabel": "Annuler",
				"fromLabel": "Du",
				"toLabel": "Au",
				"customRangeLabel": "Personnalisé",
				"weekLabel": "W",
				"daysOfWeek": [
					"Di",
					"Lu",
					"Ma",
					"Me",
					"Je",
					"Ve",
					"Sa"
				],
				"monthNames": [
					"Janvier",
					"Février",
					"Mars",
					"Avril",
					"Mai",
					"Juin",
					"Juillet",
					"Août",
					"Septembre",
					"Octobre",
					"Novembre",
					"Decembre"
				],
				"firstDay": 1
			},
			startDate: start,
			endDate: end
		}, cb);

		cb(start, end);
	}else{
		if(GetURLParameter('dateStart')){
			var start = GetURLParameter('dateStart');
			start = moment(new Date(parseInt(start) * 1000));
		}else{
			var start = moment().subtract(29, 'days');
		}
		if(GetURLParameter('dateEnd')){
			var end = GetURLParameter('dateEnd');
			end = moment(new Date(parseInt(end) * 1000));
		}else{
			var end = moment();
		}
		// var start = moment().subtract(29, 'days');
		// var end = moment();

		function cb(start, end) {
			$('.daterange span').html(start.format('DD. MM. YYYY') + ' - ' + end.format('DD. MM. YYYY'));
		}

		$('.daterange').daterangepicker({
			 "locale": {
				"format": "DD/MM/YYYY",
				"separator": " - ",
				"applyLabel": "Confirmer",
				"cancelLabel": "Annuler",
				"fromLabel": "Du",
				"toLabel": "Au",
				"customRangeLabel": "Personnalisé",
				"weekLabel": "W",
				"daysOfWeek": [
					"Di",
					"Lu",
					"Ma",
					"Me",
					"Je",
					"Ve",
					"Sa"
				],
				"monthNames": [
					"Janvier",
					"Février",
					"Mars",
					"Avril",
					"Mai",
					"Juin",
					"Juillet",
					"Août",
					"Septembre",
					"Octobre",
					"Novembre",
					"Decembre"
				],
				"firstDay": 1
			},
			startDate: start,
			endDate: end
			// ranges: {
			   // 'Aujourd\'hui': [moment(), moment()],
			   // 'Hier': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
			   // '7 derniers jours': [moment().subtract(6, 'days'), moment()],
			   // '30 derniers jours': [moment().subtract(29, 'days'), moment()],
			   // 'Mois courant': [moment().startOf('month'), moment().endOf('month')],
			   // 'Mois précedent': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
			// }
		}, cb);

		cb(start, end);
		
		$('.daterange').on('apply.daterangepicker', function(ev, picker) {
			//do something, like clearing an input
			var dateStart = Math.floor(picker.startDate._d.getTime() / 1000);
			var dateEnd = Math.floor(picker.endDate._d.getTime() / 1000);
			 window.location.href = 'http://' + window.location.hostname + window.location.pathname+"?dateStart="+dateStart+"&dateEnd="+dateEnd+"";
		});
	}
	
		
	$(document).on('click', '.ui-widget-overlay', function(){
		$('.ui-dialog .lightbox-std, .ui-dialog .csc-textpic-imagewrap').dialog('destroy');
		$('.ui-widget-overlay').remove();
	});
			
	$('.link-dialog').click(function(){
		dialog($(this));
		return false;
	});
	
	var lightboxOpen = GetURLParameter('lightbox');
	if(lightboxOpen == 1){
		$('#list-service').dialog({
			// modal: true,
			autoOpen: true,
			resizable: false,
			draggable: false,
			show: { effect: "fadeIn", duration: 200 },
			open: function(){	
				$('body').append('<div class="ui-widget-overlay ui-front" style="z-index: 998;"></div>');
			},
			close: function(){
				$('.ui-widget-overlay').remove();
			}
		});
	}

});

$(window).on('load', function (e) {
	

	/****** UNVEIL ******/
	/*$(function() {
		if($(".loading-img").size()>0){
			$(".loading-img img").unveil(3000, function(){});
		}
    });*/
	$(function() {
		if($(".loading-img").size()>0){
			$('.loading-img img').lazy({
				// works even for 'srcset' entries
				scrollDirection: 'horizontal',
				imageBase: '',
				delay: 1,
				afterLoad: function(element) {
					// $(element).parents('.loading-img').removeClass('loading-img');
					position_footer();
				},
			});
		}
    });
	/****** FIN UNVEIL ******/	
	
	$('.loading-img').each(function(){
		$(this).removeClass('loading-img');
	});

	
	if ( window.addEventListener ) {
        window.addEventListener( 'resize', redimensionnement, false );
    } else if ( window.attachEvent ) {
        window.attachEvent( 'onresize', redimensionnement );
		
    } else {
        window['onresize']=redimensionnement;
    }
	redimensionnement();
});


function redimensionnement(e){
		
	
}

function positionSubMenu(div){
	div.find('>ul >li').mouseenter(function(){
		var position_submenu = $(this).offset().left + ($(this).find('.submenu-content').outerWidth(true)/2);
		if(position_submenu > $(window).width()){
			$(this).addClass('submenu-right');
		}
		else{
			$(this).removeClass('submenu-right');
		}
	});
}

function dialog(div){
	if($('.ui-widget-overlay')){$('.ui-widget-overlay').hide();}
	$('#'+div.attr('data-dialog')).removeClass('hidden');
	$('#'+div.attr('data-dialog')).dialog({
		// modal: true,
		autoOpen: true,
		resizable: false,
		draggable: false,
        show: { effect: "fadeIn", duration: 200 },
		open: function(){	
			$('body').append('<div class="ui-widget-overlay ui-front" style="z-index: 998;"></div>');
		},
		close: function(){
			$('.ui-widget-overlay').remove();
		}
	});
    return false;
}

function menu_mobile(){
	if(isMobile){
		eventTouch = 'touchend';
	}else{
		eventTouch = 'click';
	}
	
	$('#main-menu nav >ul >li').each(function(){
		if(!$(this).find('>ul').is(':empty')){
			$(this).addClass('submenu');
		}
	});
	
	$(document).on(eventTouch, '#main-menu .mobile-menu, #main-menu .close', function(e){
		if(!$('#main-menu').hasClass('active')){
			$('#main-menu, body').addClass('active');
			// $('#main-menu .menu-header').stop().slideDown('normal');
		}
		else{
			$('#main-menu, body').removeClass('active');
			// $('#main-menu .menu-header').stop().slideUp('normal');
		}
		$('#main-menu nav >ul >li').removeClass('active');
		return false;
	});
	
	enquire.register("screen and (max-width: 950px)", {
		match : function() {
			$(document).on(eventTouch, '#main-menu nav >ul >li >a', function(e){
				if($(this).parent().find('>ul, >.submenu').length > 0){
					if(!$(this).parent().hasClass('active')){
						$('#main-menu nav >ul >li').removeClass('active');
						$(this).parent().addClass('active');
						$('#main-menu nav >ul >li >ul, #main-menu nav >ul >li >.submenu').stop().slideUp('normal');
						$(this).parent().find('>ul, >.submenu').stop().slideDown('normal');
					}
					else{
						$('#main-menu nav >ul >li').removeClass('active');
						$(this).parent().find('>ul, >.submenu').stop().slideUp('normal');
					}
					return false;
				}
			});
			
			$(document).on(eventTouch, '#main-menu nav >ul >li >ul >li >a', function(e){
				if($(this).parent().find('>ul').length > 0){
					if(!$(this).parent().hasClass('active')){
						$('#main-menu nav >ul >li >ul >li').removeClass('active');
						$(this).parent().addClass('active');
						$('#main-menu nav >ul >li >ul >li >ul').stop().slideUp('normal');
						$(this).parent().find('>ul').stop().slideDown('normal');
					}
					else{
						$('#main-menu nav >ul >li >ul >li').removeClass('active');
						$(this).parent().find('>ul').stop().slideUp('normal');
					}
					return false;
				}
			});
		}
	});
		
}

function GetURLParameter(sParam)
{
    var sPageURL = window.location.search.substring(1);
    var sURLVariables = sPageURL.split('&');
    for (var i = 0; i < sURLVariables.length; i++) 
    {
        var sParameterName = sURLVariables[i].split('=');
        if (sParameterName[0] == sParam) 
        {
            return sParameterName[1];
        }
    }
}

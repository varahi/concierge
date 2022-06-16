$(document).ready(function(){
	$('input, textarea').placeholder();
	$('input').iCheck();
		
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
		
	var isIE11 = !!navigator.userAgent.match(/Trident.*rv[ :]*11\./)
	if($.browser['msie'] || isIE11){
		$('html').addClass('ie');
	}
	
	
	/**** CALENDAR FULL ****/
	var date = new Date();
	var d = date.getDate();
	var m = date.getMonth();
	var y = date.getFullYear();
	var events = [
		{
          title: 'ASFOR',
          start: '2021-06-24T08:00:00',
		  end: '2021-06-24T10:00:00',
		  editable: false,
		  backgroundColor: '#9A7B15'
        },
		{
          title: 'CEMAC',
          start: '2021-06-24T09:30:00',
		  end: '2021-06-24T11:30:00',
		  editable: false,
		  backgroundColor: '#C63A10'
        },
		{
          title: 'ASFOR',
          start: '2021-06-26T12:00:00',
		  end: '2021-06-26T14:00:00',
		  editable: false,
		  backgroundColor: '#582708'
        }
	]
	var eventAdd = false;
	if($('#calendar').length > 0){
		
	}
	/**** CALENDAR FULL ****/
	
	
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
	
	$('.task-done').click(function(){
		$('.detail-task .task').hide();
		$('.detail-task .task.isdone').show();
	});
	$('.task-active').click(function(){
		$('.detail-task .task').show();
		$('.detail-task .task.isdone').hide();
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

	/** INPUT FILE NAME **/
	$('.input-file input').change(function(e){
		$(this).parents('.input-file').next('.list-photo').empty();
		for(var i = 0; i<e.target.files.length; i++){
			$(this).parents('.input-file').next('.list-photo').append('<span class="txt">'+e.target.files[i].name+'</span>');
		}
	});
	/** FIN INPUT FILE NAME **/
	
	
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
	var start = moment().subtract(29, 'days');
    var end = moment();

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
        endDate: end,
        ranges: {
           'Aujourd\'hui': [moment(), moment()],
           'Hier': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
           '7 derniers jours': [moment().subtract(6, 'days'), moment()],
           '30 derniers jours': [moment().subtract(29, 'days'), moment()],
           'Mois courant': [moment().startOf('month'), moment().endOf('month')],
           'Mois précedent': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
        }
    }, cb);

    cb(start, end);
	
		
	$(document).on('click', '.ui-widget-overlay', function(){
		$('.ui-dialog .lightbox-std, .ui-dialog .csc-textpic-imagewrap').dialog('destroy');
	});
			
	$('.link-dialog').click(function(){
		dialog($(this));
		return false;
	});
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
		modal: true,
		autoOpen: true,
		resizable: false,
		draggable: false,
                show: { effect: "fadeIn", duration: 200 },
		open: function(){		
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

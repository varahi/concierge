$(document).ready(function(){
    // Ui dialog
    //$('.close-ui-dialog').click(function(){
    //    $('.ui-dialog').css('display', 'none');
    //});

    // Main magnificpopup init script
    $('.ajax-popup-link').magnificPopup({
        type: 'ajax',
        closeOnBgClick: true,
        callbacks: {
            ajaxContentAdded: function() {
                $('input').iCheck();
				// $(".inputmask").inputmask();
				// $(".mask-phone").inputmask("99 99 99 99 99");
				
				$('.mask-phone').each(function(){
					var phoneInputID = $(this).attr('id');
					var input = document.querySelector("#"+phoneInputID);
					var iti = window.intlTelInput(input, {
						// allowDropdown: false,
						// autoHideDialCode: false,
						// autoPlaceholder: "off",
						// customPlaceholder: false,
						// dropdownContainer: document.body,
						// excludeCountries: ["us"],
						formatOnDisplay: true,
						hiddenInput: $(this).attr('id')+"_full_number",
						// initialCountry: "auto",
						// localizedCountries: { 'de': 'Deutschland' },
						nationalMode: true,
						// onlyCountries: ['us', 'gb', 'ch', 'ca', 'do'],
						// placeholderNumberType: "MOBILE",
						preferredCountries: ['fr'],
						separateDialCode: true,
						utilsScript: "https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/11.0.14/js/utils.js"
					});

					$("#"+phoneInputID).on("countrychange", function(event) {
						// Get the selected country data to know which country is selected.
						var selectedCountryData = iti.getSelectedCountryData();
						// Get an example number for the selected country to use as placeholder.
						newPlaceholder = intlTelInputUtils.getExampleNumber(selectedCountryData.iso2, true, intlTelInputUtils.numberFormat.INTERNATIONAL),
						// Reset the phone number input.
						// iti.setNumber("+33"+$(this).val());
						iti.setNumber($(this).val());
						// Convert placeholder as exploitable mask by replacing all 1-9 numbers with 0s
						mask = newPlaceholder.replace(/[0-9]/g, "9");
						// Apply the new mask for the input
						$(this).inputmask(mask);
					});

					// When the plugin loads for the first time, we have to trigger the "countrychange" event manually, 
					// but after making sure that the plugin is fully loaded by associating handler to the promise of the 
					// plugin instance.
					iti.promise.then(function() {
						$("#"+phoneInputID).trigger("countrychange");
					});
				});
				
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
            }
        }
    });
	
	var searchUser = GetURLParameter('employee');
	if(searchUser){
		var searchUserArr = searchUser.split(',');
		$.grep(searchUserArr, function(item) {
			$('.list-user input').filter(function(){
				return $(this).attr('data-user') == item
			}).iCheck('check');
		});
	}
	$('.list-user input').on('ifChecked', function(event){
		if($(this).attr('data-user')){
			if(window.location.search == ''){
				user = "?employee=" + $(this).attr('data-user');
			}else{
				user = window.location.search + "," + $(this).attr('data-user')
			}
			window.location = window.location.origin + window.location.pathname + user;
		}
	});
	$('.list-user input').on('ifUnchecked', function(event){
		if($(this).attr('data-user')){
			user = $(this).attr('data-user');
			searchUserArr.splice( $.inArray(user, searchUserArr), 1 );
			searchUser = searchUserArr.join(',');
			if(window.location.search){
				users = "?employee=" + searchUser;
			}else{
				users = "";
			}
			window.location = window.location.origin + window.location.pathname + users;
		}
	});
	
	
	$('#main-left .left .content >.button a.bt-pdf-text').click(function(){
		var userList = $('.list-user .icheckbox').filter(function(){
			return $(this).hasClass('checked')
		});
		var user = userList.map(function() {
			return $(this).find('input').attr('data-user-pdf');
		}).get().join(',');
		user = "?users=" +user;
		window.open('http://' + window.location.hostname + $(this).attr('href') + user);
		return false;
	});

});

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


$(document).ready(function(){
    // Main magnificpopup init script
    $('.ajax-popup-link').magnificPopup({
        type: 'ajax',
        closeOnBgClick: false,
        callbacks: {
            ajaxContentAdded: function() {
                $('input').iCheck();
                // Ajax content is loaded and appended to DOM
                $('.input-select select').selectmenu({
                    create: function( event, ui ) {
                        $(this).after($('#'+$(this).attr('id')+'-menu').parent());
                    }
                });
                $('.input-select-multiple select').select2({
                });
            }
        }
    });
	


	/**** CALENDAR FULL ****/
	var date = new Date();
	var d = date.getDate();
	var m = date.getMonth();
	var y = date.getFullYear();
	var events = [
		{
          title: 'Durand - 06 60 74 22 04',
		  resourceId: 'a',
          start: '2021-09-24T08:00:00',
		  end: '2021-09-24T10:00:00',
		  editable: false,
		  backgroundColor: '#00B715',
		  url: '/edit-event'
        },
		{
          title: 'Durand - 06 60 74 22 04',
		  resourceId: 'b',
          start: '2021-09-24T09:30:00',
		  end: '2021-09-29T11:30:00',
		  editable: false,
		  backgroundColor: '#00B715',
		  url: '/edit-event'
        },
		{
          title: 'Durand - 06 60 74 22 04',
		  resourceId: 'c',
          start: '2021-09-26T12:00:00',
		  end: '2021-09-26T14:00:00',
		  editable: false,
		  backgroundColor: '#00B715',
		  url: '/edit-event'
        }
	]

	var eventAdd = false;
	if($('#calendar').length > 0){ 
		var calendarEl = document.getElementById('calendar');
		var calendar = new FullCalendar.Calendar(calendarEl, {
			locale: 'fr',
			schedulerLicenseKey: '0585043515-fcs-1632212428',
			headerToolbar: {
				left: '',
				center: 'prev,title,next',
				right: ''
			},
			titleFormat: { year: 'numeric', month: 'long', day: 'numeric' },
			dayHeaderFormat: { weekday: 'short' },
			initialView: 'resourceTimelineDay',
			resourceAreaWidth: '170px',
			views: {
			  resourceTimelineDay: {
				type: 'resourceTimeline',
				duration: { days: 14 },
				buttonText: '14 days',
				slotDuration: '24:00',
				slotLabelFormat: [
					{ weekday: 'short' },
					{ day: 'numeric' }
				]
			  }
			},
			editable: true,
			resourceAreaHeaderContent: ' ',
			resources: 'https://fullcalendar.io/demo-resources.json?with-nesting&with-colors',
			events: events,
			eventRender: function(eventObj, $el) {
				console.log($el);
			},
			eventClick: function(info) {
				console.log(info.event.url);
				info.jsEvent.preventDefault();
				$.magnificPopup.open({
					items: {
						src: info.event.url,
					},
					type: 'ajax',
					closeOnBgClick: false,
					callbacks: {
						ajaxContentAdded: function() {
							$('input').iCheck();
							// Ajax content is loaded and appended to DOM
							$('.input-select select').selectmenu({
								create: function( event, ui ) {
									$(this).after($('#'+$(this).attr('id')+'-menu').parent());
								}
							});
							$('.input-select-multiple select').select2({
							});
						}
					}
				});
				
			}
		});

		calendar.render();
	}
	/**** CALENDAR FULL ****/
});



$(document).ready(function(){
	//$("#count_orders").html($(".table").size());
	
	$(".validate").validate();
	
	$("textarea.allergy_hint").htmlarea();
	
	$("#filter").dataTable();
	$('.datepicker').datepicker({
		dateFormat: 	'dd.mm.yy',
		showWeek: 		true, 
		weekHeader: 	'KW',
		firstDay:		1,
		dayNamesMin:	['So', 'Mo', 'Di', 'Mi', 'Do', 'Fr', 'Sa']
	});
	
	$("table.waiters_call tr").live("click", function(){
		var wcid = $(this).attr("id");
		window.location = "index.php?action=deleteItem&location=waiterCalls&table=waiters_call&id="+wcid;
	});
	
});
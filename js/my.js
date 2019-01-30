jQuery(document).ready(function(){
	$(".sprachbutton").live("click", function(){
		var lang = $(this).attr("id");
		window.location.href = "karte.php?lang="+lang;
	});
	
	$('.validate').validate();
	
	$('.lightbox').lightbox();
	
	$(".foodmenu_top_button").live("click", function(){
		var kategorie = $(this).attr("id");
		$("#content").hide();
		$("#show_products").show();
		$(".foodmenu_top_button").removeClass("active");
		$(this).addClass("active");
		$("#foodmenu_sub").load("includes/load_subkategorien.php", {kategorie: kategorie});
		$("#image").load("includes/load_kategoriebild.php", {kategorie: kategorie});
		$('#food').empty();
	});
	
	$(".foodmenu_sub_button").live("click", function(){
		var kategorie = $(this).attr("id");
		$("#content").hide();
		$("#show_products").show();
		$(".foodmenu_sub_button").removeClass("active");
		$(this).addClass("active");
		$("#food").load("includes/load_speisen.php", {kategorie: kategorie});
		$("#image").empty().load("includes/load_kategoriebild.php", {kategorie: kategorie}, function() { 
			window.location.href = 'karte.php';
		});
		
	});
	
	$(".speise").live("click", function(){
		$(".speise").removeClass("active");
		$(this).addClass("active");
		var id = $(this).attr("id");
		$("#image").load("includes/load_speise.php", {id: id});
	});
	
	$(".speise_meal").live("click", function(){
		$(".speise_meal").removeClass("active");
		$(".loaded").remove();
		
		$(this).addClass("active");
		var id = $(this).attr("id");
		//var actualCourse = $(this).attr("actualCourse");
		var action = $(this).attr("action");
		var inMenu = $(this).attr("inMenu");
		
		$("#image").append( $("<div class='loaded'>").load("includes/load_speise.php", {id: id, action: action, inMenu: inMenu}));
		
	});
	
	
	//$(".mehr_infos").live("click", function(){
		
	//});
	
	//$("input[type=submit], input[type=reset], input[type=button]").button();
});



var food;
var aboutus;
var payment;

function loadedFood() {
	document.addEventListener('touchmove', function(e){ e.preventDefault(); }, false);
	food = new iScroll('food');
	
	new iScroll('jquery-lightbox-html');
}
function loadedPayment() {
	document.addEventListener('touchmove', function(e){ e.preventDefault(); }, false);
	payment = new iScroll('payment');
	
	new iScroll('jquery-lightbox-html');
}
function loadedAboutus() {
	document.addEventListener('touchmove', function(e){ e.preventDefault(); }, false);
	aboutus = new iScroll('aboutus');
	
	new iScroll('jquery-lightbox-html');
}

document.addEventListener('DOMContentLoaded', loadedFood, false);
document.addEventListener('DOMContentLoaded', loadedPayment, false);
document.addEventListener('DOMContentLoaded', loadedAboutus, false);



//Kontextmenu Workaround
/**
function absorbEvent_(event) {
	var e = event || window.event;
	e.preventDefault && e.preventDefault();
	e.stopPropagation && e.stopPropagation();
	e.cancelBubble = true;
	e.returnValue = false;
	return false;
}

function preventLongPressMenu(node) {
	//node.ontouchstart = absorbEvent_;
	node.ontouchstart = node.onclick;
	node.ontouchmove = absorbEvent_;
	node.ontouchend = absorbEvent_;
	node.ontouchcancel = absorbEvent_;
}

function init() {
	preventLongPressMenu(document.getElementsByTagName('img'));
}
**/
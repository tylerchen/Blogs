$(document).ready(function(){
	$.get(getURLParameter("md"), function(data){
		if(data){$("#bodyColumn").html(markdown.toHTML(data));}
	});
});
function getURLParameter(name) {
	return decodeURI(
		(RegExp(name + '=' + '(.+?)(&|$)').exec(location.search)||[,null])[1]
	);
}
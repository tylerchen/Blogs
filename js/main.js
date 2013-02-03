$(document).ready(function(){
	var url = getURLParameter("md");
	if(url){
		$.get(url, function(data){
			if(data){
				$("#bodyColumn").html(markdown.toHTML(data));
			}
		});
	}
});
function getURLParameter(name) {
	return decodeURI(
		(RegExp(name + '=' + '(.+?)(&|$)').exec(location.search)||[,null])[1]
	);
}
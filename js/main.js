$(document).ready(function(){
	var url = getURLParameter("md");
	if(url){
		$.get(url, function(data){
			if(data){
				$("#bodyColumn").html(markdown.toHTML(data));
			}
		});
		#("#leftColumn a").each(function(i){
			if(this.href && this.href.indexOf("md="+url)>-1){
				$(this).parent().addClass("active");
			}
		});
	}
});
function getURLParameter(name) {
	return decodeURI(
		(RegExp(name + '=' + '(.+?)(&|$)').exec(location.search)||[,null])[1]
	);
}
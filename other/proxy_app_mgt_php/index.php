<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
	<title>Proxy Applications</title>
</head>
<?php
function get_proxies(){
	$proxyArr=array();

	$fd = @fopen("/var/www/html/proxy_applications.list", "r");
	if ($fd == false) {
		echo "<H1>fail to open file $passwd_temp!<\H1>";
		exit;
	}
	rewind($fd); /* unnessecary but I'm paranoid */

	while (!feof($fd)) {
		$buffer = fgets($fd, 4096);
		/* all data is comprised of a name, an optional seperator, and a datum */

		/* oh wow!.. trim()!!! I could hug somebody! */
		$buffer = trim($buffer);
		if( strlen ($buffer) < 1 || $buffer[0] == "#"){
			continue;
		}
		{/* process proxy=url*/
			if(strpos($buffer,'=') > 0){
				$pos = strpos($buffer,'=');
				$proxyArr[trim(substr($buffer,0,$pos))] = trim(substr($buffer,$pos+1));
			}
		}
	}
	fclose($fd);
	return $proxyArr;
}
?>
<body>
	<div class="sitemap">
		<h1>Proxy Applications</h1>
		
		<ul id="utilityNav">
			<li><a href="/proxy_applications_management.php">Management</a></li>
		</ul>
		
		<ul id="primaryNav" class="col4">
			<li id="home"><a href="/">Home</a></li>
			<li><a href="javascript:;">Default</a>
			</li>
			<li><a href="/">Other</a>
				<ul>
				<?php  $proxies = get_proxies(); foreach ($proxies as $k => $v) { ?>
					<li><a href="/<?php echo $k; ?>"><?php echo htmlentities($k); ?></a></li>
				<?php } ?>
				<ul>
			</li>	
		</ul>

	</div>
	
</body>
<style type="text/css">
	/* ------------------------------------------------------------
		Reset Styles (from meyerweb.com)
	------------------------------------------------------------ */

	html, body, div, span, applet, object, iframe,
	h1, h2, h3, h4, h5, h6, p, blockquote, pre,
	a, abbr, acronym, address, big, cite, code,
	del, dfn, em, font, img, ins, kbd, q, s, samp,
	small, strike, strong, sub, sup, tt, var,
	dl, dt, dd, ol, ul, li,
	fieldset, form, label, legend,
	table, caption, tbody, tfoot, thead, tr, th, td {
		margin: 0;
		padding: 0;
		border: 0;
		outline: 0;
		font-weight: inherit;
		font-style: inherit;
		font-size: 100%;
		font-family: inherit;
		vertical-align: baseline;
	}

	/* ------------------------------------------------------------
		NUMBER OF COLUMNS: Adjust #primaryNav li to set the number
		of columns required in your site map. The default is 
		4 columns (25%). 5 columns would be 20%, 6 columns would 
		be 16.6%, etc. 
	------------------------------------------------------------ */

	#primaryNav li {
		width:25%;
	}

	#primaryNav li ul li {
		width:100% !important;
	}

	#primaryNav.col1 li { width:99.9%; }
	#primaryNav.col2 li { width:50.0%; }
	#primaryNav.col3 li { width:33.3%; }
	#primaryNav.col4 li { width:25.0%; }
	#primaryNav.col5 li { width:20.0%; }
	#primaryNav.col6 li { width:16.6%; }
	#primaryNav.col7 li { width:14.2%; }
	#primaryNav.col8 li { width:12.5%; }
	#primaryNav.col9 li { width:11.1%; }
	#primaryNav.col10 li { width:10.0%; }

	/* ------------------------------------------------------------
		General Styles
	------------------------------------------------------------ */

	body {
		background: white;
		color: black;
		padding: 40px;
		font-family: Gotham, Helvetica, Arial, sans-serif;
		font-size: 12px;
		line-height: 1;
	}
	.sitemap {
		margin: 0 0 40px 0;
		float: left;
		width: 100%;
	}
	h1 {
		font-weight: bold;
		text-transform: uppercase;
		font-size: 20px;
		margin: 0 0 5px 0;
	}
	h2 {
		font-family: "Lucida Grande", Verdana, sans-serif;
		font-size: 10px;
		color: #777777;
		margin: 0 0 20px 0;
	}
	a {
		text-decoration: none;
	}
	ol, ul {
		list-style: none;
	}


	/* ------------------------------------------------------------
		Site Map Styles
	------------------------------------------------------------ */

	/* --------	Top Level --------- */

	#primaryNav {
		margin: 0;
		float: left;
		width: 100%;
	}
	#primaryNav #home {
		display: block;
		float: none;
		background: #ffffff url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAzQAAAAgCAYAAADE3T1nAAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAANNJREFUeNrs2bENgDAQBEEOUYX7r81tvKEE5OBBmkmcX2KtPlV1AMBbScbz3v/ItAYAXU4TAAAAggYAAEDQAAAACBoAAEDQAAAACBoAAABBAwAAIGgAAABBAwAAIGgAAAAEDQAAIGgAAAAEDQAAgKABAAAQNAAAgKABAABoc5kAgB1JhhUA6OJCAwAA/JYLDQBbqmpaAYAuLjQAAICgAQAAEDQAAACCBgAAEDQAAACCBgAAQNAAAAAIGgAAQNAAAAAIGgAAAEEDAAAIGgAAgC9bAgwAjzsLQdrgUSkAAAAASUVORK5CYII=) center bottom no-repeat;
		position: relative;
		z-index: 2;
		padding: 0 0 30px 0;
	}
	#primaryNav li {
		float: left;
		background: url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAzQAAAAgCAYAAADE3T1nAAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAAM5JREFUeNrs2bEJgEAQRFHHMuy/NtsYDwONjWThPbgGJjk+m7YbAADARLsJAAAAQQMAACBoAAAABA0AACBoAAAABA0AAICgAQAAEDQAAICgAQAAEDQAAACCBgAAEDQAAACCBgAAQNAAAAA8st5hBgAAYCIXGgAAYKy0tQIA3z+Q5L7wr3/ktAYAf3GhAQAABA0AAICgAQAAEDQAAICgAQAAEDQAAACCBgAAQNAAAACCBgAAQNAAAAAIGgAAQNAAAAAIGgAAAEEDAADwugQYANKgClIGxq9jAAAAAElFTkSuQmCC) center top no-repeat;
		padding: 30px 0;
		margin-top: -30px;
	}
	#primaryNav li a {
		margin: 0 20px 0 0;
		padding: 10px 0;
		display: block;
		font-size: 14px;
		font-weight: bold;
		text-align: center;
		color: black;	
		background: #c3eafb url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAADIAAAAtCAYAAADsvzj/AAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAAF9JREFUeNrs2UsOABAMQEES9987q0+5hI2OxAEmr91QI6KXD067d4OAvIEsRRQByQGZIEZLkRxFhtECAckBsewgIHYExGgpAgJi2T0+KKIIiNd4kKcQX2+KgCSAHAEGAF07L6+lvtV3AAAAAElFTkSuQmCC) top left repeat-x;
		border: 2px solid #b5d9ea;
		-moz-border-radius: 5px;
		-webkit-border-radius: 5px;
		-webkit-box-shadow: rgba(0,0,0,0.5) 2px 2px 2px; 
		-moz-box-shadow: rgba(0,0,0,0.5) 2px 2px 2px; /* FF 3.5+ */	
	}
	#primaryNav li a:hover {
		background-color: #e2f4fd;
		border-color: #97bdcf;
	}
	#primaryNav li:last-child {
		background: url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAzQAAAAgCAYAAADE3T1nAAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAAMlJREFUeNrs2bERwCAMBEHEUIX7r402nszOSQwzuyV8dlIlaQAAADfqJgAAAAQNAACAoAEAABA0AACAoAEAABA0AAAAggYAAEDQAAAAggYAAEDQAAAACBoAAEDQAAAACBoAAABBAwAA8BpV9ZgBgF1JphUA+IsPDQAAcK3hsgbADh9+AE7gQwMAAAgaAAAAQQMAACBoAAAAQQMAACBoAAAABA0AAICgAQAABA0AAICgAQAAEDQAAICgAQAAEDQAAACCBgAA4LMEGACUrQtBpLV3jQAAAABJRU5ErkJggg==) center top no-repeat;
	}
	a:link:before,
	a:visited:before {
		content: " "attr(href)" ";
		display: block;
		text-transform: uppercase;
		font-size: 10px;
		margin-bottom: 5px;
		word-wrap: break-word;
	}
	#primaryNav li a:link:before,
	#primaryNav li a:visited:before {
		color: #78a9c0;
	}

	/* --------	Second Level --------- */

	#primaryNav li li {
		width: 100%;
		clear: left;
		margin-top: 0;
		padding: 10px 0 0 0;
		background: url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABYAAAAyCAYAAABYiSsbAAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAADpJREFUeNrszEENACAQBLEFGfjXho3jREDCo5PMtyPJ6lNVOxebeRQYDAaDwWAwGAwGg8Fg8G/wEWAApYYEYerUPvsAAAAASUVORK5CYII=) center bottom repeat-y;
	}
	#primaryNav li li a {
		background-color: #cee3ac;
		border-color: #b8da83;
	}
	#primaryNav li li a:hover {
		border-color: #94b75f;
		background-color: #e7f1d7;
	}
	#primaryNav li li:first-child {
		padding-top: 30px;
	}
	#primaryNav li li:last-child {
		background: url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABYAAAAyCAYAAABYiSsbAAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAADpJREFUeNrszEENACAQBLEFGfjXho3jREDCo5PMtyPJ6lNVOxebeRQYDAaDwWAwGAwGg8Fg8G/wEWAApYYEYerUPvsAAAAASUVORK5CYII=) center bottom repeat-y;
	}
	#primaryNav li li a:link:before,
	#primaryNav li li a:visited:before {
		color: #8faf5c;
	}

	/* --------	Third Level --------- */

	#primaryNav li li ul {
		margin: 10px 0 0 0;
		width: 100%;
		float: right;
		padding: 9px 0 10px 0;
		background: #ffffff url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAzYAAAAKCAYAAABmH8sKAAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAAGtJREFUeNrs3cEJwCAQBEDXMuy/Ntu4mBLixwgzcPjfjyzCmapqAPBVkvGe6x6Z0gDgtC4CAABAsQEAAFBsAAAAFBsAAECxAQAAUGwAAACOypohBgB2WfcMwB94sQEAAK4XH3QCAAC3ewQYANedDgnIkiMTAAAAAElFTkSuQmCC) center top no-repeat;
	}
	#primaryNav li li li {
		background: url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAEgAAACWCAYAAAB92c4YAAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAAOhJREFUeNrs3LENwCAMRcEPYorsPxtrOClIRUeJ7kmWXJ9cuyV5vklVzWirIwAECBAgQIAAARIgQIAAAQIECJAAAQIECBAgQIAECBAgQIAAAQIESIAAAQIECBAgQAIECBAgQIAAARIgQIAAAQIECBAgAQIECBAgQIAACRAgQIAAAbqjlvWJUy7oqPEvfrm6IECAAAECBAiQAAECBAgQIECABAgQIECAAAECBEiAAAECBAgQIEACBAgQIECAAAESIECAAAECBAgQIAECBAgQIECAAAkQIECAAAECBEiAAAECBAgQoKt7BRgA5d4JJ+MZciMAAAAASUVORK5CYII=) left center no-repeat;
		padding: 5px 0;
	}
	#primaryNav li li li a {
		background-color: #fff7aa;
		border-color: #e3ca4b;
		font-size: 12px;
		padding: 5px 0;
		width: 80%;
		float: right;
	}
	#primaryNav li li li a:hover {
		background-color: #fffce5;
		border-color: #d1b62c;
	}
	#primaryNav li li li:first-child {
		padding: 15px 0 5px 0;
		background: url(data:image/png;base64,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) left center no-repeat;
	}
	#primaryNav li li li:last-child {
		background: url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAEgAAACuCAYAAACV8EvYAAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAAQNJREFUeNrs3DENwDAAA0EnMMofW2k4GbJVXTp0updM4AB4JLn20vaOHk0EgAABAgQIECBAAgQIECBAgAABEiBAgAABAgQIkAABAgQIECBAgAAJECBAgAABAgRIgAABAgQIECBAAgQIECBAgAABAiRAgAABAgQIECABAgQIECBAgAAJECBAgAABAgQIkF4aOaf/AvQNqC0FQIAAAQIECBAgAQIECBAgQIAACRAgQIAAAQIECBAgQIAAAQIECBAgAQIECBAgQIAACRAgQIAAAQIECBAgQIAAAQIECBAgAQIECBAgQIAACRAgQIAAAQIECBAgQIAAAQIECBAgAQIE6PeWAAMABE4JPn/E9NwAAAAASUVORK5CYII=) left center no-repeat;
	}
	#primaryNav li li li a:link:before,
	#primaryNav li li li a:visited:before {
		color: #ccae14;
		font-size: 9px;
	}


	/* ------------------------------------------------------------
		Utility Navigation
	------------------------------------------------------------ */

	#utilityNav {
		float: right;
		max-width: 50%;
		margin-right: 10px;
	}
	#utilityNav li {
		float: left;
		margin-bottom: 10px;
	}
	#utilityNav li a {
		margin: 0 10px 0 0;
		padding: 5px 10px;
		display: block;	
		border: 2px solid #e3ca4b;
		font-size: 12px;
		font-weight: bold;
		text-align: center;
		color: black;
		background: #fff7aa url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAADIAAAAtCAYAAADsvzj/AAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAAF9JREFUeNrs2UsOABAMQEES9987q0+5hI2OxAEmr91QI6KXD067d4OAvIEsRRQByQGZIEZLkRxFhtECAckBsewgIHYExGgpAgJi2T0+KKIIiNd4kKcQX2+KgCSAHAEGAF07L6+lvtV3AAAAAElFTkSuQmCC) top left repeat-x;
		-moz-border-radius: 5px;
		-webkit-border-radius: 5px;
		-webkit-box-shadow: rgba(0,0,0,0.5) 2px 2px 2px; 
		-moz-box-shadow: rgba(0,0,0,0.5) 2px 2px 2px; /* FF 3.5+ */	
	}
	#utilityNav li a:hover {
		background-color: #fffce5;
		border-color: #d1b62c;
	}
	#utilityNav li a:link:before,
	#utilityNav li a:visited:before {
		color: #ccae14;
		font-size: 9px;
		margin-bottom: 3px;
	}
</style>
</html>


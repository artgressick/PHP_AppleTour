<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>Apple Special Event</title>
<link href="<?=$BF?>includes/global.css" rel="stylesheet" type="text/css" />
<link href="<?=$BF?>includes/nav.css" rel="stylesheet" media='all' type="text/css">
<link href="<?=$BF?>includes/nav_black.css" rel="stylesheet" media='all' type="text/css">
</head>
<body onLoad="<?=(isset($bodyParams) ? $bodyParams : '')?>">
<div>
<script src="http://www.apple.com/global/nav/scripts/shortcuts.js" type="text/javascript" charset="utf-8"></script>
<script type="text/javascript" charset="utf-8">
	var searchSection = 'global';
	var searchCountry = 'us';
</script>
<div id="globalheader">
	<!--googleoff: all-->
	<ul id="globalnav">
		<li id="gn-apple"><a href="http://www.apple.com/">Apple</a></li>
		<li id="gn-store"><a href="http://www.apple.com/store/">Store</a></li>
		<li id="gn-mac"><a href="http://www.apple.com/mac/">Mac</a></li>

		<li id="gn-ipoditunes"><a href="http://www.apple.com/itunes/">iPod + iTunes</a></li>
		<li id="gn-iphone"><a href="http://www.apple.com/iphone/">iPhone</a></li>
		<li id="gn-downloads"><a href="http://www.apple.com/downloads/">Downloads</a></li>
		<li id="gn-support"><a href="http://www.apple.com/support/">Support</a></li>
	</ul>
	<!--googleon: all-->
	<div id="globalsearch">

		<form action="http://searchcgi.apple.com/cgi-bin/sp/nph-searchpre11.pl" method="POST" class="search" id="g-search">
			<input type="hidden" value="utf-8" name="oe" id="search-oe">
			<input type="hidden" value="p" name="access" id="search-access">
			<input type="hidden" value="us_only" name="site" id="search-site">
			<input type="hidden" value="lang_en" name="lr" id="search-lr">
			<label for="sp-searchtext"><span class="prettyplaceholder">Search</span><input type="search" name="q" id="sp-searchtext" class="g-prettysearch applesearch" style="height:25px;"></label>

		</form>
		<div id="sp-results"><div class="inside"></div></div>

	</div>
</div>
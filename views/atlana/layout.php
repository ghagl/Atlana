<html>
	<head>
		<meta name="viewport" content="width=device-width, initial-scale=0.55">
		<meta charset="utf8"/>
		<title><?=$this->e($title)?></title>
        
		<link rel="stylesheet" type="text/css" href="/assets/default.css">
		<!--<link rel="stylesheet" type="text/css" href="/assets/font-awesome.min.css">
		<link href="/images/fav.png" rel="shortcut icon">-->
	</head>

	<body>
		<div id="header">
			<div id="logo">
				<a href="/">Atlana</a>
			</div>
			<div id="navigation">
				<a href="/">Home</a>
			</div>
		</div>
		<div id="content">
			<!-- no title -->
			<div class="home-img">
				<img src="/assets/iceweasel.png"/>
			</div>

			<?=$this->section('content')?>
		</div>

		<div class="footer">
			<div class="footer-lt">
				<!--<a href=""><i class="fa fa-github"></i></a>
				<a href=""><i class="fa fa-twitter"></i></a>
				<a href=""><i class="fa fa-google-plus"></i></a>
				<a href=""><i class="fa fa-envelope"></i></a>-->
			</div>
			<div class="footer-rt">
				Powered by Atlana
			</div>
		</div>
</html>

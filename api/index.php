<?php
require_once 'config.php';

$title   = "Arduino In/Out Temperature";
$version = "v3";
$today   = strftime("%A, %d de %B de %Y, %H:%M", strtotime(date('Y-m-d h:i')));

if(isset($_GET['json']))
{
	header('Cache-Control: no-cache, must-revalidate');
	header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
	header('Content-type: application/json');

	$date = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');
	$inOut = new InOut;
	die($inOut->getData($_GET['json'], $date));

} ?>
<!DOCTYPE html>
<html>
<head>
	<title><?php echo $title . ' - ' . $today . ' '. $version ?></title>
	<meta name="author" value="hewertho.mn">
	<meta http-equiv="refresh" content="<?php echo $refresh ?>">
	<link rel="shortcut icon" href="favicon.ico">
	<link href="//netdna.bootstrapcdn.com/bootstrap/3.0.2/css/bootstrap.min.css" rel="stylesheet">
	<link href="//netdna.bootstrapcdn.com/bootswatch/3.0.1/<?php echo $theme ?>/bootstrap.min.css" rel="stylesheet">
	<link href="//netdna.bootstrapcdn.com/font-awesome/4.0.3/css/font-awesome.min.css" rel="stylesheet">
	<link rel="stylesheet" href="css/styles.css">
</head>
<body>
	<div class="container">
		<div class="row">
			<div class="col-xs-9">
				<h1>
					<?php echo $title ?> <b><?php echo $version ?></b><br>
					<small><i class="fa fa-calendar"></i> <?php echo $today ?></small>
				</h1>				
			</div>
			<div class="col-xs-3">
				<br>
				<form class="form-horizontal">					
					<div class="input-group">
						<select autofocus name="date" id="date" class="form-control input-sm">
						<?php foreach (InOut::getDatesList() as $date): ?>
							<option value="<?php echo $date ?>"<?php echo $date == @$_GET['date'] ? ' selected' : null ?>><?php echo date_format(date_create($date), 'd/m/Y') ?></option>
						<?php endforeach ?>
						</select>
						<div class="input-group-btn">
							<button type="submit" class="btn btn-sm btn-success"><i class="fa fa-check"></i></button>							
						</div>
					</div>
				</form>

				<br>

				<span class="pull-right">
					<a href="https://bitbucket.org/hewerthomn/inout-temperature-v3/src" title="Código do Arduino" class="btn btn-xs btn-<?php echo $btnLink ?>" target="_blank"><i class="fa fa-bitbucket"></i> Código</a>
					<a href="" title="Vídeo do Arduino funcionando" class="btn btn-xs btn-<?php echo $btnLink ?>" target="_blank"><i class="fa fa-vimeo-square"></i> Vídeo</a>
				</span>
			</div>
		</div>
		<hr>

		<div class="well well-sm">
			<div id="chart-temp" class="chart-pie"></div>
		</div>

		<div class="well well-sm">
			<div id="chart-humidity" class="chart-pie"></div>
		</div>

		<div class="well well-sm">
			<div id="chart-dew-point" class="chart-pie"></div>
			<a href="http://pt.wikipedia.org/wiki/Ponto_de_orvalho" class="btn btn-xs btn-<?php echo $btnInfo ?>" target="_blank"><i class="fa fa-info-circle"></i> Ponto de orvalho</a>
		</div>

		<a href="http://hewertho.mn" class="pull-right">hewertho.mn <i class="fa fa-external-link"></i></a>
	</div>
</body>

<script src="http://code.jquery.com/jquery-1.10.1.min.js"></script>
<script src="http://code.jquery.com/jquery-migrate-1.2.1.min.js"></script>
<script src="//netdna.bootstrapcdn.com/bootstrap/3.0.2/js/bootstrap.min.js"></script>
<script type="text/javascript" src="https://www.google.com/jsapi"></script>
<script type="text/javascript" src="js/scripts.js"></script>
<script>(function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){(i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)})(window,document,'script','//www.google-analytics.com/analytics.js','ga');ga('create', 'UA-42715915-1', 'hewertho.mn');ga('send', 'pageview');</script>
</html>
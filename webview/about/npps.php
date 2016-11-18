<?php
$user_agent = $REQUEST_HEADERS['user-agent'] ?? '';
$is_iphone = stripos($user_agent, 'iphone') >= 0;
$is_ipad = stripos($user_agent, 'ipad') >= 0;

$config_list = npps_config();
$global_config = [];
$specific_config = [];

// Hide some config
$config_list['X_MESSAGE_CODE_KEY'] = '(hidden)';

$to_string = function($v): string
{
	if(is_bool($v))
		return $v ? 'true' : 'false';
	else
		return strval($v);
};

// Enum
{
	foreach($config_list as $k => &$v)
	{
		if(strpos($k, 'DBWRAPPER_') !== false)
			$v = '(hidden)';
		
		if(is_array($v))
			$specific_config[$k] = $v;
		else
			$global_config[$k] = $v;
	}
}
?>
<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
		<title>Null-Pointer Private Server</title>
		<link rel="stylesheet" href="/resources/css1.3/bstyle.css">
		<link rel="stylesheet" href="/resources/css1.3/regulation.css">
		<style type="text/css">
			h1 {
				width:900px;
				height:78px;
				line-height:78px;
				font-size:25px;
				font-weight:bold;
				text-align:center;
				color:#fff;
				background:url(/resources/img/help/bg01_01.png)
				no-repeat;
			}
			table
			{
				text-align: left;
				margin: 20px;
				width: 90%
			}
		</style>
<?php
if($is_iphone):
?>
		<meta name="viewport" content="width=880px, minimum-scale=0.45, maximum-scale=0.45" />
<?php
elseif($is_ipad):
?>
		<meta name="viewport" content="width=1024px, minimum-scale=0.9, maximum-scale=0.9" />
<?php
else:
?>
		<meta name="viewport" content="width=880px, user-scalable=no, initial-scale=1, width=device-width" />
<?php
endif;
?>
	</head>
	<body>
		<div class="container">
			<h1>
				Null-Pointer Private Server
			</h1>
			<div id="box1" style="visibility:visible; display:block;">
				<div class="content_regu" style="width:900px;">
					<div class="note">
						<span style="color:black;">
							<div style="text-align: left">
							   <p>
									Null-Pointer Private Server<br/>
									Version <?=MAIN_INVOKED;?><br/>
									Running under <?=$_SERVER['SERVER_SOFTWARE'] ?? $_SERVER['SOFTWARE'];?> with PHP <?=PHP_VERSION;?><br/>
									<br/>
									<br/>
									NPPS Global Settings:<br/>
<?php
foreach($global_config as $k => $v):
?>
									・<?=$k;?> - <?=$to_string($v);?><br/>
<?php
endforeach;
foreach($specific_config as $k => $v):
?>
									<br/>
									NPPS Settings for <?=$k;?>:<br/>
<?php
	foreach($v as $a => $b):
?>
									・<?=$a;?> - <?=$to_string($b);?><br/>
<?php
	endforeach;
endforeach;
?>
								</p>
							</div>
						</span>
					</div>
				</div>
				<div style="width: 960px">
					<img src="/resources/img/help/bg03.png">
				</div>
			</div>
		</div>
	</body>
</html>

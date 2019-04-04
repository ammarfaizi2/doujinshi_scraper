<?php

$a = preg_match_all("/<a href=\"(.*?)\"/", file_get_contents("me.tmp"), $m);

unset($m[0]);

$i = 0;
unset($m[1][0], $m[1][1]);
pcntl_signal(SIGCHLD, SIG_IGN);
foreach ($m[1] as $k => &$v) {
	if (!pcntl_fork()) {
		download("http://servertoenof63yc.onion/freestuff/doujinshi/{$v}");	
		exit;
	}
	$i++;

	if (($i % 16) == 0) {
		$status = null;
		print "Waiting...\n";
		pcntl_wait($status);
	}
}

function download($url)
{
	printf("Downloading %s...\n", $url);
	$ch = curl_init($url);
	curl_setopt_array($ch, 
		[
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_SSL_VERIFYPEER => false,
			CURLOPT_SSL_VERIFYHOST => false,
			CURLOPT_USERAGENT => "Just a bot",
			CURLOPT_PROXY => "127.0.0.1:49060",
			CURLOPT_PROXYTYPE => CURLPROXY_SOCKS5_HOSTNAME
		]
	);
	$out = curl_exec($ch);
	$err = curl_error($ch);
	$ern = curl_errno($ch);

	if ($ern) {
		printf("An error occured: (%d) %s!\n", $ern, $err);
		printf("Retrying to download {$url}...\n");
		$out = curl_exec($ch);
		$err = curl_error($ch);
		$ern = curl_errno($ch);

		if ($ern) {
			printf("An error occured: (%d) %s!\n", $ern, $err);
			printf("Aborted!\n");
			return;
		}
	}

	curl_close($ch);

	is_dir("storage") or mkdir("storage");

	printf("Download success!\n");
	$url = explode("/", $url);
	$url = str_replace(" ", "_", urldecode(end($url)));
	file_put_contents("storage/{$url}", $out);
	unset($out, $url, $err, $ern, $ch);
}

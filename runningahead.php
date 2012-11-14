<?php
$agent = "Mozilla/5.0 (X11; U; Linux i686; en-US; rv:1.9.0.13) Gecko/2009080315 Ubuntu/9.04 (jaunty) Firefox/3.0.13";
$nodes = array('http://www.runningahead.com/scripts/<LOG_ACCESS_KEY>/last', 'http://www.runningahead.com/scripts/<LOG_ACCESS_KEY>/latest');
$node_count = count($nodes);

$curl_arr = array();
$master = curl_multi_init();

$results = array();

for($i = 0; $i < $node_count; $i++)
{
	$url =$nodes[$i];
	$curl_arr[$i] = curl_init($url);
	curl_setopt ($curl_arr[$i], CURLOPT_AUTOREFERER, true); 
	curl_setopt ($curl_arr[$i], CURLOPT_URL, $url);
	curl_setopt ($curl_arr[$i], CURLOPT_USERAGENT, $agent);
	curl_setopt ($curl_arr[$i], CURLOPT_RETURNTRANSFER, 1);
	curl_setopt ($curl_arr[$i], CURLOPT_TIMEOUT, 30);
	curl_multi_add_handle($master, $curl_arr[$i]);
}

do {
    curl_multi_exec($master,$running);
} while($running > 0);

for($i = 0; $i < $node_count; $i++)
{
	$result = curl_multi_getcontent($curl_arr[$i]);
	if ($i == 0) {
		$pattern = '/(?s)<th>Year:<\/th><td>(.*?) mi<\/td>/';
		preg_match($pattern, $result, $matches);
		$results['year'] = $matches[1];
	}
	if ($i == 1) {
		$pattern = '/>([0-9]*?\.[0-9]*?|[0-9]*?) mi/';
		preg_match_all($pattern, $result, $matches);
		foreach($matches[0] as $k=>$v) {
			$results['latest'][$k] = preg_replace('/>| mi/','',$v);
		}
	}
}

echo json_encode($results);

?>

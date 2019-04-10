<?php

include __DIR__."/fcimg.php";

//$rawdata = explode("\n", file_get_contents(__DIR__.DIRECTORY_SEPARATOR.'galdata'));
$file = fopen("galdata", "r");
$rawdata = explode("\n", fread($file, filesize("galdata")));
fclose($file);

$datajsfile = fopen(__DIR__."/data.js", "w");
fwrite($datajsfile, "window.galdata = {}\n");
for ($i = 0; $i < (count($rawdata) -1) / 2; $i ++)
{
	$dataName = $rawdata[$i*2];
	$swfName = $rawdata[$i*2+1];
    $xmlFileName = __DIR__."/XML/$dataName.xml";

    $xmlData = file_get_contents($xmlFileName);
    $chartPos = stripos("<chart", $xmlData);

    if($chartPos !== 0) 
        $xmlData = substr($xmlData, $chartPos);

    if(substr($xmlData, 0, 3) == pack("CCC", 0xef, 0xbb, 0xbf))
    {
        $xmlData = substr($xmlData, 3);
    }
    $xmlData = str_replace("\r\n", "", $xmlData);
    $xmlData = str_replace("\n", "", $xmlData);
    $xmlData = str_replace("\r", "", $xmlData);
	echo ($xmlData);
    fusioncharts_to_image (__DIR__."/out/$dataName.png", $swfName, $xmlData, 400, 560,
        array('debug' => true,
            'licensed_fusioncharts_js' => __DIR__."/vendor/js/fusioncharts.js",
            'licensed_fusioncharts_charts_js' => __DIR__."/vendor/js/fusioncharts.charts.js")
        );
    
    $escaped_xml = addslashes($xmlData);
    $entry = <<<DATAENTRY

galdata['$dataName'] = {
    'swfName':"$swfName",
    'data': "$escaped_xml"
};

DATAENTRY;


    fputs($datajsfile, $entry);
}

fclose($datajsfile);

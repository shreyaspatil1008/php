<?php

define("_FC_IMG_PLATFORM_LINUX", "/usr/bin/wkhtmltoimage");
define("_FC_IMG_PLATFORM_LINUX_64", "/usr/bin/wkhtmltoimage");
define("_FC_IMG_PLATFORM_WINDOWS", "wkhtmltoimage.exe");
define("_FC_IMG_PLATFORM_OSX", "/usr/local/bin/wkhtmltoimage");

class FCImageException extends Exception
{

}

/**
 * @param string $outputFilePath The <b>Full</b> path to the output image. (This <b>WILL BE OVERWRITTEN</b> if exists)
 *                               Please note: PHP should have write permissions jin this
 * @param string $inputString The JSON/XML String of the entire chart object. This is what you will pass to FusionCharts.render ()
 * @param string $swfName The FusionCharts SWF File Path
 * @param array $options A set of options:
 * options ('imageType' => 'png' or 'jpg',
 *          'width' => width in pixels
 *          'height' => height in pixels
 *          'nodelay' => Don't wait for animation to complete (this will speed up script but might cause it to not work)
 *          'quality' => quality from 1 to 100
 *          'wkhtmltoimage_path' => alternative wkhtmltoimage_path
 *          'licensed_fusioncharts_js' => Path to licensed fusioncharts.js
 *          'licensed_fusioncharts_charts_js' => Path to licensed fusioncharts.charts.js
 * )
 * @throws FCImageException
 * @return bool True if success, false if failure. TODO: Log stderr so users can properly submit bug reports.
 */
function fusioncharts_to_image($outputFilePath, $swfName, $inputString, $height, $width, $options = array())
{

    $jsonFlag = false;
    if($inputString[0] === '<') // Check for the first character
    {
        // do nothing. jsonFlag is set to false anyways
    }
    else if($inputString[0] === "{")
    {
        $jsonFlag = true;
    }
    else
    {
        throw new FCImageException("The input string doesn't seem to be either JSON or XML");
    }

    $fileType = "png";
    if(isset($options['imageType']))
        $fileType = $options['imageType'];
    
    $renderDelay = 1000;
    if (isset($options['render_delay']) && is_numeric($options['render_delay']))
        $renderDelay = $options['render_delay'];

    /*
     * Note: sys_get_temp_dir returns /tmp on Linux (don't know about osx)
     * but on the other hand, it returns C:\user\foo\Local\Temp\ on Windows
     *
     * so we need to add a directory separator on Linux but not on windows
     */
    $separator = "";
    if(DIRECTORY_SEPARATOR === "/") // check if unix system. TODO: There will be better ways to do this
        $separator = DIRECTORY_SEPARATOR;
	
    $imageFileName = __DIR__."/temp/FCImage".'.'.$fileType;

    $cwd = __DIR__; // change working directory to the current script's directory
    $env = array(); // set any environment variables here

    // configure the arguments
    $args = "--format $fileType";

    $args = $args." --crop-w ".($width - 1);
    $args = $args." --crop-h ".($height - 1);

    if(isset($options['nodelay']))
    {
        if($options['nodelay'] === true)
            $args = $args." --javascript-delay 0";
    }
    else
    {
        $args = $args." --javascript-delay {$renderDelay}";
    }



    if(isset($options['quality']))
    {
        $args = $args." --quality {$options['quality']}";
    }

    $debugFlag = false;
    if(isset($options['debug']))
    {
        $debugFlag = true;
	$debugFile = fopen(__DIR__."/debug.html", "w");

	//echo "\n\nCall to:\n fusioncharts_to_image ($outputFilePath, $swfName, [removing input string], $height, $width)";
    }

    // now, determine the platform this is running on
    $os = php_uname("s");
    $arch = php_uname("m");
    if($os==="Windows NT")
        $platform = _FC_IMG_PLATFORM_WINDOWS;
    else if($os === "Linux")
    {
        if($arch === "i386")
            $platform = _FC_IMG_PLATFORM_LINUX;
	    else if($arch === "i686")
            $platform = _FC_IMG_PLATFORM_LINUX;
        else if ($arch === "x86_64")
            $platform = _FC_IMG_PLATFORM_LINUX_64;
        else
            throw new FCImageException ("This Linux architecture is not supported");
    }
    else if($os === "Darwin") {
        $platform = _FC_IMG_PLATFORM_OSX;
    }
    else
        throw new FCImageException("Your server OS is currently not supported");

    $fcRoot = dirname(__FILE__);

    $wkCommand = $platform;
	
    if(isset($options['wkhtmltoimage_path'])) {
        $wkCommand = $options['wkhtmltoimage_path'];
    }
	
    $command = "$wkCommand $args - $imageFileName";
    if($debugFlag)
    {
	    echo "\n Command: $command";
    }



	$wkstdin = popen($command, "w");

    if(!is_resource($wkstdin))
    {
        throw new FCImageException("An error took place while trying to open wkhtmltopdf");
    }

    $templateHead = <<<TOP
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <title></title>
        <style>
            body{
                padding:0 0 0 0;
                margin:0 0 0 0;
            }
        </style>
        <script>
TOP;
    // ok. write template.txt into the process stdin
    fwrite($wkstdin, $templateHead);

    if($debugFlag)
    {
        fwrite($debugFile, $templateHead);
    }

    if(isset($options['licensed_fusioncharts_charts_js']) && isset($options['licensed_fusioncharts_js'])) {

        fwrite($wkstdin, file_get_contents($options['licensed_fusioncharts_js']));

        if($debugFlag)
        {
            fwrite($debugFile, file_get_contents($options['licensed_fusioncharts_js']));
        }

        fwrite($wkstdin, file_get_contents($options['licensed_fusioncharts_charts_js']));

        if($debugFlag)
        {
            fwrite($debugFile, file_get_contents($options['licensed_fusioncharts_charts_js']));
        }
    }
    else {
        throw new FCImageException("Need to provide fusioncharts licensed version here");
    }


    $functionToCall = "setXMLData";
    if($jsonFlag === true)
        $functionToCall = "setJSONData";

    // replace all EOL with ""
    $escapedData = str_replace("\n", "", $inputString);
    $escapedData = addslashes($escapedData);

    $templateCode = <<<BOTTOM
</script>
</head>
<body>
<div id="chartContainer"><small>Loading chart...</small></div>
</body>
<script>
FusionCharts.setCurrentRenderer('javascript');
FusionCharts.ready(function () {
    var yearlyData = {
        "lastyear": {
            "chart": {
                "caption": "Split of Revenue by Product Categories",
                    "subCaption": "Last year",
                    "numberPrefix": "$",
                    "theme": "fint",
                    "captionFontSize": "13",
                    "subcaptionFontSize": "12",
                    "subcaptionFontBold": "0"
            },
                "data": [{
                "label": "Food",
                    "value": "28504"
            }, {
                "label": "Apparels",
                    "value": "14633"
            }, {
                "label": "Electronics",
                    "value": "10507"
            }, {
                "label": "Household",
                    "value": "4910"
            }]
        },
            "thisyear": {
            "chart": {
                "caption": "Split of Revenue by Product Categories",
                    "subCaption": "This year",
                    "numberPrefix": "$",
                    "theme": "fint",
                    "captionFontSize": "13",
                    "subcaptionFontSize": "12",
                    "subcaptionFontBold": "0"
            },
                "data": [{
                "label": "Food",
                    "value": "5000"
            }, {
                "label": "Apparels",
                    "value": "14633"
            }, {
                "label": "Electronics",
                    "value": "1000"
            }, {
                "label": "Household",
                    "value": "20000"
            }]
        }
    };
    var revenueChart = new FusionCharts({
        type: 'doughnut2d',
        renderAt: 'chartContainer',
        width: '500',
        height: '300',
        dataFormat: 'json',
        dataSource: yearlyData.lastyear
    }).render();});
</script>
</html>
BOTTOM;
    fwrite($wkstdin, $templateCode);
    if($debugFlag)
    {
        fwrite($debugFile, $templateCode);
    }

    $returnCode = pclose($wkstdin);

    if($returnCode !== 0)
    {
        if(file_exists($imageFileName))
            unlink($imageFileName);
        throw new FCImageException("There was an error with wkhtmltopdf");
    }

    // success!
    rename($imageFileName, $outputFilePath);

    return true;
}

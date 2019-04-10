<?php

include __DIR__."/fcimg.php";
$inputString ="{
    'chart': {
        'caption': 'Monthly revenue for last year',
        'subCaption': 'Harry SuperMart',
        'xAxisName': 'Month',
        'yAxisName': 'Revenues (In USD)',
        'numberPrefix': '$',
        'theme': 'fusion'
    },
    'data': [
        {
            'label': 'Jan',
            'value': '420000'
        },
        {
            'label': 'Feb',
            'value': '810000'
        },
        {
            'label': 'Mar',
            'value': '720000'
        },
        {
            'label': 'Apr',
            'value': '550000'
        },
        {
            'label': 'May',
            'value': '910000'
        },
        {
            'label': 'Jun',
            'value': '510000'
        },
        {
            'label': 'Jul',
            'value': '680000'
        },
        {
            'label': 'Aug',
            'value': '620000'
        },
        {
            'label': 'Sep',
            'value': '610000'
        },
        {
            'label': 'Oct',
            'value': '490000'
        },
        {
            'label': 'Nov',
            'value': '900000'
        },
        {
            'label': 'Dec',
            'value': '730000'
        }
    ],
    'trendlines': [
        {
            'line': [
                {
                    'startvalue': '700000',
                    'valueOnRight': '1',
                    'displayvalue': 'Monthly Target'
                }
            ]
        }
    ]
}";
fusioncharts_to_image (
    __DIR__."/out/fcimg.png",           // path to image
    "",                  // SWF Name. SWF File not required
    $inputString,                    // the input XML String
    400, 500,                        // height and width
    array(    'debug' => true,                       // options
        'licensed_fusioncharts_js' => __DIR__."/vendor/js/fusioncharts.js", // REQUIRED: Path to licensed fusioncharts.js
        'licensed_fusioncharts_charts_js' => __DIR__."/vendor/js/fusioncharts.charts.js", // REQUIRED: Path to licensed fusioncharts.charts.js
        'imageType' => 'png',        // OPTIONAL: set image type as JPG
        'quality' => 75  ,            // OPTIONAL: increase Quality
        'render_delay' => 2000 ,      // OPTIONAL: increase render delay
        'wkhtmltoimage_path' => "C:/Program Files/wkhtmltopdf/bin/wkhtmltoimage.exe" // C:\Program Files\wkhtmltopdf\bin
    )
);

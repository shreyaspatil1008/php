var page = require('webpage').create(),
// webpageURLWithChart  = 'http://docs.fusioncharts.com/charts/Code/MyFirstChart/ms-weekly-sales-no-animation.html',
webpageURLWithChart  = 'PcrChart.html',
outputImageFileName = 'savedImage.png',
delay = 8000;
 
page.open(webpageURLWithChart, function () {
window.setTimeout(function () {
 
page.render(outputImageFileName);
phantom.exit();
 
}, delay);
 
});
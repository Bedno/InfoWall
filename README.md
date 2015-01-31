At-a-glance Info Wall display for personal use.  Shows time/date, weather/forecast/radar, webcams and more.

Designed for constantly on picoprojector, or any minimal laptop or tablet.
Works best in Chrome; press F11 for Full Screen mode.
Automatically adjusts all elements to window size and rotation to maximize readability.
Weather forecast uses "Wunderground" API.  Speaks the forecast on capable browsers.

Demonstrates a number of advanced Javascript techniques:Canvas element and drawing.  Analog clock. Text to speech.  JSON fetch and parse.  Weather API use.  Structured array constant. Querystring reading.  Use of localstorage.  Responsive auto-formatting.  Keypress handling.  Sun/Moon calculations.  Centralized update handler using timer.

USAGE: Simply open the index.htm preferably in Chrome or Safari.  Tap the date for next background.  Tap the forecast to speak it.  Tap other links (background name, radar, conditions...) for offsite details in new window.  Include ?n={code} param in URL to start with a particlar background: cam, wrig, art, zoom, radar, cats.  Some backgrounds don't show radar, or show compact forecast.  Art shows an analog clock.  Currently coded for Chicago, but programmer configurable in vars at top of script documented in comments.

NEXT PLANNED ENHANCEMENTS:
1. Add support for NWS (National Weather Service) forecasts (as alternative to Wunderground), using their XML API: http://graphical.weather.gov/xml/
2. Internally animate radar frames (using visibility) and only load new frame every few minutes, rather than constantly reloading the radar image, to reduce  network activity.
  
Incorporates SunCalc.js by Vladimir Agafonkin https://github.com/mourner/suncalc

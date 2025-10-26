<?
// Supporting program for InfoWall project.  https://bedno.com/infowall/about
// Run from command line as: php -f nwslookup.php
// Returns URLs for National Weather Service RADAR, and forecast and observations json files, given GPS coordinates.
// Fetches all files to local for review.
// The provides URLs for updating them in the Javascript code used in InfoWall (bedno.com/infowall/about) for weather data fetchs, to simplify that program.

// UPDATE KEY VALUES HERE
global $Weather_Zone;  $Weather_Zone = 'Portland';
global $Weather_Lat;  $Weather_Lat = 45.49067;
global $Weather_Lon;  $Weather_Lon = -122.629134;

// Returned values.
global $Weather_Radar;  $Weather_Radar = "";
global $Weather_Current;  $Weather_Current = "";
global $Weather_Forecast;  $Weather_Forecast = "";
global $Weather_URL;  $Weather_URL = "";

// Log message.  
date_default_timezone_set('America/Los_Angeles');
function WriteLog ($WL_in) {
  $server_time = time();
  $YYYYMMDD = date("Ymd", $server_time);  $HHMMSS = date("His", $server_time);  $YYYYMMDDHHMMSS = $YYYYMMDD.$HHMMSS;
  $WriteLog_filename = 'nwslookup.log';
  $WriteLog_handle = @fopen($WriteLog_filename, 'a');
  if ($WriteLog_handle) {
    $fetchlogresults = @fwrite($WriteLog_handle,$YYYYMMDD.'|'.$HHMMSS.'|'.$Weather_Zone.'|'.$WL_in."|\n");
    @fclose($WriteLog_handle);
  }
}

// Fetch a binary file from URL to local.
function RemoteFetchFile ($RFF_URL, $RFF_filename, $RFF_title, $RFF_minsize) {
  $RFF_Out = "";
  // Define the stream context options, required by NWS
  $options = [ 'http' => [ 'header' => "User-Agent: Bedno.com, andrew@bedno.com\r\n", ], ];
  $context = stream_context_create($options);
  $RFF_fp = @fopen($RFF_URL, "rb", false, $context);
  if ($RFF_fp) {
    while (!feof($RFF_fp)) {
      $RFF_chunk = fread($RFF_fp, 20000000);
      $RFF_Out .= $RFF_chunk;
    }
    @fclose($RFF_fp);
    if (strlen($RFF_Out) >= 10) {
      if ($RFF_filename) {
        if (strlen($RFF_Out)>=$RFF_minsize) {
          $cachefp = @fopen($RFF_filename,"wb");
          @fwrite($cachefp,$RFF_Out);
          @fclose($cachefp);
          WriteLog('Fetch '.$RFF_filename.':saved('.strlen($RFF_Out).')');
        }
      }
    } else {
      WriteLog('Fetch '.$RFF_URL.':empty');
    }
  } else {
    WriteLog('Fetch '.$RFF_URL.':failed');
  }
  return($RFF_Out);
}

// Return contents of text file.
function LoadFreshText ($IF_fname) {
  $IF_out = "";
  if (@file_exists($IF_fname)) {
    $ins_fd = @fopen($IF_fname, 'r');
    if ($ins_fd) {
      while (! feof($ins_fd)) {
        $IF_out .= fgets($ins_fd, 2000000);
      }
      @fclose($ins_fd);
    }
    WriteLog('Load '.$IF_fname.':saved('.strlen($IF_out).')');
  } else {
    WriteLog('Load '.$IF_fname.' failed');
  }
  return($IF_out);
}

// NWS first step to convert GPS to observation index, forecast grid, and radar station.
function FetchPoints () {
  global $Weather_Zone, $Weather_Lat, $Weather_Lon, $Weather_Radar, $Weather_Current, $Weather_Forecast, $Weather_URL;
  $NWS_points_url = "https://api.weather.gov/points/".$Weather_Lat.",".$Weather_Lon;
  RemoteFetchFile($NWS_points_url, $Weather_Zone.'-p.json', "points", 1);
  $weather_points_data = LoadFreshText($Weather_Zone.'-p.json');
  $PointsRec = json_decode($weather_points_data,true);
  // print_r($PointsRec);
  $Weather_Radar = $PointsRec['properties']['radarStation'];
  WriteLog('radar='.$Weather_Radar);
  $Weather_Forecast = $PointsRec['properties']['forecast'];
  WriteLog('forecast='.$Weather_Forecast);
  WriteLog('gridId='.$PointsRec['properties']['gridId']);
  $Weather_URL = 'https://www.weather.gov/'.strtolower($PointsRec['properties']['gridId']);
}

function FetchWeather () {
  global $Weather_Zone, $Weather_Lat, $Weather_Lon, $Weather_Radar, $Weather_Current, $Weather_Forecast, $Weather_URL;
  // Radar
  if ( (isset($Weather_Radar)) && (! empty($Weather_Radar)) ) {
    $Weather_Radar_URL = 'https://radar.weather.gov/ridge/standard/'.$Weather_Radar.'_loop.gif';
    RemoteFetchFile($Weather_Radar_URL, $Weather_Zone.'-r.gif', 'radar', 4000 );
    WriteLog('RadarURL='.$Weather_Radar_URL);
  }
  // Create stations URL from Forecast URL.  Fetch the stations json.
  WriteLog('stations='.str_replace('forecast','stations',$Weather_Forecast));
  RemoteFetchFile(str_replace('forecast','stations',$Weather_Forecast), $Weather_Zone.'-x.json', "stations", 1);
  $weather_stations_data = LoadFreshText($Weather_Zone.'-x.json');
  $FWSrec = json_decode($weather_stations_data,true);
  // Use first station to create observations url.  Fetch that station's obserevations json.
  // print_r($FWSrec);
  if (isset($FWSrec["features"][0]['properties']['stationIdentifier'])) {
    $Weather_Current = 'https://api.weather.gov/stations/'.$FWSrec["features"][0]['properties']['stationIdentifier'].'/observations';
    WriteLog('current='.$Weather_Current);
    RemoteFetchFile($Weather_Current, $Weather_Zone.'-o.json', "observations", 1);
    $weather_observations_data = LoadFreshText($Weather_Zone.'-o.json');
    // Parse current conditions.
    $FWOrec = json_decode($weather_observations_data,true);
    // print_r($FWOrec);
  }
  // Fetch forecast.
  if ( (isset($Weather_Forecast)) && (! empty($Weather_Forecast)) ) {
    RemoteFetchFile($Weather_Forecast, $Weather_Zone.'-f.json', 'forecast', 1024 );
    $weather_more_data = LoadFreshText($Weather_Zone.'-f.json');
    $FWFrec = json_decode($weather_more_data,true);
    // print_r($FWFrec);
  }
}

FetchPoints();
FetchWeather();
print("Processing... ");
print("Code inserts for InfoWall use:\n\n");
print("var WeatherCurrentURL = '".$Weather_Current."';\n");
print("var WeatherForecastURL = '".$Weather_Forecast."';\n");
print("var WeatherRadarStation = '".$Weather_Radar."';\n");
print("var WeatherURL= '".$Weather_URL."';\n");

?>

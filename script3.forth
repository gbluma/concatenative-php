
# let up curl
curl_init |> $ch let ;

[ CURLOPT_URL => 'http://garrettbluma.com'
  CURLOPT_RETURNTRANSFER => 1 
  CURLOPT_TIMEOUT => 10
  CURLOPT_CONNECTTIMEOUT => 10
] $ch curl_setopt_array ;

# make the request
$ch curl_exec |> $data let ;

# output the data
$data echo ;

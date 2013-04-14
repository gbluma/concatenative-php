# This is a comment

"forth.php" require_once ;

"Hello world" Prelude::println ;
# => "Hello world"

[ 'rtf' => 'Rich Text Format'
  'doc' => 'Microsoft Document Format'
  'xml' => 'Extensible Markup Language'
] 'rtf' Prelude::cond ;
# => 'Rich Text Format'
{ $x } $foo function ;

[ 'x' => 30 ] $foo ;

{ $x } lambda ;

Document class ;

[ 'title'  => 'An introduction to php-forth' 
  'author' => 'Garrett Bluma'
  'hiFive' => [ { 'Hi Five' echo ; } lambda ; ]
] Document $MyDocument new ;

$MyDocument->title echo ;
# => 'An introduction to php-forth'

$MyDocument->author echo ;
# => 'Garrett Bluma'

[ ] $MyDocument->hiFive ;
# => 'Hi Five'

5 $five set ;

$five echo ;
# => 5
[ 1 2 3 4 5 ] $numbers set ;

[ 'x' => 9
  'y' => 10
  'z' => 11 ] $myArray set ;

# assignment
9 $nine set ;

# single argument-functions
"abcdef" strlen ;
# => 6

# multi-argument functions
"the quick, brown, fox jumped" " " "," str_replace ;
# => "The quick  brown  fox jumped"

# inline substitution
"Here is a substitution: $nine" Prelude::println ;
# => 'Here's substitution: 9'

# concatenation
[ 'hello ' 'there' 'world' ] . ;   

[ [ [ "http://garrettbluma.com" file_get_contents ; ] strlen ; ] strlen ; ] Prelude::println ;
# => 5

"http://garrettbluma.com" file_get_contents 
  |> strlen 
  |> strlen 
  |> Prelude::println ;
# => 5

# set up curl
curl_init |> $ch set ;

[ CURLOPT_URL => 'http://google.com'
  CURLOPT_RETURNTRANSFER => 1 
  CURLOPT_TIMEOUT => 10
  CURLOPT_CONNECTTIMEOUT => 10
] $ch curl_setopt_array ;

# make the request
$ch curl_exec |> $data set ;

# output the data
$data echo ;

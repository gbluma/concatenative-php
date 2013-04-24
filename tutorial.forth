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
  'hiFive' => [ { 'Hi Five' echo ; } ] 
] Document $MyDocument new ;

$MyDocument->title echo ;
# => 'An introduction to php-forth'
 
$MyDocument->author echo ;
# => 'Garrett Bluma'
 
$MyDocument->hiFive ;
# => 'Hi Five'
 
5 $five let ;
 
$five echo ;
# => 5
[ 1 2 3 4 5 ] $numbers let ;

[ 'x' => 9
  'y' => 10
  'z' => 11 ] $myArray let ;

# assignment
9 $nine let ;

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

"http://garrettbluma.com" file_get_contents 
  |> strlen 
  |> strlen 
  |> Prelude::println ;
# => 5

# let up curl
curl_init |> $ch let ;

[ CURLOPT_URL => 'http://google.com'
  CURLOPT_RETURNTRANSFER => 1 
  CURLOPT_TIMEOUT => 10
  CURLOPT_CONNECTTIMEOUT => 10
] $ch curl_setopt_array ;

# make the request
$ch curl_exec |> $data let ;

# output the data
$data echo ;

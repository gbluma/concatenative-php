# This is a comment

"forth.php" require_once ;

"Hello world" Prelude::println ;
# => "Hello world"

5 $a set ;

[ '5' => [ { $x 5 * ; } lambda ; ]
  '6' => [ { $x 10 * ; } lambda ; ]
] $a Prelude::cond |> $timesA set ;

[ 'x' => 500 ] $timesA |> Prelude::println ;
# => 25
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


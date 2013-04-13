
# Here's a comment

"prelude.php" require_once ;

# create a new class called Document
Document class ;

# creates a Document with two members
[ 'foo' => 'foo_value' 
  'bar' => 'bar_value'
  'baz' => [ [ { $x echo ; } 
               { $x echo ; } ] lambda ; ] 
] Document $MyDocument new ;

# prints two lines
[ $MyDocument->foo $MyDocument->bar ] 
    Prelude::println ;

[ 'x' => 'b' ] 
    $MyDocument->baz ;

# expands to variables 'a' and 'b'
[ 'a' => [ 'hello' 'there' ]
  'b' => 'bingo' ] extract ;

[ 'hello ' "$b" 'abc' ] . ; 

# function syntax:
# $foo2 = function($x) { return $x; }
{ $x return ; } $foo2 function ;

[ [ 'x' => 314159 ] $foo2 ; ] echo ;

# define an orphaned lambda
[ $x ] lambda |> $test set ;
[ 'x' => 'HELLO' ] $test |> $test2 set ;
$test2 Prelude::println ;

# This works, but has nested braces
# [ [ [ "http://garrettbluma.com" file_get_contents ; ] strlen ; ] strlen ; ] Prelude::println ;

# This is the same without nested braces
"http://garrettbluma.com" file_get_contents 
  |> strlen 
  |> echo
  |> lambda 
  |> $getData set ;

# not evaluated until here
[ ] $getData ;


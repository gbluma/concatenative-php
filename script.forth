
# Here's a comment

# create a new class called Document
Document class ;

# creates a Document with two members
[ 'foo' => 'foo_value' 
  'bar' => 'bar_value'
  'baz' => [ { $x echo ; } lambda ; ] 
] Document $MyDocument new ;

# prints two lines
[ $MyDocument->foo $MyDocument->bar ] 
    Prelude::println ;

[ 'x' => 'b' ] 
    $MyDocument->baz ;

# expands to variables 'a' and 'b'
[ 'a' => [ 'hello' 'there' ]
  'b' => 'bingo' ] extract ;

[ 'hello ' "$b" 'abc' ] . ; echo ;

# function syntax:
# $foo2 = function($x) { return $x; }
#{ [ $x echo ; ] [ $x return ; ] } $foo2 function ;

#[ 'a' echo ; 'b' echo ; ] 

#[ [ 'x' => 5 ] $foo2 ; ] echo ;

# define an orphaned lambda
# [ $x return ; ] lambda ;

# variable assignment, $c = 'b'
#file_get_contents('http://garrettbluma.com') $c set ;
#$c Prelude::println ;



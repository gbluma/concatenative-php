
# Here's a comment

# create a new class called Document
Document class ;

# creates a Document with two members
[ 'foo' => 'foo_value'
  'bar' => 'bar_value' ]
    Document $MyDocument new ;

# prints two lines
[ $MyDocument->foo $MyDocument->bar ]
    Prelude::println ;

# expands to variables 'a' and 'b'
[ 'a' => 'hello' 
  'b' => 'there' ] extract ;

# variable assignment, $c = 'b'
file_get_contents('http://garrettbluma.com') $c set ;
$c Prelude::println ;



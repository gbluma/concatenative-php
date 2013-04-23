
{ [ 'defg' strlen ; echo ; " grills on the lawn\n" echo ; ]
  [ 'abc' strlen ; echo ; " steaks grilling\n" echo ; ]
  [ $a echo ; " cobs of corn\n" echo ; ]
} $foo set ;

{ [ 'a' echo ; ] 
  [ { 5 5 * ; } ] 
} $foo2 set ;

[ 'a' => 5 ] $foo ;

5 $a set ;
$a echo ;




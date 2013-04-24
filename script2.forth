
{ [ 'defg' strlen ; echo ; " grills on the lawn\n" echo ; ]
  [ 'abc' strlen ; echo ; " steaks grilling\n" echo ; ]
  [ $a echo ; " cobs of corn\n" echo ; ]
} $foo let ;

{ [ 'a' echo ; ] 
  [ { 5 5 * ; } ] 
} $foo2 let ;

[ 'a' => 5 ] $foo ;

5 $a let ;
$a echo ;




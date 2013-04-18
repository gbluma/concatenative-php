
{  [ 'defg' strlen ; ]
   [ 'abc' strlen ; $b set ; ] 
   [ $b 2 * ; ]
} $foo function ;

$foo |> var_dump ;

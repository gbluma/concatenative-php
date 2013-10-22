
This document will attempt to explain all of the language features.

Comments
------------------

First, comments are prefixed with a hash character `#` and are closed by an end of line
character. This is very similar to the `//` row-level comments in PHP.

    # This is a comment
    

Statements are laid out in postfix notation. That is, in PHP the tokens are parsed left to
right and the function call comes first (i.e. `foo(x,y)` ). Lisp is similar to this, but the
parenthesis comes before the function name and the parameters are delimited by spaces (i.e.
`(foo x y)` ).  This language also delimits with spaces (same as Lisp) but switches the order
from right to left and removes the requirement for parentheses too.  (i.e. `y x foo`).

Calling functions
-----------------

Thus, we can load PHP files using the following syntax. 

   "language.php" require_once ;

This is equivalent to the PHP expression `require_once("language.php");`. Note, the semi-colon
exists to *evaluate* the current stack, which we'll get to later. For now, just remember that
nothing good will happen if it is left out.

We then have access to the functions in that library.

    "Hello world" Prelude::println ;
    # => "Hello world"
    

Conditional operations
----------------------

    { 'rtf' => 'Rich Text Format'
      'doc' => 'Microsoft Document Format'
      'xml' => 'Extensible Markup Language'
    } 'rtf' Prelude::cond ;
    # => 'Rich Text Format'
    

Defining functions
------------------

Moving on to functions, we can define these with the following syntax.

    [ $x ] $foo function ;
    

And call them with the folowing syntax:

    { 'x' => 30 } $foo ;
    

There are some odd bits here, so take a second and catch them. 

* The definition syntax is `{ <body> } <name> function ;`. If you already have a body on the
  stack, you
  can give it a name and it's suddently a function.

* Parameters are implicitly passed. An associative array can be passed to assign values to 
  local variables. In the case above, when `$foo` is called, it assigns `$x` to be `30` 
  returns `30`.

* Returns statements are implicit as well. A function will automatically return any results
  of what is on the stack after some computation. In this case, `$x`.

* `$foo` is a function identifier. In order to get first class functions these need to be
  variables, and because the underlying translation is very light-weight the names can't be
  substituted. Sorry. I'd like to get rid of those dollar signs too...

We can also define anonymous functions using the `lambda` keyword.

    [ $x ] lambda ;
    

The only difference here is that the function does not need a name. Behind the scenes both
`function` and `lambda` use closures in PHP. This is for convenience in working with the
stack.

Objects
-----------------

This language assumes a prototype based OOP model. This isn't because it is *better* than the
traditional model, just because it provides simpler back-end semantics. To define a new class,
`Document`, we can do the following.

    Document class ;
    

This extends the basic `Prototype` object and lets the rest of the runtime be aware of the
`Document` class. To instantiate the new object we can do the following.

    { 'title'  => 'An introduction to Concatenative PHP' 
      'author' => 'Garrett Bluma'
      'hiFive' => { [ 'Hi Five' echo ; ] }
    } Document $MyDocument new ;
    

Here we define a couple properties (*title* and *author*) while also defining a member
function on our object (*hiFive*). We tell the system that we want it to be of the *Document*
class, we name it `$MyDocument` and then kick off the instantiation process with `new`.

To test things, we can try the following:

    $MyDocument->title echo ;
    # => 'An introduction to Concatenative PHP'
    
    $MyDocument->author echo ;
    # => 'Garrett Bluma'
    
    $MyDocument->hiFive ;
    # => 'Hi Five'
    

Variables
---------

Variables can be defined using the `let` function. 

    5 $five let ;
    
    $five echo ;
    # => 5

We can assign simple types, or complex types like arrays.

    { 1 2 3 4 5 } $numbers let ;
    
    { 'x' => 9
      'y' => 10
      'z' => 11 } $myArray let ;
    

Oh, and that's the syntax for arrays right there. No commas needed. Associate arrays still use
the same `$key => $value` syntax as PHP proper.


The PHP API
-----------

Because this language is just a translation process, any function that exists in PHP can
potentially be used, along with string substitution, etc. Your mileage may vary on the ease of
these things, however theoretically they're all here.

All of the following work:

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
    { 'hello ' 'there' 'world' } . ;   
    

Nested expressions work. In the following we take the content of web-page, take
the length of it (11193 characters), take the length of the length
(`str_len('11193') = 5`) and output that value to the screen.  The `|>`
operator should be familiar if you've used F#. It evaluates the stack, and
wraps the result in an array for the next function. 

    "http://garrettbluma.com" file_get_contents 
      |> strlen 
      |> strlen 
      |> Prelude::println ;
    # => 5
    

CURL example
------------

The following example uses the CURL module to connect to google and downlaod a web page.

    # set up curl
    curl_init |> $ch let ;
    
    { CURLOPT_URL => 'http://google.com'
      CURLOPT_RETURNTRANSFER => 1 
      CURLOPT_TIMEOUT => 10
      CURLOPT_CONNECTTIMEOUT => 10
    } $ch curl_setopt_array ;
    
    # make the request
    $ch curl_exec |> $data let ;
    
    # output the data
    $data echo ;








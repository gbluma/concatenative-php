
test:
	literate -s tutorial.lforth > tutorial.forth
	literate -d tutorial.lforth > tutorial.md
	php forth.php tutorial.forth




test:
	literate -s tutorial.lcphp > tutorial.cphp
	literate -d tutorial.lcphp > tutorial.md
	php language.php tutorial.cphp
	mv -f tutorial.md README.md




test:
	#php language.php tutorial.cphp
	phpunit tests

docs:
	literate -s tutorial.lcphp > tutorial.cphp
	literate -d tutorial.lcphp > tutorial.md
	mv -f tutorial.md README.md


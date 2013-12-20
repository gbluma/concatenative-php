
test:
	#php language.php tutorial.cphp
	php ./vendor/phpunit/phpunit/phpunit.php --coverage-text=/tmp/coverage.txt tests
	@cat /tmp/coverage.txt
	@rm /tmp/coverage.txt
	@echo "\n"

docs:
	literate -s tutorial.lcphp > tutorial.cphp
	literate -d tutorial.lcphp > tutorial.md
	mv -f tutorial.md README.md


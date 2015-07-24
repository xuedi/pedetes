# pedetes
Lightweight PHP library to kickstart multilanguage projects

## how to set up

Usually, the pedetes folder is somewhere else than the public http docs. I do use /var/www/libs/pedetes and then have the web docs at /var/www/sites/newSite but that is just personal preference. there will be an blank site example that does use the lib later.

 - curl -sS https://getcomposer.org/installer | php
 - ./composer.phar update

## privacy warning

The caching option uses APCu user cache, if you run on shared hosting, either disable caching or make sure each page has its private APCu caching pool (via own CGI instance). To clear a pages cache, just add '~FC' at the end of the url.
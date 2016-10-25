# pedetes
Minimal PHP lib & website

## how to set up
This install expects that you have everything in a website folder like /var/www/sites/newSite the webserver will point to /var/www/sites/newSite/www/web/

Of course every path can be adjusted like you with when you install manualy, for that see the install.sh as install guideline

### requirements
 - Linux / Unix
 - PHP 7+
 - Nginx / Apache
 - bower (or manual assets)

### webhost (nginx)
	upstream php {
		server unix:/tmp/php-cgi.socket;
		server 127.0.0.1:9000;
	}

	server {
		server_name domain.tld;
		root /var/www/sites/newSite/www/web/;
		index index.php;
		location / {
			try_files $uri $uri/ /index.php?$args;
		}

		location ~ \.php$ {
			include fastcgi.conf;
			fastcgi_intercept_errors on;
			fastcgi_pass php;
		}
	}

### automatic install (bash)
	cd /var/www/sites/newSite/
	curl -sS https://raw.githubusercontent.com/xuedi/pedetes-www/master/install.sh | bash
	# btw, never trust a forign remote scipt ...


## privacy warning
The caching option uses APCu user cache, if you run on shared hosting, either disable caching or make sure each page has its private APCu caching pool (via own CGI instance). To clear a pages cache, just add '~FC' at the end of the url.
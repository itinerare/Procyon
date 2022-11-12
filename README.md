# Procyon

Procyon is a simple Laravel-based project for generating daily digests for configured RSS feeds. It then generates its own feed containing all the digests it generates, allowing you to get daily summaries of feeds' contents as one update per feed per day rather than potentially many, while still enjoying the benefits of RSS. It's worth noting that it employs minimal styling, allowing whatever settings you have for your feed reader to do their work (operating under the assumption that you have these configured as best suits your needs).

Digests contain only the items since the last digest (or, if there is no extant digest for a feed, since the day prior.) And then, of course, your feed reader should be able to take it from there.

- Support Discord: https://discord.gg/mVqUzgQXMd

## Setup

### Obtain a copy of the code

```
$ git clone https://github.com/itinerare/procyon.git
```

### Configure .env in the directory

```
$ cp .env.example .env
```

This should largely not need adjustments, but you should set a password for the web UI if you intend to use it and your Procyon instance is public/web-accessible.

### Setting up

Install packages with composer:
```
$ composer install
```

Generate app key, create the database, and run migrations:
```
$ php artisan key:generate
$ touch database/database.sqlite
$ php artisan migrate
```

Add feeds to `config/procyon-settings.php` per the instructions in that file. Optionally, configure your time zone in `config/app.php` (see [here](https://www.php.net/manual/en/timezones.php) for a list of PHP-supported time zones).

By default, only the date and title/summary of each item are added to the digest; to include the full text of each item, change the `summary-only` value in `config/procyon-settings.php` to `false`. 

Optionally, there is a web UI accessible at `site-name.com/subscriptions` (substituting in your domain) that can be used to manage feeds. It is disabled by default; it can be enabled in `config/procyon-settings.php`. Again, if your Procyon instance is public/web-accessible, it's recommended to set a password in your .env as mentioned above as otherwise anyone can add or remove subscriptions.

Ensure that the scheduler is added to cron, like so:
```
* * * * * cd ~/site-name.com/www && php artisan schedule:run >> /dev/null 2>&1
```

Digests are automatically generated daily, or may be manually generated using the command `php artisan create-digests`.

The resulting feed can be accessed at `site-name.com`, substituting in your domain.

## Contact
If you have any questions, please contact me via email at [queries@itinerare.net](emailto:queries@itinerare.net).

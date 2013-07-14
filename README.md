# muneris - services for the people

restservice platform for bundeling services into one entry point

implemented so far:

* [nno](http://nno.dk/)
* [maxmind](http://maxmind.com)
* [GeoPostcodes](http://www.geopostcodes.com/)

more will come


## Install:

only via composer

```
$ curl -sS https://getcomposer.org/installer | php
$ mv composer.phar /usr/local/bin/composer
$ git clone https://github.com/mrbase/muneris.git
$ cd muneris
$ cp app/config/parameters.yml.dist app/config/parameters.yml
$ vim app/config/parameters.yml
$ composer install
```


## Populate GeoPostcodes database:

1. Purchase a [GeoPostcode](http://www.geopostcodes.com/) database
2. Download and save the .csv file on the server.
3. run: `php app/console --env=prod muneris:gpc:import /path/to/file.csv`
4. wait … it will take a long time, depending on the size of the purchased database.


## NNO and MaxMind

To use these services you need keys for both.

- [NNO](http://www.nnmarkedsdata.dk/produkter/nn-privat/navne-numre-direkte/)'s service
- [MaxMind](http://www.maxmind.com/en/web_services)'s service


## Usage

### GeoPostal:

`http://server/gpc/countries/{iso2 country code}/cities/{city name}.json`
`http://server/gpc/countries/{iso2 country code}/postcodes/{zip code}.json`

eks:

`http://server/gpc/countries/DK/cities/Kolding.json`
`http://server/gpc/countries/DK/postcodes/6000.json`


### NNO:

`http://server/nno/numbers/{phone number}.json`

eks:

`http://server/nno/numbers/70260085.json`

### MaxMind:

`http://server/mm/cities/{ip2long}.json`

The ip send must be [ip2long](http://publibn.boulder.ibm.com/doc_link/en_US/a_doc_lib/libs/commtrf2/inet_addr.htm) formatted, here are docs on how to do this in [php](http://php.net/ip2long) and [javascript](http://phpjs.org/functions/ip2long/)

eks:

`http://server/mm/cities/1522126436.json`

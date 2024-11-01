#!/bin/bash

flyway -connectRetries=60 migrate

php-fpm -F

#!/bin/bash

sleep 15

flyway -connectRetries=15 migrate

php-fpm -F

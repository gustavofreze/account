#!/bin/bash

flyway -connectRetries=15 migrate

php-fpm -F

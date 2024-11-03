# Account

[![License](https://img.shields.io/badge/license-MIT-green)](LICENSE)

* [Overview](#overview)
    - [Use cases](#use_cases)
    - [Queries](#queries)
* [Installation](#installation)
    - [Repository](#repository)
    - [Configuration](#configuration)
    - [Tests](#tests)
    - [Review](#review)
    - [Reports](#reports)
* [Environment setup](#environment_setup)

<div id="overview"></div> 

## Overview

Each cardholder (holder) has an account with their information.
For each operation performed, a transaction is created and associated with the respective account.
Transactions have specific types, e.g., **Normal Purchase**, **Purchase with installments**, **Withdrawal**, and
**Credit Voucher**.
**Normal Purchase** and **Withdrawal** transactions are recorded with **negative** values, while **Credit Voucher**
transactions are recorded with **positive** values.

<p align="center">
    <img src="./docs/excalidraw/account-flow.svg" alt="Account flow diagram" width="800">
</p>

<p align="center"><small>Figure 01: Account transaction flow diagram.</small></p>

<div id='use_cases'></div> 

### Use cases

- [Account opening](docs/USE_CASES.md#account-opening)
- [Account crediting](docs/USE_CASES.md#account-crediting)
- [Account debiting](docs/USE_CASES.md#account-debiting)
- [Account withdrawal](docs/USE_CASES.md#account-withdrawal)

<div id='queries'></div> 

### Queries

- [Find account by id](docs/QUERIES.md#find-account-by-id)

<div id='installation'></div> 

## Installation

<div id='repository'></div> 

### Repository

To clone the repository using the command line, run:

```bash
git clone https://github.com/gustavofreze/account.git
```

<div id='configuration'></div> 

### Configuration

To install project dependencies locally, run:

```bash
make configure
```

To start the application containers, run:

```bash
make start
```

To stop the application containers, run:

```bash
make stop
```

<div id='tests'></div> 

### Tests

Run all tests with coverage:

```bash
make test 
```

Run all tests without coverage:

```bash
make test-no-coverage
```

<div id='review'></div> 

### Review

Run static code analysis:

```bash
make review 
```

<div id='reports'></div> 

### Reports

Open static analysis reports (e.g., coverage, lints) in the browser:

```bash
make show-reports 
```

> You can check other available commands by running `make help`.

<div id='environment_setup'></div> 

## Environment setup

### Access URLs

| Environment | DNS                      | 
|:------------|:-------------------------|
| `Local`     | http://account.localhost |

### Database

| Environment | URL                         | Port | 
|:------------|:----------------------------|:----:|
| `Local`     | jdbc:mysql://localhost:3307 | 3307 |

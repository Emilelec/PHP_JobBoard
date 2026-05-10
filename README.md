# Job Board — Symfony 7

A job board web application built with Symfony 7, demonstrating core framework concepts.

## Features
- Employer and candidate roles
- Job posting CRUD (employers only)
- Job application system (candidates only)
- Session-based authentication
- Employer dashboard with application counts
- Candidate application tracking

## Stack
- PHP 8.3 / Symfony 7
- PostgreSQL
- Doctrine ORM
- Twig

## Setup
1. Clone the repo
2. Copy `.env.example` to `.env` and fill in values
3. Start a PostgreSQL instance
4. Run `composer install`
5. Run `php bin/console doctrine:migrations:migrate`
6. Run `symfony serve`
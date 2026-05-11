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
```bash
   git clone https://github.com/Emilelec/PHP_JobBoard.git
   cd PHP_JobBoard
```

2. Install dependencies
```bash
   composer install
```

3. Configure environment
```bash
   cp .env.example .env
   # Edit .env and set your DATABASE_URL and APP_SECRET
```

4. Start a PostgreSQL instance
```bash
   docker run -d \
     --name job-board-db \
     -e POSTGRES_DB=job_board \
     -e POSTGRES_USER=app \
     -e POSTGRES_PASSWORD=app \
     -p 5432:5432 \
     postgres:16
```

5. Run migrations
```bash
   php bin/console doctrine:migrations:migrate
```

6. Start the dev server
```bash
   symfony serve
```

7. Visit `http://127.0.0.1:8000` and register an account

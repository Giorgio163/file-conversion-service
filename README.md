# File Conversion Service

A small asynchronous file conversion API built with **Symfony 7**,
**Docker**, and **MySQL**.

The service allows clients to:

-   Upload a file and request conversion
-   Check job status
-   Download the converted file when ready

Jobs are processed asynchronously using **Symfony Messenger**.

------------------------------------------------------------------------

## Tech Stack

-   PHP 8.2
-   Symfony 7.4
-   MySQL 8
-   Symfony Messenger (Doctrine transport)
-   Docker & Docker Compose
-   phpMyAdmin

------------------------------------------------------------------------

## Requirements

-   Docker
-   Docker Compose

------------------------------------------------------------------------

## Installation

Clone the repository:

``` bash
git clone https://github.com/Giorgio163/file-conversion-service.git
cd file-conversion-service
```

Start containers:

``` bash
docker compose up -d --build
```

Install dependencies:

``` bash
docker compose exec php composer install
```

------------------------------------------------------------------------

## Database Setup

Create database and run migrations:

``` bash
docker compose exec php php bin/console doctrine:database:create --if-not-exists
docker compose exec php php bin/console doctrine:migrations:migrate -n
```

Setup Messenger transport table:

``` bash
docker compose exec php php bin/console messenger:setup-transports
```

------------------------------------------------------------------------

## Running the Worker

Messenger worker must be running to process jobs:

``` bash
docker compose exec php php bin/console messenger:consume async -vv
```

Without the worker, jobs will remain in `PENDING` or `PROCESSING`.

------------------------------------------------------------------------

## API Endpoints

Base URL:

    http://localhost:8080/swagger.html

### Create Conversion Job

**POST** `/api/jobs`

Example:

``` bash
curl -X POST http://localhost:8080/api/jobs   -F "file=@/path/to/sample.csv"   -F "outputFormat=json"
```

Response (202 Accepted):

``` json
{
  "id": "uuid",
  "status": "PENDING",
  "statusUrl": "/api/jobs/{id}",
  "downloadUrl": "/api/jobs/{id}/download"
}
```

------------------------------------------------------------------------

### Get Job Status

**GET** `/api/jobs/{id}`

Example:

``` bash
curl http://localhost:8080/api/jobs/{id}
```

------------------------------------------------------------------------

### Download Converted File

**GET** `/api/jobs/{id}/download`

-   Returns **409** if job not ready
-   Returns file if conversion completed

Example:

``` bash
curl -L -o result.xml http://localhost:8080/api/jobs/{id}/download
```

------------------------------------------------------------------------

## File Storage

Files are stored locally:

    var/storage/input
    var/storage/output

Directories are created automatically if missing.

------------------------------------------------------------------------

## phpMyAdmin

Access database UI:

    http://localhost:8081

Credentials:

-   Server: `db`
-   Username: `user`
-   Password: `secret`

------------------------------------------------------------------------

## Running Tests

``` bash
docker compose exec -e APP_ENV=test -e APP_DEBUG=1 php php bin/phpunit
```

------------------------------------------------------------------------

## Useful Commands

List routes:

``` bash
docker compose exec php php bin/console debug:router
```

Clear cache:

``` bash
docker compose exec php php bin/console cache:clear
```

Follow logs:

``` bash
docker compose logs -f
```

------------------------------------------------------------------------

## Notes

-   Conversion logic is simulated (dummy output generation).
-   Asynchronous processing is handled via Symfony Messenger with
    Doctrine transport.
-   Worker must be running to complete jobs.

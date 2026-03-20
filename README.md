# ACP Demo Portal

ACP Demo Portal is a CakePHP 5 application used for convention
registrations, scheduling, submissions, judging, and reporting.

## Stack

- PHP 8.2 + Apache
- CakePHP 5.x
- MySQL (expected host name: `mysql-db`)

## Project Layout

- `src/` application controllers, models, templates
- `config/` CakePHP and app constants configuration
- `plugins/` custom plugin code
- `webroot/` public assets and uploaded/generated files

## Local Setup

1. Copy the sample constants file:

```bash
cp config/my_const.example.php config/my_const.php
```

2. Edit `config/my_const.php` and set your local values:

- DB host/user/password/name
- SMTP credentials
- captcha keys
- HTTP and base paths

3. Ensure dependencies are present (this repository already includes `vendors/`).

4. Start with Docker (manual flow):

```bash
docker network create acp-net
docker run -d --name mysql-db --network acp-net \
	-e MYSQL_ROOT_PASSWORD=rootpass \
	-e MYSQL_DATABASE=convention_acpdemo \
	-p 3306:3306 mysql:8.0

docker build -t acp-web -f Dockerfile .
docker run -d --name acp-web --network acp-net \
	-p 8080:80 -v "$PWD":/var/www/html/acp_demo acp-web
```

5. Import the schema/data dump:

```bash
cat convention_acpdemo.sql | docker exec -i mysql-db \
	mysql -uroot -prootpass convention_acpdemo
```

6. Open `http://localhost:8080`.

## Security Notes

- Do not commit `config/my_const.php` (contains secrets).
- Uploaded files and runtime logs are excluded from git by `.gitignore`.
- If real credentials were used previously, rotate them before production use.

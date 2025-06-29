# Local Testing Setup

This folder contains files useful for testing the project locally with HTTPS and Gmail SMTP.

## 1. SSL certificate

Self-signed certificate generated for `localhost` is located in `ssl/`.
You can regenerate it with:

```bash
openssl req -x509 -newkey rsa:2048 -nodes -keyout ssl/localhost.key -out ssl/localhost.crt -days 365 -subj "/CN=localhost"
```

## 2. nginx configuration

`nginx/nginx.conf` is an example configuration. Update the paths inside it so they point to this repository. Then include or copy it to your local nginx configuration, e.g. `/etc/nginx/sites-enabled/wizytowka.conf`.
Make sure you have `php-fpm` running on port `9000`.

Reload nginx after the file is in place:

```bash
sudo nginx -s reload
```

## 3. Environment variables

Copy `.env` to `.env` (or create one) and fill in your Gmail SMTP credentials. It is recommended to use a Gmail App Password.

```
SMTP_HOST=smtp.gmail.com
SMTP_PORT=587
SMTP_USER=you@gmail.com
SMTP_PASS=app_password
SMTP_FROM=you@gmail.com
SMTP_FROM_NAME=Spyrja Contact Form
SMTP_TO=samband@spyrja.com
```

## 4. Testing

1. Start php-fpm (`php-fpm -F`) or another PHP handler.
2. Start nginx with the provided config.
3. Open `https://localhost/` in your browser.
4. Submit the contact form to send a test email to `samband@spyrja.com`.

If something does not work:
- check the nginx error logs
- verify the environment variables are set correctly
- ensure that less secure app access is allowed or app password is used in Gmail

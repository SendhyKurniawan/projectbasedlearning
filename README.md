<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework. You can also check out [Laravel Learn](https://laravel.com/learn), where you will be guided through building a modern Laravel application.

If you don't feel like reading, [Laracasts](https://laracasts.com) can help. Laracasts contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

## Laravel Sponsors

We would like to extend our thanks to the following sponsors for funding Laravel development. If you are interested in becoming a sponsor, please visit the [Laravel Partners program](https://partners.laravel.com).

### Premium Partners

- **[Vehikl](https://vehikl.com)**
- **[Tighten Co.](https://tighten.co)**
- **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**
- **[64 Robots](https://64robots.com)**
- **[Curotec](https://www.curotec.com/services/technologies/laravel)**
- **[DevSquad](https://devsquad.com/hire-laravel-developers)**
- **[Redberry](https://redberry.international/laravel-development)**
- **[Active Logic](https://activelogic.com)**

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

## Docker Usage

This project includes a Docker setup for easy development and deployment.

### Prerequisites

- [Docker](https://www.docker.com/get-started)
- [Docker Compose](https://docs.docker.com/compose/install/)

### Getting Started

1.  **Clone the repository** (if you haven't already).
2.  **Copy the environment file**:
    ```bash
    cp .env.example .env
    ```
3.  **Update `.env` configuration** for Docker:
    ```ini
    DB_CONNECTION=mysql
    DB_HOST=db
    DB_PORT=3306
    DB_DATABASE=pjbl
    DB_USERNAME=pjbl
    DB_PASSWORD=password
    ```
4.  **Build and start the containers**:
    ```bash
    docker-compose up -d --build
    ```
5.  **Access the application**:
    - Web App: [http://localhost:8000](http://localhost:8000)
    - Database: Port 3306 (internal)

### Commands

- **Stop containers**: `docker-compose down`
- **View logs**: `docker-compose logs -f`
- **Run Artisan command**: `docker-compose exec app php artisan <command>`
- **Run Composer command**: `docker-compose exec app composer <command>`
- **Run NPM command**: `docker-compose exec app npm <command>`

### Default Credentials

After running migrations and seeders, you can use the following accounts:

| Role      | Email             | Password   |
| :-------- | :---------------- | :--------- |
| **Admin** | `admin@pjbl.test` | `password` |
| **Dosen** | `dosen@pjbl.test` | `password` |

### Troubleshooting

**Error: `php_network_getaddresses: getaddrinfo for db failed`**
This error occurs if you run `php artisan serve` locally while `.env` is configured for Docker (`DB_HOST=db`).

**Solution:**

1.  **Stop `php artisan serve`** (Ctrl+C in your terminal).
2.  Use the Docker URL: `http://localhost:8000`.
3.  If using **Cloudflare Tunnel**, ensure it points to the Docker service:
    - If running manually: `cloudflared tunnel --url http://localhost:8000`
    - If running in Docker: Ensure the tunnel container is on the same network (`pjbl-network`) and points to `http://web:80` (internal service name).

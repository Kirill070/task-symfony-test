# Описание

Тестовое задание на фреймворке Symfony. Реализует REST API для управления товарами и категориями, консольную команду для импорта данных из JSON, а также систему email-уведомлений при изменении товаров.

Данный проект также использовался для отработки взаимодействия с LLM-агентом (Claude Code).

ТЗ: https://docs.google.com/document/d/1ixExyddJG4V4jxpSuYH61rh0rZ-RFztfqWa3aApZnlI/edit?pli=1&tab=t.0

## Минимальные требования

- PHP 8.4+
- Composer
- SQLite (default) или другая БД, сконфигурированная в `.env`

## Установка

```bash
git clone git@github.com:Kirill070/task-symfony-test.git
cd task-symfony-test
cp .env .env.local
composer install
php bin/console doctrine:schema:create
```

## Запуск

```bash
php -S localhost:8000 -t public
```

## API эндпоинты

| Метод  | URL                   | Описание           |
|--------|-----------------------|--------------------|
| GET    | /api/products         | Список товаров     |
| GET    | /api/products/{id}    | Один товар         |
| POST   | /api/products         | Создать товар      |
| PUT    | /api/products/{id}    | Обновить товар     |
| DELETE | /api/products/{id}    | Удалить товар      |

## Консольные команды

Импорт категорий и товаров из JSON:

```bash
php bin/console app:import-products Data/categories.json Data/products.json
```

## Примечание

- Email для уведомлений настраивается в `.env` (параметр `NOTIFICATION_EMAIL`).
- Для отправки писем настройте `MAILER_DSN` в `.env`.
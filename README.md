# Subscription Billing System

A Laravel-based subscription billing application that helps merchants manage customers, subscription plans, usage events, billing periods, invoices, and customer usage analytics.

## Features

- **Merchant Management** — Manage merchants and view merchant-specific data.
- **Customer Management** — Create, update, and manage customers for each merchant.
- **Plan Management** — Create subscription plans with pricing, billing cycles, included usage, and overage rates.
- **Subscription Management** — Create and manage customer subscriptions and billing periods.
- **Usage Tracking** — Record usage events with quantities and idempotency keys to prevent duplicate events.
- **Invoice Generation** — Generate invoices based on subscription pricing and usage.
- **Merchant Dashboard** — View active subscription customers, top customers by usage, and projected overage revenue.
- **Churn Risk Analysis** — Identify customers whose usage has dropped significantly compared with the previous month.

## Tech Stack

- PHP
- Laravel
- MySQL
- Blade templates
- Bootstrap
- Composer
- Artisan CLI

## Requirements

Ensure the following are installed:

- PHP version compatible with your Laravel version
- Composer
- MySQL database
- Redis Queue

## Installation

### 1. Clone or open the project

```bash
git clone git clone https://github.com/mohanlalcoder/Subscription-Billing.git
cd Subscription-Billing
```

If the project already exists locally, open its directory instead.

### 2. Install PHP dependencies

```bash
composer install
```

### 3. Configure the environment

Copy the example environment file:

```bash
copy .env.example .env
```

Generate the application key:

```bash
php artisan key:generate
```

### 4. Configure the database

Update the database settings in `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=subscription_billing
DB_USERNAME=root
DB_PASSWORD=
```

Create the database before running migrations. Adjust the credentials to match your local environment.

### 5. Run migrations

```bash
php artisan migrate
```

If the project includes seeders and you want to populate the database with initial data, run:

```bash
php artisan db:seed
```

**Note:** Run individual seeders only after ensuring their required merchant, customer, subscription, and billing-period records exist.

### 6. Start the Laravel server

```bash
php artisan serve
```

Open the local URL displayed in your terminal, usually:

http://127.0.0.1:8000

## Main Application Modules

### Merchants

Merchants are the owners of customer and subscription data. The dashboard displays information scoped to the relevant merchant.

### Customers

Customers belong to merchants and can have subscriptions. Customer email uniqueness should follow the application's merchant-level validation and database constraints.

### Plans

Plans define subscription pricing and billing settings, including:

- Plan name
- Base price
- Billing cycle
- Included usage units
- Overage rate

Plan names can be restricted to unique values within each merchant.

### Subscriptions and Billing Periods

A subscription connects a customer to a plan. Billing periods track the dates, included usage, base pricing, overage rate, and total billing amount for a subscription cycle.

Queue Start Command:

You now have two background processes for local development:
Terminal 1 (Checks for billing periods that are due):
php artisan schedule:work

Terminal 2 (Runs queued jobs)
php artisan queue:work --tries=3 --timeout=120

For a local development test only, you can run:
php artisan billing:process-due-cycles --force

The --force option includes unfinished periods, so it can create a test invoice before the real billing period ends. Do not use it in production.
The command should report a queued billing cycle. Your running queue worker should then process:
AggregateUsageJob
       |
       v
GenerateInvoiceJob
       |
       v
Invoice saved to database

Because the job chain waits for aggregation to finish, invoice generation won't run if aggregation fails.

Expired test command manually:
php artisan subscriptions:expire

### Usage Events

Usage events record customer consumption. Typical event data includes:

- Merchant ID
- Customer ID
- Subscription ID
- Event key and event type
- Idempotency key
- Quantity
- Occurrence timestamp
- Optional metadata

Example usage event:

```json
{
    "merchant_id": 1,
    "customer_id": 1,
    "subscription_id": 1,
    "event_key": "test-event-001",
    "event_type": "api_call",
    "idempotency_key": "pay_abc123",
    "quantity": 50,
    "occurred_at": "2026-10-27T10:00:00",
    "metadata": {
        "source": "postman",
        "description": "Test usage event"
    }
}
```

Use IDs that exist in your local database when testing.

### Invoice Generation

Invoices are generated using subscription-period pricing and applicable usage charges. Depending on the billing implementation, the base charge may be prorated for partial billing periods.

Review invoice line items and billing-period values when debugging unexpected totals.

### Merchant Dashboard

The dashboard provides a view of:

- Active subscription customers
- Top customers by usage
- Customers at risk of churn
- Projected or accrued overage revenue

The exact calculations depend on the dashboard queries and the billing rules implemented in the application.

## Useful Artisan Commands

Clear cached application configuration:

```bash
php artisan optimize:clear
```

Run database migrations:

```bash
php artisan migrate
```

Run a specific seeder:

```bash
php artisan db:seed --class=ChurnRiskUsageSeeder
```

Open Laravel Tinker:

```bash
php artisan tinker
```

List application routes:

```bash
php artisan route:list
```

Start the local server:

```bash
php artisan serve
```

## Troubleshooting

### Database connection errors

- Confirm that the database server is running.
- Verify the database name and credentials in `.env`.
- Ensure the database exists.
- Run `php artisan optimize:clear` after changing configuration.

### Seeder reports missing subscriptions or periods

Create the required merchant, customer, active subscription, and subscription-period records before running the churn-risk seeder.

### Invoice amount is incorrect

Check the subscription period's base price, included usage, overage rate, usage totals, billing dates, and any proration logic in the invoice-generation job.

### Duplicate usage events

Ensure each incoming event has a stable idempotency key and that the application enforces the intended uniqueness constraint.

## Security Notes

- Never commit `.env` or production credentials.
- Keep application debug mode disabled in production.
- Validate merchant ownership when accessing customers, subscriptions, usage events, and invoices.
- Use database transactions for operations that create related billing records.
- Avoid modifying issued invoices without an appropriate adjustment or credit-note workflow.

## License

Add the license applicable to your project before distributing it.

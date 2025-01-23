# Usage

## Installation

```bash
composer require khamdullaevuz/laravel-payme
```

## Configuration

```bash
php artisan vendor:publish --tag=payme-config
```

## Edit your configs in ```config/payme.php```

## Add service provider to `config/app.php`

```php
'providers' => [
    // Other Service Providers
    Khamdullaevuz\Payme\PaymeServiceProvider::class,
],
```

## Add facade to globally aliases in `config/app.php`

```php
'aliases' => [
    // Other Aliases
    'Payme' => Khamdullaevuz\Payme\Facades\Payme::class,
],
```

## Migrate database

```bash
php artisan migrate
```

## Usage in route

```php
use Khamdullaevuz\Payme\Facades\Payme;
use Khamdullaevuz\Payme\Http\Middleware\PaymeCheck;
use Illuminate\Http\Request;

// Other Routes

Route::any('/payme', function (Request $request) {
    return Payme::handle($request);
})->middleware(PaymeCheck::class);
```

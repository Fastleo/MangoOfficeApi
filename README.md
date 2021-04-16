## Класс для работы с IP-телефонией Mango-Office

### установка
```
composer require fastleo/mango-office-api
```
### использование
```php
use Fastleo\MangoOfficeApi\Mango;

$mango = new Mango('Уникальный код АТС', 'Ключ для создания подписи');

// История звонков
$mango->getHistory('UNIX формат начальная дата', 'UNIX формат конечная дата');
```

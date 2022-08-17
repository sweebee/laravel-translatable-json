# laravel-translatable-json

This package extends the spatie translatable package and makes nested JSON data translatable.

### Usage

```php

namespace App\Models;

...
use Wiebenieuwenhuis\LaravelTranslatableJson\HasJsonTranslations;


class Post extends Model
{
    use HasJsonTranslations;
    
    public $translatableJson = [
        'content' => [
            'title',
            'content',
        ],
    ];
    
    ...
```

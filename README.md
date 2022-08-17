# laravel-translatable-json

This package extends the spatie translatable package and makes nested JSON data translatable.

Please note, when setting translatable json keys, all of them will be translatable (root keys and all nested keys).

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

### Whats happening

Input:
```php
[
    [
        "title": "English title",
    ]
]
```

Will be saved as:

```php
[
    [
        "title": [
            "en": "English title",
        ],
    ]
]
```

When setting a different locale (nl):
```php
[
    [
        "title": "Nederlandse titel",
    ]
]
```
it will be saved as:
```php
[
    [
        "title": [
            "en": "English title",
            "nl": "Nederlandse titel",
        ],
    ]
]
```

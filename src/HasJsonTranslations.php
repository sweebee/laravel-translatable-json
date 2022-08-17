<?php

namespace Wiebenieuwenhuis\LaravelTranslatableJson;

use Spatie\Translatable\HasTranslations;
use Spatie\Translatable\Translatable;

trait HasJsonTranslations {

    use HasTranslations {
        HasTranslations::getAttributeValue as parentGetAttributeValue;
        HasTranslations::setAttribute as parentSetAttributeValue;
        HasTranslations::getTranslation as parentGetTranslation;
        HasTranslations::getTranslatableAttributes as parentGetTranslatableAttributes;
    }

    /*
     *
     */
    public function getTranslatableAttributes(): array
    {
        $attributes = $this->parentGetTranslatableAttributes();
        return array_merge($attributes, array_keys($this->getTranslatableJsonAttributes()));
    }

    /**
     * @param string $key
     * @param string $locale
     * @param bool $useFallbackLocale
     *
     * @return array|mixed
     */
    public function getTranslation(string $key, string $locale, bool $useFallbackLocale = true)
    {
        $translatableJson = array_keys($this->getTranslatableJsonAttributes());

        if(in_array($key, $translatableJson)){
            $value = $this->getJsonTranslation($key, $locale);

            if(!$value && $useFallbackLocale){
                $fallbackConfig = app(Translatable::class);

                $fallbackLocale = $fallbackConfig->fallbackLocale ?? config('app.fallback_locale');
                return $this->getJsonTranslation($key, $fallbackLocale);
            }

            return $value;
        }

        return $this->parentGetTranslation($key, $locale, $useFallbackLocale);
    }

    public function getTranslatableJsonAttributes(): array
    {
        return is_array($this->translatableJson)
            ? $this->translatableJson
            : [];
    }

    public function getAttributeValue($key): mixed
    {
        if (! $this->isTranslatableJsonAttribute($key)) {
            return $this->parentGetAttributeValue($key);
        }

        return $this->getJsonTranslation($key, $this->getLocale());
    }

    /**
     * @param string $key
     *
     * @return bool
     */
    public function isTranslatableJsonAttribute(string $key): bool
    {
        return isset($this->translatableJson[$key]);
    }

    /**
     * @param $key
     * @param $locale
     *
     * @return array
     */
    public function getJsonTranslation($key, $locale): array
    {
        $data = json_decode($this->attributes[$key], true);
        $translatable = $this->translatableJson[$key];
        foreach($data as $key => $value){
            $data[$key] = $this->translateArray($translatable, $key, $value, $locale);
        }
        return $data;
    }

    /**
     * @param $translatable
     * @param $key
     * @param $data
     * @param $locale
     *
     * @return array|mixed|null
     */
    public function translateArray($translatable, $key, $data, $locale)
    {
        if(in_array($key, $translatable)){
            $data = $data[$locale] ?? null;
        } else {
            if(is_array($data)){
                foreach($data as $key => $value){
                    $data[$key] = $this->translateArray($translatable, $key, $value, $locale);
                }
            }
        }
        return $data;
    }

    /**
     * @param $key
     * @param $value
     *
     * @return \App\Models\Post|mixed
     */
    public function setAttribute( $key, $value )
    {
        if ($this->isTranslatableJsonAttribute($key)) {
            return $this->setJsonTranslation($key, $value, $this->getLocale());
        }

        return $this->parentSetAttributeValue($key, $value);
    }

    /**
     * @param $column_key
     * @param $data
     * @param $locale
     *
     * @return $this
     */
    public function setJsonTranslation($column_key, $data, $locale)
    {

        $original = json_decode($this->attributes[$column_key] ?? '[]', true);
        $translatable = $this->translatableJson[$column_key] ?? [];

        foreach($data as $key => $value){
            $data[$key] = $this->setArrayTranslation($original[$key] ?? null, $translatable, $key, $value, $locale);
        }

        $this->attributes[$column_key] = $this->asJson($data);

        return $this;
    }

    /**
     * @param $original
     * @param $translatable
     * @param $key
     * @param $data
     * @param $locale
     *
     * @return array|mixed
     */
    public function setArrayTranslation($original, $translatable, $key, $data, $locale)
    {
        if(in_array($key, $translatable)){

            $data = array_merge($original ? (is_array($original) ? $original : []) : [], [
                $locale => $data,
            ]);
        } else {
            if(is_array($data)){
                foreach($data as $key => $value){
                    $data[$key] = $this->setArrayTranslation($original[$key] ?? null, $translatable, $key, $value, $locale);
                }
            }
        }

        return $data;
    }
}

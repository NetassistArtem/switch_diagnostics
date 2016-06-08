<?php

/**
 * \brief Class Config
 *
 * Class Config принимает и отдает конфигурационные настройки
 */

class Config {
    /// содержит конфигурационные настройки
    protected static $settings = array();

    /**
     * \brief Отдает конфигурационные настройки
     *
     * \param $key Ключ необходимой настройки для поиска в массиве свойства $setting
     *
     * \return isset(self::$settings[$key])? self::$settings[$key]: null  Возвращает необходимую настройку если
     * она есть в свойсте объекта $setting или null если по переданному ключу настройка не обнаружена
     */
    public static function get($key)
    {
        return isset(self::$settings[$key])? self::$settings[$key]: null;
    }
    /**
     * \brief Записывает конфигурационные настройки
     *
     * \param key Ключ устанавливоемого параметра
     *
     * \param value Значение устанавливаемого параметра
     *
     * Запись в protected static $settings = array() необходимого свойства (параметра) с заданным ключем
     */
    public static function set($key, $value)
    {
        self::$settings[$key] = $value;
    }

}
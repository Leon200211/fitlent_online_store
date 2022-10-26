<?php

// все запросы по ссылкам
// навигационные запросы по сайту


// константа безопасности
define('VG_ACCESS', true);


header('Content-Type:text/html;charset=utf-6'); // в какой кодировки пользователь обрабатывает данные (первый заголовок)
session_start(); //стартуем сессию



require_once 'config.php';  // базовые настройки для хостинга
require_once 'core/base/settings/internal_settings.php';  // фундаментальные настройки сайта

## PHP-HSCache for HandlerSocketi
```
// создаём таблицу на серверах кеша
# php ./handler_cache.php new test2
	localhost [handler_s1] -> hs_test2 -> OK
	localhost [handler_s2] -> hs_test2 -> OK
# php ./handler_cache.php get test2 a
	false
# php ./handler_cache.php set test2 a 123
# php ./handler_cache.php get test2 a
	'123'
```

### PHP-ext-handlerSocketi documentation
https://github.com/mantyr/php-ext-handlersocketi
#### HandlerSocket Plugin forMySQL, ebuild for Gentoo
https://github.com/mantyr/overlay-gentoo-flower/tree/master/dev-db/HandlerSocket

### Описание
Это черновик, можно создавать таблички на HS серверах, можно читать и записывать туда данные.
Поддерживается сериализация/десериализация массивов и объектов при сохранении/считывании.

Планируется дописать:
 
* более удобный планировщик выбирающий сервер
* автоматику по плановой отчистки кеша (Garbage Collector)
* горизонтальный шардинг данных, будет RAID 0, 1, 3 для типа таблицы

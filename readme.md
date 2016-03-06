# php-benchmark
Набор тестов для проверки производительности PHP. С помощью benchmark.php можно оценить производительность хостинга, сравнить скорость работы разных версий PHP.

## Пример использования
Запуск тестов из консоли без загрузки скрипта:

```
% php -d allow_url_include=1 -r 'include "https://raw.githubusercontent.com/anton-pribora/php-benchmark/master/benchmark.php";'
PHP 5.6.18                 Result      Units  Score
---------------------------------------------------
Simple math             4,166,388   oper/sec       
String concat           7,416,097   oper/sec       
Array + array             739,047   oper/sec       
Use array_merge           407,103   oper/sec       
Create empty object     3,669,997    obj/sec       
Foreach array           7,791,002   iter/sec       
Use array_walk          1,169,196   iter/sec       
Foreach array object    4,432,615   iter/sec       
Foreach simple object   4,209,200   iter/sec       
Call closure              754,202   call/sec       
Call object function    1,094,096   call/sec       
Call user function      1,225,817   call/sec       
Function rand           1,547,847   call/sec       
Function is_null        1,482,252   call/sec       
Function empty          8,325,254   call/sec       
Function isset          8,416,068   call/sec       
---------------------------------------------------
Avg score                                          
```

Запуск тестов с сохраниением результатов:

```
% php benchmark.php --out myresults.txt
```
  
Запуск тестов с сравнением результатов:

```
% php benchmark.php --ref myresults.txt 
PHP 5.6.18                 Result      Units  Score
---------------------------------------------------
Simple math             3,956,883   oper/sec    1.1
String concat           6,872,280   oper/sec      1
Array + array             722,726   oper/sec      1
Use array_merge           403,922   oper/sec      1
Create empty object     3,292,726    obj/sec    1.1
...
```

Если указаны реферальные значения тестов, то будет также рассчитываться `Score` - показатель эффективности. Так, если `Score` равно двум, то тест выполнился в два раза лучше, чем реферальный.

Для удобства в папке `refs` приведены эталонные тесты, которые были выполнены на ноутбуке с процессором `Intel(R) Core(TM)2 Duo CPU T5550 @ 1.83GHz` с разными версиями PHP. Производительность этого процессора вполне соотвествует среднему VDS.

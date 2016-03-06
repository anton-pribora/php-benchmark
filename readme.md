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

Пример запуска тестов на коммерческом VDS с виртуализацией через KVM:

```
# php -d allow_url_include=1 -r 'include "https://raw.githubusercontent.com/anton-pribora/php-benchmark/master/benchmark.php";' -- --ref https://raw.githubusercontent.com/anton-pribora/php-benchmark/master/refs/php-5.6.19-core-2duo-1.8G-debian-amd64.txt
PHP 5.6.18                 Result      Units  Score
---------------------------------------------------
Simple math             7,952,649   oper/sec    1.6
String concat          12,332,563   oper/sec    1.2
Array + array             457,657   oper/sec    1.2
Use array_merge           342,916   oper/sec    1.2
Create empty object     5,782,185    obj/sec    1.4
Foreach array          18,063,568   iter/sec    1.3
Use array_walk          5,765,434   iter/sec    1.3
Foreach array object    6,168,008   iter/sec    1.3
Foreach simple object   5,814,055   iter/sec    1.3
Call closure            7,293,731   call/sec    1.1
Call object function    7,802,731   call/sec    1.1
Call user function      9,600,850   call/sec    1.1
Function rand          11,218,436   call/sec    1.3
Function is_null        7,827,497   call/sec    1.2
Function empty         25,015,757   call/sec    1.2
Function isset         29,764,804   call/sec    1.4
---------------------------------------------------
Avg score                                       1.3
```

Из результатов видно, что VDS примерно на 30% производительней чем ноутбук.

Также можно оценить, какой прирост производительсти будет, если использовать другую версию PHP.

```
% ./php-bin/php-5.6.19 benchmark.php --out refs.txt  
PHP 5.6.19                 Result      Units  Score
---------------------------------------------------
Simple math             5,135,641   oper/sec       
String concat          10,507,440   oper/sec       
Array + array             394,956   oper/sec       
Use array_merge           311,205   oper/sec       
Create empty object     4,371,526    obj/sec       
Foreach array          10,682,632   iter/sec       
Use array_walk          4,542,787   iter/sec       
Foreach array object    4,640,345   iter/sec       
Foreach simple object   4,737,873   iter/sec       
Call closure            6,571,837   call/sec       
Call object function    7,132,668   call/sec       
Call user function      8,701,253   call/sec       
Function rand           9,474,553   call/sec       
Function is_null        6,328,242   call/sec       
Function empty         20,324,406   call/sec       
Function isset         22,015,516   call/sec       
---------------------------------------------------
Avg score                                          
% ./php-bin/php-7.0.4 benchmark.php --ref refs.txt 
PHP 7.0.4                  Result      Units  Score
---------------------------------------------------
Simple math            30,624,965   oper/sec      6
String concat          14,199,853   oper/sec    1.4
Array + array             722,964   oper/sec    1.8
Use array_merge           764,421   oper/sec    2.5
Create empty object     6,157,331    obj/sec    1.4
Foreach array          27,945,561   iter/sec    2.6
Use array_walk          9,947,772   iter/sec    2.2
Foreach array object    7,791,154   iter/sec    1.7
Foreach simple object   8,403,637   iter/sec    1.8
Call closure           11,472,133   call/sec    1.7
Call object function   11,263,529   call/sec    1.6
Call user function     13,057,357   call/sec    1.5
Function rand          14,683,969   call/sec    1.5
Function is_null       25,492,346   call/sec      4
Function empty         25,491,410   call/sec    1.3
Function isset         26,603,067   call/sec    1.2
---------------------------------------------------
Avg score                                       2.1
```

Из тестов видно, что PHP 7.0.4 примерно в шесть (!) раз быстрее справляется с простыми расчётами. И в четыре (!) раза быстрее использует `is_null` чем PHP 5.6.19. В целом же можно утверждать, что при переходе на седьмую версию, общая производительность увеличится как минимум в два раза.

Приятного тестирования :)

# php-benchmark

Набор тестов для проверки производительности PHP. С помощью benchmark.php можно оценить производительность хостинга, сравнить скорость работы разных версий PHP.

С помощью теста удобно оценивать общую производительность сервера, а также сравнивать разные версии PHP. Однако, нужно понимать, что эти тесты являются ситетическими, по ним нельзя оценить производтельность реальных приложений. Но можно сравнить разных хостеров, если на серверах установлены одинаковые версии PHP.

## Быстрый старт

Протестировать все системные версии PHP:

```bash
wget -qO- https://raw.githubusercontent.com/anton-pribora/php-benchmark/master/get_and_start_multitest.sh | bash
```

Протестировать указанные версии PHP:

```bash
wget -qO- https://raw.githubusercontent.com/anton-pribora/php-benchmark/master/get_and_start_multitest.sh | bash php5.6 php7.4
```

Скрипт скачает бенчмарк и мультитест в папку /tmp, после чего запустит тесты.

Пример запуска:

```bash
$ ./get_and_start_multitest.sh /usr/bin/php* ~/test2/php-src/sapi/cli/php
Testing PHP 5.6.40 ... done
Testing PHP 7.3.12 ... done
Testing PHP 7.4.0 ... done
Testing PHP 8.0.0 ... done
AMD Ryzen 7 2700 Eight-Core Processor  Units     PHP 5.6.40  PHP 7.3.12  PHP 7.4.0   PHP 8.0.0
Simple math                            oper/sec  10,854,474  16,985,633  16,848,437  167,913,419
String concat                          oper/sec  15,040,862  13,284,544  13,644,075  51,065,321
Array + array                          oper/sec  1,106,521   2,503,988   2,552,109   3,585,115
Use array_merge                        oper/sec  661,500     1,389,261   1,497,644   3,632,503
Create empty object                    obj/sec   6,906,456   2,602,294   8,701,608   27,962,074
Foreach array                          iter/sec  16,336,167  32,501,948  29,966,397  121,715,210
Use array_walk                         iter/sec  2,825,851   2,610,945   2,683,153   16,560,682
Foreach array object                   iter/sec  7,576,593   8,189,503   8,034,629   21,243,940
Foreach simple object                  iter/sec  7,436,535   10,661,587  10,629,987  48,841,167
Call closure                           call/sec  1,544,574   1,084,538   1,254,531   42,988,544
Call object function                   call/sec  2,493,997   2,075,726   2,460,575   80,364,105
Call user function                     call/sec  2,562,125   2,136,204   2,455,150   82,173,421
Function rand                          call/sec  3,284,780   2,849,542   3,311,249   79,713,543
Function is_null                       call/sec  2,978,263   12,351,476  11,140,412  109,887,526
Function empty                         call/sec  21,325,568  14,333,979  13,560,208  106,165,381
Function isset                         call/sec  21,586,539  13,219,264  14,131,442  143,211,399
```

Чтобы удалить скрипты из папки /tmp выполните:

```bash
rm /tmp/multitest.php /tmp/benchmark.php
```

## Пример использования bemchmark.php
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

Также можно оценить, какой прирост производительсти будет, если использовать другую версию PHP:

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

Из тестов видно, что PHP 7.0.4 примерно в шесть (!) раз быстрее справляется с простыми расчётами. И в четыре (!) раза быстрее использует `is_null` чем PHP 5.6.19. В целом же можно утверждать, что при переходе на седьмую версию общая производительность увеличится как минимум в два раза.

## График производительности PHP

Вот так выглядит рост производительности простых расчётов PHP с версии 5.0 до 7.1. Результат впечатляющий!

![Простые расчёты](https://raw.githubusercontent.com/anton-pribora/php-benchmark/master/simple_calc_graph.png)

Приятного тестирования :)

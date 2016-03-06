<?php
/**
 * Проверка производительности PHP
 * 
 * @copyright Anton Pribora (http://anton-pribora.ru)
 * @license https://github.com/anton-pribora/php-benchmark/blob/master/LICENSE
 */

$tests = new ArrayObject;

$tests[] = new TestSimpleCalc;
$tests[] = new TestStringConcat;
$tests[] = new TestArrayCombine;
$tests[] = new TestArrayMerge;
$tests[] = new TestCreateObjects;
$tests[] = new TestArrayIteration;
$tests[] = new TestArrayIterationArrayWalk;
$tests[] = new TestIterateArrayObj;
$tests[] = new TestIterateObj;

if ( class_exists('Closure', false) ) {
    $tests[] = new TestCallClosure;
}

$tests[] = new TestCallObjFunc;
$tests[] = new TestUserFunction;
$tests[] = new TestFunctionRand;
$tests[] = new TestFunctionIsNull;
$tests[] = new TestFunctionEmpty;
$tests[] = new TestFunctionIsset();

$runner     = new BenchmarkRunner;
$storageRef = new BenchmarkResultStorage;
$storageOut = new BenchmarkResultStorage;

if ( PHP_SAPI == 'cli' ) {
    $writer  = new ConsoleResults();
    $options = getopt('', array('ref:', 'out:', 'help'));
    
    if ( isset($options['help']) ) {
?>
PHP benchmark tests
Usage
    php benchmark.php                     Run all tests and exit
    php benchmark.php --out results.txt   Save results into a file
    php benchmark.php --ref results.txt   Load references from file and calculate score
    php benchmark.php --help              Show help and exit
    
Anton Pribora, 2016
<?php
        exit;
    }
}
else {
    $writer = new HtmlResults();
    
    if ( !isset($options) ) {
        $options = array();
        
        if ( isset($_REQUEST['ref']) ) {
            $options['ref'] = $_REQUEST['ref'];
        }
        
        if ( isset($_REQUEST['out']) ) {
            $options['out'] = $_REQUEST['out'];
        }
    }
}

if ( isset($options['ref']) ) {
    $storageRef->load($options['ref']);
}

list($version) = explode('-', PHP_VERSION); 

$writer->start();
$writer->header('PHP '. $version, 'Result', 'Units', 'Score');

foreach ( $tests as $test ) {
    /* @var $test Benchmark */
    $test->setReference($storageRef->getResult($test));
    $runner->run($test);
    $storageOut->setResult($test);
    $writer->row($test->getName(), Formatter::bigint($test->getResult()), $test->getUnits(), $test->getScore());
}

$writer->footer('Avg score', '', '', $runner->getAvgScore());
$writer->finish();

if ( isset($options['out']) ) {
    $storageOut->save($options['out']);
}

class BenchmarkResultStorage
{
    private $storage = null;
    
    public function __construct()
    {
        $this->storage = new ArrayObject();
    }
    
    public function load($path)
    {
        foreach ( file($path) as $line ) {
            $line = trim($line);
            
            if ( $line ) {
                list($test, $result) = explode(':', $line) + array(null, null);
                
                if ( $result ) {
                    $this->storage[ $test ] = trim($result);
                }
            }
        }
    }
    
    public function setResult(Benchmark $test)
    {
        $this->storage->offsetSet($test->getName(), $test->getResult());
    }
    
    public function getResult(Benchmark $test)
    {
        return isset($this->storage[$test->getName()]) ? $this->storage[$test->getName()] : null;
    }
    
    public function save($path)
    {
        $contents = '';
        
        foreach ( $this->storage as $test => $result ) {
            $contents .= sprintf("%s: %s\n", $test, $result);
        }
        
        file_put_contents($path, $contents);
    }
}

class ConsoleResults
{
    private $endBody = false;
    
    public function start()
    {
    }
    
    public function header($name, $result, $units, $score)
    {
        $this->row($name, $result, $units, $score);
        echo "---------------------------------------------------\n";
    }
    
    public function row($name, $result, $units, $score)
    {
        printf('%-21s %11s %10s %6s'. PHP_EOL, $name, $result, $units, $score);
    }
    
    public function footer($name, $result, $units, $score)
    {
        if ( !$this->endBody ) {
            echo "---------------------------------------------------\n";
            $this->endBody = true;
        }
        $this->row($name, $result, $units, $score);
    }
    
    public function finish()
    {
    }
}

class HtmlResults
{
    public function start()
    {
        echo "<table border=1>\n";
    }
    
    public function header()
    {
        $args = func_get_args();
        echo "  <tr><th>", join("</th><th>", $args), "</th></tr>\n";
    }
    
    public function row()
    {
        $args = func_get_args();
        echo "  <tr><td>", join("</td><td>", $args), "</td></tr>\n";
    }
    
    public function footer()
    {
        $args = func_get_args();
        echo "  <tr><th>", join("</th><th>", $args), "</th></tr>\n";
    }
    
    public function finish()
    {
        echo "</table>\n";
    }
}

class BenchmarkRunner
{
    private $maxExecution = 1;
    
    private $scoreTests = 0;
    private $scoreSum   = 0;
    
    public function run(Benchmark $benchmark)
    {
        $benchmark->start();
        $startTime = Util::microtime();
        
        do {
            $benchmark->nextIteration();
        } while ( Util::microtime() - $startTime < $this->maxExecution );
        
        $benchmark->finish();
        
        if ( !is_null($benchmark->getScore()) ) {
            $this->scoreTests += 1;
            $this->scoreSum   += $benchmark->getScore();
        }
    }
    
    public function getAvgScore()
    {
        return $this->scoreTests ? round($this->scoreSum / $this->scoreTests, 1) : null;
    }
}

class Util
{
    public static function microtime()
    {
        return microtime(true);
    }
}

class Formatter
{
    public static function bigint($num)
    {
        return number_format($num, 0);
    }
}

abstract class Benchmark
{
    protected $name      = 'unknown test';
    protected $result    = null;
    protected $units     = null;
    protected $reference = null;
    
    function start(){}
    function nextIteration(){}
    function finish(){}
    
    public function setReference($ref)
    {
        $this->reference = $ref;
    }
    
    public function getName()
    {
        return $this->name;
    }
    
    public function getResult()
    { 
        return $this->result;
    }
    
    public function getScore()
    {
        return $this->reference ? round($this->result / $this->reference, 1) : null;
    }
    
    public function getUnits()
    {
        return $this->units;
    }
}

class TestUserFunction extends Benchmark
{
    protected $name  = 'Call user function';
    protected $units = 'call/sec';
    
    private $calls = 0;
    private $time  = 0;
    
    public function start()
    {
        if ( !function_exists('userfunction') ) {
            function userfunction(){return true;}
        }
    }
    
    public function nextIteration()
    {
        $start = Util::microtime();
        
        $i = 1000;
        do {
            userfunction();
        } while (--$i);
        
        $this->time  += Util::microtime() - $start;
        $this->calls += 1000;
    }
    
    public function finish()
    {
        $this->result = round($this->calls / $this->time);
    }
}

class TestCallClosure extends Benchmark
{
    protected $name  = 'Call closure';
    protected $units = 'call/sec';

    private $calls    = 0;
    private $time     = 0;
    private $function = null;

    public function start()
    {
        $this->function = eval('return function() { return true; };');
    }

    public function nextIteration()
    {
        $start = Util::microtime();
        $func = $this->function;
        
        $i = 1000;
        do {
            $func();
        } while (--$i);

        $this->time  += Util::microtime() - $start;
        $this->calls += 1000;
    }

    public function finish()
    {
        $this->result = round($this->calls / $this->time);
    }
}

class TestArrayIteration extends Benchmark
{
    protected $name  = 'Foreach array';
    protected $units = 'iter/sec';

    private $calls = 0;
    private $time  = 0;
    private $array = null;

    public function start()
    {
        $this->array = array_fill(0, 1e3, 'hello');
    }
    
    public function nextIteration()
    {
        $start = Util::microtime();

        foreach ( $this->array as $key => $value ) {
            ;
        }

        $this->time  += Util::microtime() - $start;
        $this->calls += count($this->array);
    }

    public function finish()
    {
        $this->array = null;
        $this->result = round($this->calls / $this->time);
    }
}

class TestArrayIterationArrayWalk extends Benchmark
{
    protected $name  = 'Use array_walk';
    protected $units = 'iter/sec';

    private $calls = 0;
    private $time  = 0;
    private $array = null;

    public function start()
    {
        $this->array = array_fill(0, 1e3, 'hello');
    }
    
    public function test()
    {
    }

    public function nextIteration()
    {
        $start = Util::microtime();

        array_walk($this->array, array($this, 'test'));

        $this->time  += Util::microtime() - $start;
        $this->calls += count($this->array);
    }

    public function finish()
    {
        $this->array = null;
        $this->result = round($this->calls / $this->time);
    }
}

class TestCreateObjects extends Benchmark
{
    protected $name  = 'Create empty object';
    protected $units = 'obj/sec';

    private $calls = 0;
    private $time  = 0;
    
    public function nextIteration()
    {
        $start = Util::microtime();

        $i = 1000;
        do {
            $obj = new stdClass();
        } while (--$i);

        $this->time  += Util::microtime() - $start;
        $this->calls += 1000;
    }

    public function finish()
    {
        $this->result = round($this->calls / $this->time);
    }
}

class TestCallObjFunc extends Benchmark
{
    protected $name  = 'Call object function';
    protected $units = 'call/sec';
    
    private $calls = 0;
    private $time  = 0;

    private function foo()
    {
        return true;
    }

    public function nextIteration()
    {
        $start = Util::microtime();

        $i = 1000;
        do {
            $this->foo();
        } while (--$i);

        $this->time  += Util::microtime() - $start;
        $this->calls += 1000;
    }
    
    public function finish()
    {
        $this->result = round($this->calls / $this->time);
    }
}

class TestIterateObj extends Benchmark
{
    protected $name  = 'Foreach simple object';
    protected $units = 'iter/sec';

    private $calls = 0;
    private $time  = 0;
    private $obj   = null;

    public function start()
    {
        $obj = new stdClass();
        
        for($i = 0; $i < 1e3; ++$i) {
            $obj->{"prop$i"} = 'Hello!';
        }
        
        $this->obj = $obj;
    }
    
    public function nextIteration()
    {
        $start = Util::microtime();

        $i = 1000;
        do {
            foreach ( $this->obj as $key => $value ) {
                ++$this->calls;
            }
        } while (--$i);

        $this->time += Util::microtime() - $start;
    }

    public function finish()
    {
        $this->result = round($this->calls / $this->time);
    }
}

class TestIterateArrayObj extends Benchmark
{
    protected $name  = 'Foreach array object';
    protected $units = 'iter/sec';

    private $calls = 0;
    private $time  = 0;
    private $obj   = null;

    public function start()
    {
        $this->obj = new ArrayObject(array_fill(0, 1e3, 'hello'));
    }
    
    public function nextIteration()
    {
        $start = Util::microtime();
 
        foreach ( $this->obj as $key => $value ) {
            ++$this->calls;
        }

        $this->time += Util::microtime() - $start;
    }

    public function finish()
    {
        $this->result = round($this->calls / $this->time);
    }
}

class TestSimpleCalc extends Benchmark
{
    protected $name  = 'Simple math';
    protected $units = 'oper/sec';

    private $calls = 0;
    private $time  = 0;
    
    public function nextIteration()
    {
        $start = Util::microtime();

        $i = 10000;
        do {
            $result = 12 * 13 / 10 + 20 / (0.5 * 10) % 3;
        } while (--$i);

        $this->time  += Util::microtime() - $start;
        $this->calls += 10000;
    }

    public function finish()
    {
        $this->result = round($this->calls / $this->time);
    }
}

class TestStringConcat extends Benchmark
{
    protected $name  = 'String concat';
    protected $units = 'oper/sec';

    private $calls = 0;
    private $time  = 0;
    
    public function nextIteration()
    {
        $start = Util::microtime();

        $str = '';
        
        $i = 1000;
        do {
            $str .= 'abc';
        } while (--$i);

        $this->time  += Util::microtime() - $start;
        $this->calls += 1000;
    }

    public function finish()
    {
        $this->result = round($this->calls / $this->time);
    }
}

class TestArrayCombine extends Benchmark
{
    protected $name  = 'Array + array';
    protected $units = 'oper/sec';

    private $calls = 0;
    private $time  = 0;
    
    private $a = null;
    private $b = null;

    public function start()
    {
        $this->a = array(
            'AAAAAAAA'=> 'AAAAAAAAAAAAAAAAAAAA',
            'BBBBBBBB'=> 'BBBBBBBBBBBBBBBBBBBB',
            'CCCCCCCC'=> 'CCCCCCCCCCCCCCCCCCCC',
            'DDDDDDDD'=> 'DDDDDDDDDDDDDDDDDDDD',
            'EEEEEEEE'=> 'EEEEEEEEEEEEEEEEEEEE',
            'FFFFFFFF'=> 'FFFFFFFFFFFFFFFFFFFF',
            'GGGGGGGG'=> 'GGGGGGGGGGGGGGGGGGGG',
            'HHHHHHHH'=> 'HHHHHHHHHHHHHHHHHHHH',
            'IIIIIIII'=> 'IIIIIIIIIIIIIIIIIIII',
            'JJJJJJJJ'=> 'JJJJJJJJJJJJJJJJJJJJ',
            'KKKKKKKK'=> 'KKKKKKKKKKKKKKKKKKKK',
            'LLLLLLLL'=> 'LLLLLLLLLLLLLLLLLLLL',
            'MMMMMMMM'=> 'MMMMMMMMMMMMMMMMMMMM',
            'NNNNNNNN'=> 'NNNNNNNNNNNNNNNNNNNN',
        );
        
        $this->b = array(
            'JJJJJJJJ'=> 'JJJJJJJJJJJJJJJJJJJJ',
            'KKKKKKKK'=> 'KKKKKKKKKKKKKKKKKKKK',
            'LLLLLLLL'=> 'LLLLLLLLLLLLLLLLLLLL',
            'MMMMMMMM'=> 'MMMMMMMMMMMMMMMMMMMM',
            'NNNNNNNN'=> 'NNNNNNNNNNNNNNNNNNNN',
            'OOOOOOOO'=> 'OOOOOOOOOOOOOOOOOOOO',
            'PPPPPPPP'=> 'PPPPPPPPPPPPPPPPPPPP',
            'QQQQQQQQ'=> 'QQQQQQQQQQQQQQQQQQQQ',
            'RRRRRRRR'=> 'RRRRRRRRRRRRRRRRRRRR',
            'SSSSSSSS'=> 'SSSSSSSSSSSSSSSSSSSS',
            'TTTTTTTT'=> 'TTTTTTTTTTTTTTTTTTTT',
            'UUUUUUUU'=> 'UUUUUUUUUUUUUUUUUUUU',
            'VVVVVVVV'=> 'VVVVVVVVVVVVVVVVVVVV',
            'WWWWWWWW'=> 'WWWWWWWWWWWWWWWWWWWW',
            'XXXXXXXX'=> 'XXXXXXXXXXXXXXXXXXXX',
            'YYYYYYYY'=> 'YYYYYYYYYYYYYYYYYYYY',
            'ZZZZZZZZ'=> 'ZZZZZZZZZZZZZZZZZZZZ',
        );
    }

    public function nextIteration()
    {
        $start = Util::microtime();

        $i = 1000;
        do {
            $c = $this->a + $this->b;
        } while (--$i);

        $this->time  += Util::microtime() - $start;
        $this->calls += 1000;
    }

    public function finish()
    {
        $this->result = round($this->calls / $this->time);
    }
}

class TestArrayMerge extends Benchmark
{
    protected $name  = 'Use array_merge';
    protected $units = 'oper/sec';

    private $calls = 0;
    private $time  = 0;
    
    private $a = null;
    private $b = null;

    public function start()
    {
        $this->a = array(
            'AAAAAAAA'=> 'AAAAAAAAAAAAAAAAAAAA',
            'BBBBBBBB'=> 'BBBBBBBBBBBBBBBBBBBB',
            'CCCCCCCC'=> 'CCCCCCCCCCCCCCCCCCCC',
            'DDDDDDDD'=> 'DDDDDDDDDDDDDDDDDDDD',
            'EEEEEEEE'=> 'EEEEEEEEEEEEEEEEEEEE',
            'FFFFFFFF'=> 'FFFFFFFFFFFFFFFFFFFF',
            'GGGGGGGG'=> 'GGGGGGGGGGGGGGGGGGGG',
            'HHHHHHHH'=> 'HHHHHHHHHHHHHHHHHHHH',
            'IIIIIIII'=> 'IIIIIIIIIIIIIIIIIIII',
            'JJJJJJJJ'=> 'JJJJJJJJJJJJJJJJJJJJ',
            'KKKKKKKK'=> 'KKKKKKKKKKKKKKKKKKKK',
            'LLLLLLLL'=> 'LLLLLLLLLLLLLLLLLLLL',
            'MMMMMMMM'=> 'MMMMMMMMMMMMMMMMMMMM',
            'NNNNNNNN'=> 'NNNNNNNNNNNNNNNNNNNN',
        );
        
        $this->b = array(
            'JJJJJJJJ'=> 'JJJJJJJJJJJJJJJJJJJJ',
            'KKKKKKKK'=> 'KKKKKKKKKKKKKKKKKKKK',
            'LLLLLLLL'=> 'LLLLLLLLLLLLLLLLLLLL',
            'MMMMMMMM'=> 'MMMMMMMMMMMMMMMMMMMM',
            'NNNNNNNN'=> 'NNNNNNNNNNNNNNNNNNNN',
            'OOOOOOOO'=> 'OOOOOOOOOOOOOOOOOOOO',
            'PPPPPPPP'=> 'PPPPPPPPPPPPPPPPPPPP',
            'QQQQQQQQ'=> 'QQQQQQQQQQQQQQQQQQQQ',
            'RRRRRRRR'=> 'RRRRRRRRRRRRRRRRRRRR',
            'SSSSSSSS'=> 'SSSSSSSSSSSSSSSSSSSS',
            'TTTTTTTT'=> 'TTTTTTTTTTTTTTTTTTTT',
            'UUUUUUUU'=> 'UUUUUUUUUUUUUUUUUUUU',
            'VVVVVVVV'=> 'VVVVVVVVVVVVVVVVVVVV',
            'WWWWWWWW'=> 'WWWWWWWWWWWWWWWWWWWW',
            'XXXXXXXX'=> 'XXXXXXXXXXXXXXXXXXXX',
            'YYYYYYYY'=> 'YYYYYYYYYYYYYYYYYYYY',
            'ZZZZZZZZ'=> 'ZZZZZZZZZZZZZZZZZZZZ',
        );
    }

    public function nextIteration()
    {
        $start = Util::microtime();

        $i = 1000;
        do {
            $c = array_merge($this->a, $this->b);
        } while (--$i);

        $this->time  += Util::microtime() - $start;
        $this->calls += 1000;
    }

    public function finish()
    {
        $this->result = round($this->calls / $this->time);
    }
}

class TestFunctionIsNull extends Benchmark
{
    protected $name  = 'Function is_null';
    protected $units = 'call/sec';

    private $calls = 0;
    private $time  = 0;

    public function nextIteration()
    {
        $start = Util::microtime();

        $i = 1000;
        do {
            is_null($i);
        } while (--$i);

        $this->time  += Util::microtime() - $start;
        $this->calls += 1000;
    }

    public function finish()
    {
        $this->result = round($this->calls / $this->time);
    }
}

class TestFunctionEmpty extends Benchmark
{
    protected $name  = 'Function empty';
    protected $units = 'call/sec';

    private $calls = 0;
    private $time  = 0;

    public function nextIteration()
    {
        $start = Util::microtime();

        $i = 1000;
        do {
            empty($i);
        } while (--$i);

        $this->time  += Util::microtime() - $start;
        $this->calls += 1000;
    }

    public function finish()
    {
        $this->result = round($this->calls / $this->time);
    }
}

class TestFunctionIsset extends Benchmark
{
    protected $name  = 'Function isset';
    protected $units = 'call/sec';

    private $calls = 0;
    private $time  = 0;

    public function nextIteration()
    {
        $start = Util::microtime();

        $i = 1000;
        do {
            isset($i);
        } while (--$i);

        $this->time  += Util::microtime() - $start;
        $this->calls += 1000;
    }

    public function finish()
    {
        $this->result = round($this->calls / $this->time);
    }
}

class TestFunctionRand extends Benchmark
{
    protected $name  = 'Function rand';
    protected $units = 'call/sec';

    private $calls = 0;
    private $time  = 0;

    public function nextIteration()
    {
        $start = Util::microtime();

        $i = 1000;
        do {
            rand();
        } while (--$i);

        $this->time  += Util::microtime() - $start;
        $this->calls += 1000;
    }

    public function finish()
    {
        $this->result = round($this->calls / $this->time);
    }
}
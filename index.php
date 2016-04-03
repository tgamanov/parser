<?php

//use testnamespace\Foo;
use Sunra\PhpSimple\HtmlDomParser;

$loader = require __DIR__ . '/vendor/autoload.php';

header("Content-type:text/html; charset=utf-8");
/** @var  simple_html_dom $dom */


$servername = "localhost";
$dbname = "scotchbox";
$username = "root";
$password = "root";

try {
    $conn = new PDO(
        "mysql:host=$servername;dbname=$dbname",
        $username,
        $password,
        array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8")
    );
    // set the PDO error mode to exception
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $str = file_get_contents('https://myshows.me/view/187/');//change the link to parse another page
    $dom = HtmlDomParser::str_get_html($str);

    $str = ($dom->find('h1', 0)->plaintext);
    $str = preg_replace('!\s+!', ' ', $str);//removing multispaces
    $parts = explode(':',$str);
//    echo $parts[0];die;
    $sql = "insert into myshows
            set line = 'Название',
                val = '{$parts[0]}'
                ";
    $conn->exec($sql);
    echo "Название " . $parts[0] . " has been added to myshows table <br>";

    for ($i = 0; $i <= 9; $i++) {
        $str = ($dom->find('.clear p', $i)->plaintext);
        $str = preg_replace('!\s+!', ' ', $str);
        $parts = explode(':',$str);
        $parts[1]=preg_replace('/&#?[a-z0-9]+;/i',' ',$parts[1]);
        $parts[1]=preg_replace('!\s+!',' ',$parts[1]);
        $sql = "INSERT INTO myshows
                SET line = '{$parts[0]}',
                    val = '{$parts[1]}'";
        $conn->exec($sql);
        echo $parts[0] . " has been added to myshows.line ".$parts[1]." to val <br>";
    }

// Create DOM from fs.to

    $html = file_get_contents('http://fs.to/video/serials/iw8ypXjXXom7QpgYXsvU2Y-vo-vse-tyazhkije.html');
    $dom = HtmlDomParser::str_get_html($html);
    foreach($dom->find('.item-info tr') as $elm) {
        $str = $elm->plaintext;
        $str = preg_replace('!\s+!', ' ', $str);
        $parts = explode(':',$str);
        $sql = "INSERT INTO fs
                SET line = '{$parts[0]}',
                    val = '{$parts[1]}'";
        $conn->exec($sql);
        echo $parts[0] . " has been added to fs.line ".$parts[1]." to val <br>";
    }

    $sql = "INSERT INTO result
            SELECT * FROM fs
            UNION 
            SELECT * FROM myshows
            ";
    $conn->exec($sql);
    $sql = "DELETE FROM result
            WHERE line = 'Жанры'";
    $conn->exec($sql);
    $sql = "DELETE FROM result
            WHERE line = 'Страна'";
    $conn->exec($sql);
} catch (PDOException $e) {
    echo $sql . "<br>" . $e->getMessage();
}
$conn = null;


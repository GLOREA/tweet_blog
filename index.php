<?php
    require_once __DIR__ . "/lib/autoload.php";
    use Glorea\TweetBlog\Config;
    use Glorea\TweetBlog\Tweet;
    use Glorea\TweetBlog\TweetAnalyzer;
    use Glorea\TweetBlog\TweetDate;
?><!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <title><?php print Config::site()->title; ?></title>
    </head>
    <body>
        <header>
        </header>
        <navi id='main'>
        </navi>
        <main>
<?php
    $ta = new TweetAnalyzer(true);
    $tweets = $ta->get_tweets(['count' => 1]);
    var_dump($tweets);

    $dates = TweetDate::order_by_desc('tweet_date')->limit(10)->find_many();
    var_dump(
        Tweet::fetch_tweets(array_pop($dates), array_shift($dates))
    );
 ?>
        </main>
        <navi id='side'>
        </navi>
        <footer></footer>
    </body>
</html>

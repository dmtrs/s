<?php
$config = require_once __DIR__.'/../config/main.php';
//functions
function get($url, $params) {
    $ch = curl_init();
    $url = $url.'?'.http_build_query($params);
    error_log($url);

    curl_setopt($ch, CURLOPT_URL, $url );

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_TIMEOUT, '3');
    $content = curl_exec($ch);
    curl_close($ch);
    return $content;
}
//applcation
require __DIR__.'/../lib/slim/Slim/Slim.php';
\Slim\Slim::registerAutoloader();

$app = new \Slim\Slim();
$app->setName('Web application');

$app->get('/', function () use ($app) {
    $app->render('layout.php', array(
        'name'  => $app->getName(),
    ));
});

$app->get('/:blog', function($blog) use ($app) {
    $r = $app->response();
    $r['Content-Type'] = 'application/json';

    $source = __DIR__.'/../data/'.$blog.'.json';
    if(!isset($_GET['debug']) && file_exists($source)) {
        $content = file_get_contents($source);
    } else {
        $content = get('http://api.tumblr.com/v2/blog/'.$blog.'/posts', array(
            'api_key'     => 'fuiKNFp9vQFvjLNvx4sUwti4Yb5yGutBN4Xh10LXZhhRKjWlV4',
            'notes_info'  => 'true',
            'reblog_info' => 'true',
        ));
        file_put_contents($source, $content);
    }
    $provider = (object)json_decode($content);
    
    $provider = $provider->response;

    $posts = $provider->posts;

    echo json_encode(array_map(function($p) {
        $post = (object)array(
            'id'          => $p->id,
            'blog_name'   => $p->blog_name,
            'notes_count' => $p->note_count,
            'timestamp'   => $p->timestamp,
        );
        
        if(isset($p->notes)) {
            $post->reblogs = array_map(function($reblog) use($post) {
                $r = array(
                    'id' => $reblog->post_id,
                    'blog_name' => $reblog->blog_name,
                    'timestamp' => $reblog->timestamp,
                    'url'       => implode('/',array('http://staging.stamblr.com', $post->blog_name, $reblog->post_id)),
                );
                return $r;
            }, array_filter($p->notes,function($note) {
                return $note->type=='reblog';
            }));
        }
        return $post;
    }, $provider->posts));
})->conditions(array(
    'blog' => '\w+\.tumblr\.com'
));

$app->run();

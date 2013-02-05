<?php
$config = require_once __DIR__.'/../config/main.php';
//applcation
require __DIR__.'/../lib/slim/Slim/Slim.php';
\Slim\Slim::registerAutoloader();

$app = new \Slim\Slim();
$app->config($config);

$app->setName('Web application');

$app->get('/', function () use ($app) {
    $app->render('layout.php', array(
        'name'  => $app->getName(),
    ));
});

$app->get('/:blog(/:post)', function($blog, $post=null) use ($app) {
    $r = $app->response();
    $r['Content-Type'] = 'application/json';

    echo json_encode(retrieve($blog, $post, $app->config('components.tumblr.api_key')));
})->conditions(array('blog' => '\w+\.tumblr\.com'));

$app->run();
//functions
function get($url, $params=array(), $api_key=null) {
    
    $ch = curl_init();
    $url = $url.'?'.http_build_query($params);

    curl_setopt($ch, CURLOPT_URL, $url );
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_TIMEOUT, '3');

    $content = curl_exec($ch);
    curl_close($ch);
    return $content;
}

function retrieve($blog, $post=null, $api_key=null) {
    $source = __DIR__.'/../data/'.$blog.'.'.(($post!==null)?$post:'').'.json';
    if(!isset($_GET['debug']) && file_exists($source)) {
        $content = file_get_contents($source);
    } else {
        $params = array(
            'notes_info'  => 'true',
            'reblog_info' => 'true',
        );
        if($api_key!==null)
            $params['api_key'] = $api_key;

        if($post!==null)
            $params['id']=$post;
        
        $content = get('http://api.tumblr.com/v2/blog/'.$blog.'/posts', $params);
        file_put_contents($source, $content);
    }
    $provider = (object)json_decode($content);
    
    $provider = $provider->response;

    $posts = $provider->posts;

    return array_map(function($p) {
        $post = (object)array(
            'id'          => $p->id,
            'blog_name'   => $p->blog_name,
            'notes_count' => $p->note_count,
            'timestamp'   => $p->timestamp,
            'url'         => implode('/',array('http://staging.stamblr.com', $p->blog_name.'.tumblr.com', $p->id)),
        );
        
        if(isset($p->notes)) {
            $post->reblogs = array_map(function($reblog) use($post) {
                $r = array(
                    'id' => $reblog->post_id,
                    'blog_name' => $reblog->blog_name,
                    'timestamp' => $reblog->timestamp,
                    'url'       => implode('/',array('http://staging.stamblr.com', $reblog->blog_name.'.tumblr.com', $reblog->post_id)),
                );
                return $r;
            }, array_filter($p->notes,function($note) {
                return $note->type=='reblog';
            }));
        }
        return $post;
    }, $provider->posts);
}

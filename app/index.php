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
function get($url, $params=array(), $api_key=null)
{
    
    $ch = curl_init();
    $url = $url.'?'.http_build_query($params);
    
    curl_setopt($ch, CURLOPT_URL, $url );
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_TIMEOUT, '30');

    $content = curl_exec($ch);
    curl_close($ch);
    return $content;
}

function formatPost($post)
{
    $r = (object)array(
        'blog_name'   => $post->blog_name,
        'timestamp'   => $post->timestamp,
    );
    $r->id  = (isset($post->id)) ? $post->id : $post->post_id;
    $r->url = implode('/',array('http://staging.stamblr.com', $post->blog_name.'.tumblr.com', $r->id));

    $r->notes_count = (isset($post->note_count)) ? $post->note_count : null;
    $r->reblog_key  = (isset($post->reblog_key)) ? $post->reblog_key : null;

    return $r;
}

function retrieve($blog, $post=null, $api_key=null)
{
    $source = __DIR__.'/../data/'.$blog.(($post!==null) ? '.'.$post:'').'.json';
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
    $json = (object)json_decode($content);

    return array_map(function($p) {
        $post = formatPost($p);

        if(isset($p->notes)) {
            foreach($p->notes as $reblog)
            {
                if($reblog->type=='reblog')
                    $post->reblogs[] = formatPost($reblog);
            }
        }
        return $post;
    }, $json->response->posts);
}

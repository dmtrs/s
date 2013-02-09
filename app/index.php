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
    echo json_encode(array_map(function($post) {
        return format($post);
    }, posts($blog, $post, $app->config('components.tumblr.api_key'))
    ));
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

function format($post, $with_notes=true)
{
    $r = (object)array(
        'blog_name'   => $post->blog_name,
        'timestamp'   => $post->timestamp,
    );
    $r->id  = (isset($post->id)) ? $post->id : $post->post_id;
    $r->url = url($post->blog_name, $r->id);

    if(isset($post->note_count))
        $r->notes_count =  $post->note_count;

    if(isset($post->reblog_key))
        $r->reblog_key  =  $post->reblog_key;

    //reblog info
    if(isset($post->reblogged_from_id)) {
        $r->from_id  = $post->reblogged_from_id;
        $r->from_blog_name = $post->reblogged_from_name;
        $r->from_url = url($r->from_blog_name, $r->from_id);

        //"reblogged_root_url": "http://whiteshoe.tumblr.com/post/41319011308/asap-rocky-margiela-kenzo-paris-paul-smith",
        $r->root_id        = 'TBD';
        $r->root_blog_name = $post->reblogged_root_name;

        $r->root_url = url($r->root_blog_name, $r->root_id);;
    }
    //reblogs
    if($with_notes && isset($post->notes)) {
        foreach($post->notes as $reblog)
        {
            if($reblog->type=='reblog')
                $r->reblogs[] = format($reblog);
        }
    }

    return $r;
}

function url($name, $id)
{
    $parts = array(
        'http://staging.stamblr.com',
        $name.'.tumblr.com'
    );

    if($id!==null)
        $parts[] = $id;

    return implode('/', $parts);
}

function posts($blog, $post=null, $api_key=null)
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
    return $json->response->posts;
}

<?php

// Installation de Composer
// Installation de Silex grâce à Composer dans le dossier voulu
// Installation de Doctrine grâce à Composer dans le dossier voulu

require_once __DIR__.'/../vendor/autoload.php';

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

$app = new Silex\Application();

$app['debug'] = true;

$app->register(new Silex\Provider\DoctrineServiceProvider(), array(
    'db.options' => array (
            'driver'    => 'pdo_mysql',
            'host'      => 'localhost',
            'dbname'    => 'social-voyage',
            'user'      => 'root',
            'password'  => '',
            'charset'   => 'utf8mb4',
    ),
));



// Article



// Voir ce qui a dans la BDD
$app->get('/api/blog', function () use ($app) {
    $sql = "SELECT * FROM article";
    $post = $app['db']->fetchAll($sql);

    return  json_encode($post, JSON_UNESCAPED_UNICODE);
});

//Voir les derniers articles
$app->get('/api/blog/lastUpdate', function () use ($app) {
    $sql = "SELECT * FROM article ORDER BY dateUpdate DESC LIMIT 3";
    $post = $app['db']->fetchAll($sql);

    return  json_encode($post, JSON_UNESCAPED_UNICODE);
});

// Voir un élément de la BDD juste avec un id 
// Par exemple : http://localhost/social_voyage/web/index.php/api/blog/1
// Pour voir l'article avec l'id n°1
$app->get('/api/blog/{id}', function ($id) use ($app) {
    $sql = "SELECT * FROM post WHERE id = ?";
    $post = $app['db']->fetchAssoc($sql, array((int) $id));

    // $sql = "SELECT COUNT(id) as nbCommentaire FROM commentaire WHERE idArticle = ?";
    // $post = $app['db']->fetchAssoc($sql, array((int) $id));

    return  json_encode($post, JSON_UNESCAPED_UNICODE);
});

// Insérer dans la BDD
$app->post('/api/post', function (Request $request) use ($app){
    
    $titre = $request->get('titre');
    $texte = $request->get('texte');
    $mini_texte = $request->get('mini_texte');
    $auteur = $request->get('auteur');
    $urlImage = $request->get('urlImage');
  
    $app['db']->insert('article', array(
        'titre' => $titre,
        'texte' => $texte,
        'mini_texte' => $mini_texte,
        'auteur' => $auteur,
        'urlImage' => $urlImage,
    ));

    // Pour voir ce qui a été inséré avec le dernier id inséré
    $id = $app['db']->lastInsertId();
    $sql = "SELECT * FROM article WHERE id = ?";
    $post = $app['db']->fetchAssoc($sql, array((int) $id));
    return json_encode($post, JSON_UNESCAPED_UNICODE);
});

// Modifier dans la BDD
$app->post('/api/post/{id}', function (Request $request, $id) use ($app){
    
    $sql = "SELECT * FROM article WHERE id = ?";
    $post = $app['db']->fetchAssoc($sql, array((int) $id));

    $post['titre'] = $request->get('titre');
    $post['texte'] = $request->get('texte');
    $post['mini_texte'] = $request->get('mini_texte');
    $post['auteur'] = $request->get('auteur');
    $post['urlImage'] = $request->get('urlImage');
    date_default_timezone_set('Europe/Paris');
    $dateUpdate = date("Y-m-d H:i:s");
  
    $sql = "UPDATE article SET titre = ?, texte = ?, mini_texte = ?, auteur = ?, urlImage = ?, dateUpdate = ? WHERE id = ?";
    $app['db']->executeUpdate($sql, array(
        $post['titre'],
        $post['texte'],
        $post['mini_texte'],
        $post['auteur'],
        $post['urlImage'],
        $dateUpdate,
        (int) $id
    ));

    // Pour voir ce qui a été inséré
    $sql = "SELECT * FROM article WHERE id = ?";
    $post = $app['db']->fetchAssoc($sql, array((int) $id));
    return json_encode($post, JSON_UNESCAPED_UNICODE);
});


// Supprimer un article
$app->get('/api/blog/{id}/delete', function ($id) use ($app) {
    $sql = "DELETE FROM article WHERE id = ?";
    $post = $app['db']->executeQuery($sql, array((int) $id));

    return  json_encode(true);
});





// Commentaire



// Voir les commentaires d'un article juste avec un id 
// Par exemple : http://localhost/social_voyage/web/index.php/api/blog/1/comment
// Pour voir les commentaires de l'article avec l'id n°1
$app->get('/api/blog/{id}/comment', function ($id) use ($app) {
    $sql = "SELECT * FROM commentaire WHERE idArticle = ?";
    $comment = $app['db']->fetchAll($sql, array((int) $id));

     return json_encode($comment, JSON_UNESCAPED_UNICODE);
});

$app->get('/api/blog/{idA}/comment/{idC}', function ($idA, $idC) use ($app) {
    $sql = "SELECT * FROM commentaire WHERE idArticle = :idA AND id = :idC";
    $comment = $app['db']->fetchAssoc($sql, array('idA' => (int) $idA, 'idC' => (int) $idC));

     return json_encode($comment, JSON_UNESCAPED_UNICODE);
});

// Mettre un commentaire dans un article
$app->post('/api/post/{id}/comment', function (Request $request, $id) use ($app){
    $idArticle = $id;
    $texte = $request->get('texte');
    $auteur = $request->get('auteur');

    $app['db']->insert('commentaire', array(
        'idArticle' => $idArticle,
        'texte' => $texte,
        'auteur' => $auteur,
    ));

    // Pour voir ce qui a été inséré avec le dernier id inséré
    $id = $app['db']->lastInsertId();
    $sql = "SELECT * FROM commentaire WHERE id = ?";
    $post = $app['db']->fetchAssoc($sql, array((int) $id));
    return json_encode($post, JSON_UNESCAPED_UNICODE);
});

// Modifier dans la BDD
$app->post('/api/post/{idA}/comment/{idC}', function (Request $request, $idA, $idC) use ($app){
    
    $sql = "SELECT * FROM commentaire WHERE idArticle = :idA AND id = :idC";
    $post = $app['db']->fetchAssoc($sql, array('idA' => (int) $idA, 'idC' => (int) $idC));

    $post['idArticle'] = $request->get('idArticle');
    if($post['idArticle'] == null){ $post['idArticle'] = (int) $idA;}
    $post['texte'] = $request->get('texte');
    $post['auteur'] = $request->get('auteur'); 

  
    $sql = "UPDATE commentaire SET idArticle = ?, texte = ?, auteur = ? WHERE id = ?";
    $app['db']->executeUpdate($sql, array(
        $post['idArticle'],
        $post['texte'],
        $post['auteur'],
        (int) $idC
    ));

    // Pour voir ce qui a été inséré
    $sql = "SELECT * FROM commentaire WHERE id = ?";
    $post = $app['db']->fetchAssoc($sql, array((int) $idC));
    return json_encode($post, JSON_UNESCAPED_UNICODE);
});
/*
$app->get('/api/blog/{id}/delete', function ($id) use ($app) {
    $sql = "DELETE FROM article WHERE id = ?";
    $post = $app['db']->executeQuery($sql, array((int) $id));

    return  json_encode(true);
});
*/


// Like

// Voir ce qui a dans la BDD
$app->get('/api/blog/{idA}/comment/{idC}/like', function ($idA, $idC) use ($app) {
    $sql = "SELECT * FROM aime WHERE idArticle = :idA AND idCommentaire = :idC";
    $aime = $app['db']->fetchAll($sql, array('idA' => (int) $idA, 'idC' => (int) $idC));

     return json_encode($aime, JSON_UNESCAPED_UNICODE);
});

//////////////////////////////////////////////////////////////////
$app->get('/api/blog/{idA}/getlikes', function ($idA) use ($app) {
    
    // Récupérer les id commenaires
    $sql = "SELECT id FROM commentaire WHERE idArticle = :idA";
    $idComs = $app['db']->fetchAll($sql, array('idA' => (int) $idA));

    // Pour chauqe id commentaire récupérer le nombre de like
    foreach($idComs as $key => $com) {
        $sql = "SELECT COUNT(*) as nbTotal FROM aime WHERE idCommentaire = :idC";
        $nbLikes[$com['id']] = $app['db']->fetchAssoc($sql, array('idC' => (int) $com['id']));
        
    }
return json_encode($nbLikes);
     // var_dump($idComs, $nbLikes);
    
   // 
    // Renvoyer le tableau tab[id_commentaire] => nombre

    /*$sql = "SELECT COUNT(*) FROM aime WHERE idCommentaire =? ";
    $nbLikes = $app['db']->fetchAll($sql, $like);
    return json_encode($nbLikes, JSON_UNESCAPED_UNICODE);*/
    
    //$aime = $app['db']->fetchAssoc($sql, array('idA' => (int) $idA, 'idC' => (int) $idC));

    // return json_encode($aime['nbTotal'], JSON_UNESCAPED_UNICODE);
});

// Modifier dans la BDD
$app->post('/api/post/{idA}/comment/{idC}/like', function (Request $request, $idA, $idC) use ($app){

    $idArticle = $idA;
    $idCommentaire = $idC;

    $app['db']->insert('aime', array(
        'idArticle' => $idArticle,
        'idCommentaire' => $idCommentaire,
    ));

    // Pour voir ce qui a été inséré
    $sql = "SELECT * FROM aime WHERE idArticle = :idA AND idCommentaire = :idC";
    $post = $app['db']->fetchAssoc($sql, array('idA' => (int) $idArticle, 'idC' => (int) $idCommentaire));
    return json_encode($post, JSON_UNESCAPED_UNICODE);
});
$app->run();
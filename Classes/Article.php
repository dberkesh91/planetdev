<?php
class Article {

  private static $_articlesTable = 'articles',
                 $_authorsTable = 'authors',

                 $_articleIdentifier = 'article_id',
                 $_authorIdentifier = 'author_id';

  public static function getAll(){
    $articlesTable = self::$_articlesTable;
    $authorsTable = self:: $_authorsTable;
    $authorIdentifier = self:: $_authorIdentifier;

    $articles = DB::getInstance()
                ->query(
                  "SELECT * FROM {$articlesTable}
                  INNER JOIN {$authorsTable}
                  ON {$articlesTable}.{$authorIdentifier} = {$authorsTable}.{$authorIdentifier}"
                );

    $result = $articles ? $articles : false;
    return $result;
  }

  public static function getById($id){
    $table = self::$_articlesTable;
    $authorsTable = self:: $_authorsTable;
    $authorIdentifier = self:: $_authorIdentifier;

    $article = DB::getInstance()->get($table, array('article_id', '=', $id));

    $result = $article ? $article : false;
    return $result;
  }
}
 ?>

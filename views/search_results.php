<?php

use Coco\Infra\View;

if (!defined("CMSIMPLE_XH_VERSION")) {header("HTTP/1.0 403 Forbidden"); exit;}

/**
 * @var View $this
 * @var string $search_term
 * @var list<array{heading:string,url:string}> $pages
 */
?>
<!-- coco search results -->
<h1><?=$this->text("search_result")?></h1>
<p><?=$this->plural("search_found", count($pages), $search_term)?></p>
<?if (!empty($pages)):?>
<ul>
<?  foreach ($pages as $page):?>
  <li><a href="<?=$page['url']?>"><?=$page['heading']?></a></li>
<?  endforeach?>
</ul>
<?endif?>

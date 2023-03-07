<?php

use Coco\Infra\View;

/**
 * @var View $this
 * @var string $search_term
 * @var list<array{heading:string,url:string}> $pages
 */

?>
<!-- coco search results -->
<h1><?=$this->text("search_result")?></h1>
<p><?=$this->plural("search_found", count($pages), $search_term)?></p>
<?php if (!empty($pages)):?>
<ul>
<?php   foreach ($pages as $page):?>
    <li><a href="<?=$page['url']?>"><?=$page['heading']?></a></li>
<?php   endforeach?>
</ul>
<?php endif?>

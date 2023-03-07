<?php

use Coco\Infra\View;

/**
 * @var View $this
 * @var string $sn
 * @var list<string> $cocos
 */
?>
<!-- coco administration -->
<h1>Coco – <?=$this->text('menu_main')?></h1>
<form id="coco_admin_cocos" action="<?=$sn?>" method="get">
  <input type="hidden" name="selected" value="coco"/>
  <input type="hidden" name="admin" value="plugin_main"/>
  <table>
    <tr>
      <th></th>
      <th>Coco</th>
    </tr>
<?foreach ($cocos as $coco):?>
    <tr>
      <td><input type="checkbox" id="coco_name_<?=$coco?>" name="coco_name[<?=$coco?>]"></td>
      <td><label for="coco_name_<?=$coco?>"><?=$coco?></label></td>
    </tr>
<?endforeach?>
  </table>
  <p><button name="action" value="delete"><?=$this->text('label_delete')?></button></p>
</form>

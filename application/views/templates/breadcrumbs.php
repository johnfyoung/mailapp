<?php
/**
 * breadcrumbs.php
 *
 * @package mailapp
 * @author johny
 * @copyright Copyright (c) 2016, codeandcreative
 */

defined('BASEPATH') OR exit('No direct script access allowed');
?>
<?php if (!empty($breadcrumbs)): ?>
  <ol class="breadcrumb">
    <?php foreach($breadcrumbs as $breadcrumb): ?>

    <?php if($breadcrumb['is_active']): ?>
    <li class="active"><?php echo $breadcrumb['name']?></li>
    <?php else: ?>
    <li><a href="<?php echo $breadcrumb['url']?>"><?php echo $breadcrumb['name']?></a></li>
    <?php endif; ?>

    <?php endforeach; ?>
  </ol>

<?php endif; ?>
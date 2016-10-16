<?php
/**
 * admin.php
 *
 * @package mailapp
 * @author johny
 * @copyright Copyright (c) 2016, codeandcreative
 */
?>
<div class="container">
	<div class="row">
		<div class="col-md-12">
			<h1><?php echo $page_title; ?></h1>
		</div>
	</div>
	<div class="row">
		<div class="col-md-12">
			<?php if(!empty($database_created)): ?>
			<div class="alert alert-success">Created it!</div>
			<?php endif; ?>

			<?php if(isset($database_dropped) && $database_dropped == true): ?>
				<div class="alert alert-success">Dropped it!</div>
			<?php elseif(isset($database_dropped) && $database_dropped == false): ?>
				<div class="alert alert-success">No database to drop!</div>
			<?php endif; ?>

		</div>
	</div>
</div>
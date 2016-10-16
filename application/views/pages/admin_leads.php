<?php
/**
 * admin_leads.php
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
		<?php if(!empty($leads)): ?>
		<div class="col-md-12">
			<h2>Leads</h2>
			<div class="table-responsive">
			<table class="table table-striped table-bordered">
				<tr>
					<?php foreach(array_keys($leads[0]) as $field_name):?>
						<th><?php echo $field_name; ?></th>
					<?php endforeach; ?>
					<th>&nbsp;</th>
				</tr>
				<?php foreach($leads as $lead):?>
					<tr>
						<?php foreach($lead as $field_name => $field_value):?>
							<td><?php echo !is_array($field_value) && !is_object($field_value) ? $field_value : print_r($field_value, true); ?></td>
						<?php endforeach; ?>
					</tr>
				<?php endforeach; ?>
			</table>
			<div
		</div>
		<?php else: ?>
			<div class="alert alert-info">No leads found. <a href="<?php echo base_url('admin/mailservice'); ?>">Go get some leads</a></div>
		<?php endif; ?>
	</div>
</div>
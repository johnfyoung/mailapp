<?php
/**
 * admin_mailservice.php
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
		<?php if(!empty($lists)): ?>
		<div class="col-md-12">
			<h2>Lists</h2>
			<div class="table-responsive">
			<table class="table table-striped">
				<caption>Next refresh: <?php echo $list_expire_time; ?> <a href="<?php echo base_url('admin/mailservice/'.$service_name.'/lists'); ?>" class="btn">Refresh now</a></caption>
				<tr>
					<th><input type="checkbox" name="select_all" /></th>
<?php foreach(array_keys($lists[0]) as $field_name):?>
					<th><?php echo $field_name; ?></th>
<?php endforeach; ?>
					<th>&nbsp;</th>
				</tr>
<?php foreach($lists as $list):?>
				<tr>
					<td><input type="checkbox" name="select_list_<?php echo $list['list_unique_id']; ?>" data-listid="<?php echo $list['list_unique_id']; ?>" class="mailinglist_select" /></td>
<?php foreach($list as $field_name => $field_value):?>
					<td>
<?php echo !is_array($field_value) && !is_object($field_value) ? $field_value : print_r($field_value, true); ?>
					</td>
<?php endforeach; ?>
					<td><a href="<?php echo base_url('admin/mailinglistmembers/'. $service_name .'/'. $list['id']) ?>" class="btn btn-primary btn-small">Members</a></td>
				</tr>
<?php endforeach; ?>
			</table>
			</div>
		</div>
		<?php elseif(!empty($mailservices)): ?>
		<?php foreach($mailservices as $service_id => $service_config): ?>
		<div class="col-md-4">
			<div class="panel panel-primary">
				<div class="panel-heading">
					<h3 class="panel-title"><?php echo $service_config['name']; ?></h3>
				</div>
				<div class="panel-body">
					<?php foreach($service_config as $service_config_id => $service_config_value): ?>
						<strong><?php echo $service_config_id ?></strong>: <?php echo $service_config_value; ?><br />
					<?php endforeach; ?>
					<br /><a class="btn btn-primary" href="<?php echo base_url('admin/mailservice/'. $service_id)?>">View</a>
				</div>
			</div>
		</div>
		<?php endforeach; ?>
		<?php endif; ?>
	</div>
</div>
<?php
/**
 * admin_mailservice_memberlist.php
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
		<?php if(!empty($members)): ?>
		<div class="col-md-12">
			<h2>Members - <?php echo $list_name?></h2>
			<div class="table-responsive">
			<table class="table table-striped table-bordered">
				<caption>
					Next refresh: <?php echo $members_list_expiration; ?>
					<a href="<?php echo base_url('admin/mailinglistmembers/'. $service_name .'/'. $list_id .'/refresh'); ?>" class="btn">Refresh now</a> <a href="<?php echo base_url('admin/mailinglistmembers/'. $service_name .'/'. $list_id .'/import_leads');?>" class="btn">Add as leads</a>
					<input id="keep_synced" type="checkbox" name="keep_synced" data-listid="<?php echo $list_id;?>"/> <label for="keep_synced">Keep synched?</label>
				</caption>
				<tr>
					<th><input type="checkbox" name="select_all" /></th>
<?php foreach(array_keys($members[0]) as $field_name):?>
					<th><?php echo $field_name; ?></th>
<?php endforeach; ?>
				</tr>
<?php foreach($members as $member):?>
				<tr>
					<td><input type="checkbox" name="select_<?php echo $member['id']; ?>" data-memberid="<?php echo $member['id']; ?>" /></td>
<?php foreach($member as $field_name => $field_value):?>
					<td><?php echo !is_array($field_value) && !is_object($field_value) ? $field_value : print_r($field_value, true); ?></td>
<?php endforeach; ?>

				</tr>
<?php endforeach; ?>
			</table>
			</div>
		</div>
		<?php endif; ?>
	</div>
</div>
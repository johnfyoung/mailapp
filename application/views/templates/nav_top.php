<?php
/**
 * nav_top.php
 *
 * @package mailapp
 * @author johny
 * @copyright Copyright (c) 2016, codeandcreative
 */

defined('BASEPATH') OR exit('No direct script access allowed');
?>
<div class="navbar navbar-inverse navbar-fixed-top" role="navigation">
	<div class="container">
		<div class="navbar-header">
			<button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
				<span class="sr-only">Toggle navigation</span>
				<span class="icon-bar"></span>
				<span class="icon-bar"></span>
				<span class="icon-bar"></span>
			</button>
			<a class="navbar-brand" href="<?php echo base_url(); ?>"><?php echo !empty($site_title) ? $site_title : lang('site_title'); ?></a>
		</div>
		<div class="navbar-collapse collapse">
		<?php if($has_menu): ?>
			<ul class="nav navbar-nav">
				<li <?php echo $page_name == lang('page_name_home') ? 'class="active"' : ''; ?>><a href="<?php echo base_url(); ?>"><?php echo lang('page_name_home'); ?><span class="sr-only">(current)</span></a></li>
				<li><a href="<?php echo base_url('admin'); ?>"></a></li>
				<li class="dropdown">
					<a href="#" class="dropdown-toggle <?php echo $page_name == lang('page_name_admin') ? ' active' : ''; ?>" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false"><?php echo lang('page_name_admin'); ?> <span class="caret"></span></a>
					<ul class="dropdown-menu">
						<li><a href="<?php echo base_url('admin'); ?>"><?php echo lang('page_name_admin'); ?></a></li>
						<li><a href="<?php echo base_url('admin/mailservice'); ?>">Mailservices</a></li>
						<li><a href="<?php echo base_url('admin/leads'); ?>">Leads</a></li>
					</ul>
				</li>
			</ul>
			<?php if(empty($current_user)): ?>
			<?php echo form_open(base_url('access/login'), array('class' => 'navbar-form navbar-right', 'role' => 'form')); ?>
				<div class="form-group">
					<input type="text" placeholder="Email" class="form-control" name="identity">
				</div>
				<div class="form-group">
					<input type="password" placeholder="Password" class="form-control" name="password">
				</div>
				<button type="submit" class="btn btn-success"><?php echo lang('sign_in'); ?></button>
			</form>
			<?php else: ?>
			<div class="navbar-right">
				<span class="navbar-text"><?php echo lang('loggedin_as') . $current_user->first_name .' '. $current_user->last_name;; ?>&nbsp;&nbsp;&nbsp;<a href="<?php echo base_url('access/logout'); ?>" class="btn btn-primary" role="button"><?php echo lang('log_out'); ?></a></span>

			</div>
			<?php endif; ?>
		<?php endif; ?>
		</div><!--/.navbar-collapse -->
	</div>
</div>
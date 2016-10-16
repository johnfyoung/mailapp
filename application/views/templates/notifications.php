<?php
/**
 * notifications.php
 *
 * @package mailapp
 * @author johny
 * @copyright Copyright (c) 2016, codeandcreative
 */
 
 defined('BASEPATH') OR exit('No direct script access allowed');
 
?>
<?php if(!empty($raw_notifications)): ?>
	<div class="container">
		<div class="row">
			<div class="col-md-12">
			<?php foreach($raw_notifications as $notification_type => $notification_messages):
					if(!empty($notification_messages)):
			?>

				<div class="alert alert-<?php echo $notification_type; ?>" role="alert">
				<?php foreach($notification_messages as $notification_message): ?>
					<div><?php echo $notification_message; ?></div>
				<?php endforeach; ?>
				</div>

			<?php 	endif;
				  endforeach;
			?>
			</div>
		</div>
	</div>
 <?php endif; ?>
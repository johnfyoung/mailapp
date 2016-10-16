<?php
/**
 * debugconsole.php
 *
 * @package philips-oral-healthcare-dev-head
 * @author johny
 * @copyright Copyright (c) 2015, Williams Helde
 * @link http://www.williams-helde.com
 */
 
 defined('BASEPATH') OR exit('No direct script access allowed');
?>
<div id="debugconsole" class="container-fluid" style="margin-top:20px;">
	<div class="container">
		<div class="row">
			<div class="col-md-10 col-md-offset-1">
				<!-- Debug Vars -->
				<?php if(isset($debug_vars)): ?>
				<div class="panel panel-primary">
					<div class="panel-heading" role="tab" id="debugconsole_heading_debugvars">
						<h4 class="panel-title">
							<a data-toggle="collapse" data-parent="#accordion" href="#collapse_debugconsole_heading_debugvars" aria-expanded="false" aria-controls="collapse_debugconsole_heading_debugvars">
								Debug Vars
							</a>
						</h4>
					</div>
					<div id="collapse_debugconsole_heading_debugvars" class="panel-collapse collapse" role="tabpanel" aria-labelledby="debugconsole_heading_debugvars">
						<div class="panel-body">
							<div class="table-responsive">
								<table class="table table-striped">
<!--									<tr>
										<th>Label</th>
										<th>Var</th>
										<th>File</th>
										<th>Function</th>
										<th>Line</th>
									</tr>-->
									<?php foreach($debug_vars as $k => $debug_var): ?>
										<tr>
											<td>
												<div class="panel-heading" role="tab" id="debugconsole_heading_debugvar_<?php echo $k; ?>">
													<p class="panel-title">
														<a data-toggle="collapse" data-parent="#accordion" href="#collapse_debugconsole_debugvar_<?php echo $k; ?>" aria-expanded="false" aria-controls="collapse_debugconsole_debugvar_<?php echo $k; ?>">
															<?php

															// $debug_var['time_of_day'] is a microtime time stamp, here looks like s float, but it is really a string
															$time_parts = explode('.',$debug_var['time_of_day']);

															$debug_var['time_of_day'] = $time_parts[0];
															$debug_var['time_of_day_micro'] = $time_parts[1];
															?>
															<?php echo date('H:i:s', intval($debug_var['time_of_day'])) .'.'. $debug_var['time_of_day_micro'] .' - '. $debug_var['time'] .' secs. - '. $debug_var['label']; ?>
														</a>
													</p>
												</div>

												<div id="collapse_debugconsole_debugvar_<?php echo $k; ?>" class="panel-collapse collapse" role="tabpanel" aria-labelledby="collapse_debugconsole_debugvar_<?php echo $k; ?>">
													<div class="panel-body">
														<p><?php printf('Function: %s - in %s, line %s', $debug_var['function'], $debug_var['file'], $debug_var['line']); ?></p>
														<pre><?php echo $debug_var['var']; ?></pre>
													</div>
												</div>
											</td>

										</tr>
									<?php endforeach; ?>
								</table>
							</div>
						</div>
					</div>
				</div>
				<?php endif; ?>
				<!-- / Debug Vars -->
				<!-- Datastore Calls -->
				<?php if(!empty($datastore_calls)): ?>
				<div class="panel panel-primary">
					<div class="panel-heading" role="tab" id="debugconsole_heading_datastore">
						<h4 class="panel-title">
							<a data-toggle="collapse" data-parent="#accordion" href="#collapse_debugconsole_heading_datastore" aria-expanded="false" aria-controls="collapse_debugconsole_heading_datastore">
								Datastore Webservice calls
							</a>
						</h4>
					</div>
					<div id="collapse_debugconsole_heading_datastore" class="panel-collapse collapse" role="tabpanel" aria-labelledby="debugconsole_heading_datastore">
						<div class="panel-body">
							<div class="table-responsive">
								<table class="table table-striped">
										<tr>
											<th>Proc</th>
											<th>Params</th>
											<th>Time in secs</th>
										</tr>
									<?php foreach($datastore_calls as $k => $call): ?>
										<tr>
											<?php foreach($call as $call_field_name => $call_field): ?>
												<?php if($call_field_name != 'start' && $call_field_name != 'end') : ?>
												<td><?php echo $call_field; ?></td>
												<?php endif; ?>
											<?php endforeach; ?>
										</tr>
									<?php endforeach; ?>
								</table>
							</div>
						</div>
					</div>
				</div>
				<!-- /Datastore Calls -->
				<?php endif; ?>
			</div>
		</div>
	</div>
</div>
 
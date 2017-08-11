<?php
// admin user page view
use Extranet\Model\UserModel;

$model = new UserModel();
?>

<div class="wrap">
	<h1 class="wp-heading-inline"><?php echo __( 'Edit User', 'extranet' );?></h1>

	<div class="pure-container">
		<form name="userForm" id="userForm" method="post">
			<div class="pure-g header">
				<div class="pure-u-1">
					<button class="button" type="button" onclick="document.location.href='<?php echo $this->url;?>';"><i class="dashicons dashicons-editor-break"></i> <?php echo __('Go back', 'extranet');?></button>
					<button class="button" type="button" onclick="save();"><?php echo __( 'Save', 'extranet' );?></button>
				</div>
			</div>

			<div class="pure-g view-user">
				<div class="pure-u-1 pure-u-md-1-2">
					<div class="pure-g">
				        <div class="pure-u-1-2">
				            <?php echo __('Username', 'extranet');?>
				        </div>
				        <div class="pure-u-1-2">
				            <?php echo $model->user->user_login ;?>
				        </div>
				    </div>

					<div class="pure-g">
				        <div class="pure-u-1-2">
			           		<?php echo __('Email', 'extranet');?>	
			   			</div>
				        <div class="pure-u-1-2">
			            	<?php echo $model->user->user_email ;?>
			      		</div>
			      	</div>

					<div class="pure-g">
				        <div class="pure-u-1-2">
			            	<?php echo $model->form->getLabel('user[_extranet_enabled]');?>
						</div>
				        <div class="pure-u-1-2">
			            	<?php echo $model->form->getInput('user[_extranet_enabled]');?>
						</div>
					</div>


					<div class="pure-g">
				        <div class="pure-u-1-2">
			            	<?php echo $model->form->getLabel('user[_extranet_user_homepage]');?>
						</div>
				        <div class="pure-u-1-2">
			            	<?php echo $model->form->getInput('user[_extranet_user_homepage]');?>
						</div>
					</div>
				</div>
			</div>

			<div id="update-area"></div>

			<input type="hidden" name="user[id]" value="<?php echo $model->user->ID;?>" />
		</form>
	</div>
</div>

<script type="text/javascript">
	var urls = 
		{
			'usersave':'<?php echo $this->url . '&task=adminuser.save&nonce='.wp_create_nonce('adminuser');?>',
			'userfolders':'<?php echo site_url() . '/wp-admin/admin.php?page=extranet&task=adminuser.folders&nonce='.wp_create_nonce('adminuser');?>',
			'savepermission':'<?php echo site_url() . '/wp-admin/admin.php?page=extranet&task=adminfolder.setPermissions&nonce='.wp_create_nonce('adminfolder');?>'
		}

	function save() {
		document.userForm.action = urls.usersave;
		document.userForm.submit();
	}

	var config = {
		language:{
			'permissions':'<?php echo esc_html__('Permissions','extranet');?>',
			'aggregate':'<?php echo esc_html__('Aggregated permissions','extranet');?>',
			'back':'<?php echo esc_html__('Back','extranet');?>',
			'list':'<?php echo esc_html__('List','extranet');?>',
			'download':'<?php echo esc_html__('Download','extranet');?>',
			'upload':'<?php echo esc_html__('Upload','extranet');?>',
			'delete':'<?php echo esc_html__('Delete','extranet');?>',
			'mkdir':'<?php echo esc_html__('Create folder','extranet');?>',
			'rmdir':'<?php echo esc_html__('Remove folder','extranet');?>',
			'recursive':'<?php echo esc_html__('Recursive','extranet');?>',
			'allowed':'<?php echo esc_html__('Allowed','extranet');?>',
			'blocked':'<?php echo esc_html__('Blocked','extranet');?>',
			'view':'<?php echo esc_html__('View','extranet');?>',
		},
		user: <?php echo $model->user->ID;?>,
		back: ''
	};
	var extranet = new Extranet(config);


	function showPermissions(path){
		var p = 'id=<?php echo $model->user->ID;?>&path=' + path; 
		extranet.makeAjaxCall(urls.userfolders,'POST',p,function(response){
			var r = JSON.parse(response);
			document.getElementById('update-area').innerHTML = '';
			extranet.renderUserPermissions('update-area', r);
		});
	}


	function togglePermission(e,el,userID) {
		
		el.individual = e.target.checked ? (parseInt(e.target.value,2) | parseInt(el.individual,2)) : (parseInt(e.target.value,2) ^ parseInt(el.individual,2));
		el.aggregate  = e.target.checked ? (parseInt(e.target.value,2) | parseInt(el.aggregate,2)) : (parseInt(e.target.value,2) ^ parseInt(el.aggregate,2));
		el.individual = (el.individual.toString(2).length<8) ? '0'.repeat(8-el.individual.toString(2).length) + el.individual.toString(2) : el.individual.toString(2);
		el.aggregate = (el.aggregate.toString(2).length<8) ? '0'.repeat(8-el.aggregate.toString(2).length) + el.aggregate.toString(2) : el.aggregate.toString(2);

		var p = 'user=' + userID + '&permissions=' + el.individual + '&folder=' + el.path;

		extranet.makeAjaxCall(urls.savepermission,'POST',p,function(response){
			var r = JSON.parse(response);
			console.log(r);
		});

		return el;
	}


	window.addEventListener('load', function(){
		showPermissions('');
	});
</script>
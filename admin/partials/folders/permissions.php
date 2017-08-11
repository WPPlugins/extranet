<?php
// admin folder permissions view
use Extranet\Model\FolderModel;

$model = new FolderModel();
$breadcrumb = $model->getBreadcrumb();
?>

<div class="wrap">
	<h1 class="wp-heading-inline"><?php echo __( 'Folder permissions', 'extranet' );?></h1>

		<div class="pure-g">
			<div class="pure-u-1">
			<p><?php echo __('These permissions must be set to allow access to specific users to your folders. If user status is <span class="allowed">Allowed</span>, the user will be able to execute the actions selected here. If user status is <span class="blocked">Blocked</span>, the user cannot execute any of these actions.', 'extranet');?></p>
			<p><?php echo __( 'Aggregated permissions = this folder permissions combined with parent folder permissions, if parent folder permissions are recursive.<br/>In case of recursive permissions, the permissions will be recursively applied to all other folders that are anywhere within the folder. That is why aggregated permissions might have different values.', 'extranet' );?></p>
			</div>
		</div>

	<div class="pure-container">

		<div class="pure-g navigation">
			<div class="pure-u-12-24 pure-u-md-4-24">
				<a href="<?php echo $this->url . '&path=' . base64_encode($model->getPrevious());?>"><i class="dashicons dashicons-editor-break"></i> <?php echo __('Back to folders view', 'extranet');?></a>
			</div>
			<div class="pure-u-12-24 pure-u-md-20-24">
			</div>
		</div>

		<div class="pure-g breadcrumb">
			<div class="pure-u-1">
				<?php echo __('Location', 'extranet') . ': ';?>
				<a href="<?php echo $this->url;?>"><?php echo __('Root', 'extranet') ;?></a>
				
				<?php if($breadcrumb) {?>
					<?php foreach ($breadcrumb as $node) { ?>
						/ <a href="<?php echo $this->url . '&path=' . base64_encode($node->url);?>"> <?php echo $this->escape($node->name);?></a>
					<?php } ?>
				<?php } ?>
			</div>
		</div>
		
		<div class="pure-g"><div class="pure-u-1"><p></p></div></div>

		<div class="pure-g">
			<div class="pure-u-1">			
				<form class="pure-form">
				    <input type="text" id="filterby" class="pure-input" placeholder="<?php echo __('Filter by username or name','extranet');?>" />
				    <button type="button" class="pure-button" onclick="filterdo();"><?php echo __('Filter', 'extranet');?></button>
				    <button type="button" class="pure-button" onclick="filterclear();"><?php echo __('Clear', 'extranet');?></button>
				</form>
			</div>
		</div>

		<div class="pure-g"><div class="pure-u-1" id="error-area"></div></div>
		
		<div id="update-area"></div>
	</div>
</div>

<script type="text/javascript">
var folder = '<?php echo base64_encode($model->folder->getRelativePath());?>';
var permissions = '<?php echo $model->getPermissionsList();?>';
 
var config = {
	language:{
		'username':'<?php echo esc_html__('Username','extranet');?>',
		'fullname':'<?php echo esc_html__('Full Name','extranet');?>',
		'aggregate':'<?php echo esc_html__('Aggregated permissions','extranet');?>',
		'permissions':'<?php echo esc_html__('Permissions','extranet');?>',
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
	}
};
var extranet = new Extranet(config);


function togglePermission(e, el) {
	
	el.individual = e.target.checked ? (parseInt(e.target.value,2) | parseInt(el.individual,2)) : (parseInt(e.target.value,2) ^ parseInt(el.individual,2));
	el.aggregate  = e.target.checked ? (parseInt(e.target.value,2) | parseInt(el.aggregate,2)) : (parseInt(e.target.value,2) ^ parseInt(el.aggregate,2));
	el.individual = (el.individual.toString(2).length<8) ? '0'.repeat(8-el.individual.toString(2).length) + el.individual.toString(2) : el.individual.toString(2);
	el.aggregate = (el.aggregate.toString(2).length<8) ? '0'.repeat(8-el.aggregate.toString(2).length) + el.aggregate.toString(2) : el.aggregate.toString(2);

	var u = '<?php echo site_url() . '/wp-admin/admin.php?page=extranet&task=adminfolder.setPermissions&nonce='.wp_create_nonce('adminfolder');?>';
	var p = 'user=' + el.id + '&permissions=' + el.individual + '&folder=' + folder;

	extranet.makeAjaxCall(u,'POST',p,function(response){
		var r = JSON.parse(response);
		if (r.error) {
			extranet.showError(r.error);
		}
	});

	return el;
}

function filterdo() {

	var filtered = [];
	var tofilter = document.getElementById("filterby").value.toLowerCase();
	var items = JSON.parse(permissions);

	if (tofilter) {
		items.forEach(function(p){
			if (he.decode(p.username.toLowerCase()).indexOf(tofilter) !== -1 || he.decode(p.nick.toLowerCase()).indexOf(tofilter) !== -1) {
				filtered.push(p);
			}
		});
		document.getElementById("update-area").innerHTML = '';
		extranet.renderPermissions('update-area', JSON.stringify(filtered));
	}
}

function filterclear() {

	document.getElementById("filterby").value = '';
	document.getElementById("update-area").innerHTML = '';
	extranet.renderPermissions('update-area', permissions);
}


window.addEventListener('load', function(){
	
	extranet.renderPermissions('update-area', permissions);
	document.getElementById("filterby").value = '';
	extranet.bindEnter([{"id":"filterby","cback":filterdo}]);
});
</script>
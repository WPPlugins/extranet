<?php
// admin list of users view
use Extranet\Model\UsersModel;

$model 	= new UsersModel();
$users 	= $model->getUsers();
?>

<div class="wrap">
	<h1 class="wp-heading-inline"><?php echo __( 'Users', 'extranet' );?></h1>

	<div class="pure-container">

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

	var data = '<?php echo json_encode($users);?>';
	var config = {
		url:'<?php echo $this->url;?>',
		language:{
			'username':'<?php echo esc_html__('Username','extranet');?>',
			'name':'<?php echo esc_html__('Name','extranet');?>',
			'email':'<?php echo esc_html__('Email','extranet');?>',
			'allowed':'<?php echo esc_html__('Allowed','extranet');?>',
			'blocked':'<?php echo esc_html__('Blocked','extranet');?>',
			'edit':'<?php echo esc_html__('Edit','extranet');?>'
		}
	};

	var extranet = new Extranet(config);


	function filterdo() {

		var filtered = [];
		var tofilter = document.getElementById("filterby").value.toLowerCase();
		var items = JSON.parse(data);

		if (tofilter) {
			items.forEach(function(p){
				if (he.decode(p.username.toLowerCase()).indexOf(tofilter) !== -1 || he.decode(p.name.toLowerCase()).indexOf(tofilter) !== -1) {
					filtered.push(p);
				}
			});
			document.getElementById("update-area").innerHTML = '';
			extranet.renderUsers('update-area', JSON.stringify(filtered));
		}
	}

	function filterclear() {

		document.getElementById("filterby").value = '';
		document.getElementById("update-area").innerHTML = '';
		extranet.renderUsers('update-area', data);
	}

	window.addEventListener('load', function(){
		extranet.renderUsers('update-area', data);
		document.getElementById("filterby").value = '';
		extranet.bindEnter([{"id":"filterby","cback":filterdo}]);
	});
</script>
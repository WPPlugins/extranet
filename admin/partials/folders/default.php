<?php
// admin list of folders view
use Extranet\Model\FoldersModel;

$model 		= new FoldersModel();
$folders 	= $model->getFolders();
$files		= $model->getFiles();
$breadcrumb = $model->getBreadcrumb();
?>

<div class="wrap">
	<h1 class="wp-heading-inline"><?php echo __( 'Folders', 'extranet' );?></h1>

	<div class="pure-container">
		<?php $i = 0; ?>

		<div class="pure-g navigation">
			<div class="pure-u-12-24 pure-u-md-4-24">
				<a href="<?php echo $this->url . '&path=' . base64_encode($model->getPrevious());?>"><i class="dashicons dashicons-editor-break"></i> <?php echo __('Go back', 'extranet');?></a>
			</div>
			<div class="pure-u-12-24 pure-u-md-20-24">
				<button class="button" type="button" onclick="toggleCreate();"><?php echo __( 'Create new folder', 'extranet' );?></button>
				<button class="button" type="button" onclick="toggleUploader(this);"><?php echo __('Add files', 'extranet');?></button>
			</div>
		</div>

		<div class="pure-g upload-area">
			<div class="pure-u-1 hidden" id="uf1">
				<input name="afile" id="afile" style="display:none;" multiple="multiple" type="file">
				<div class="inner-upload center" id="uploadarea">
					<h3><?php echo __('Add files into the current directory','extranet');?></h3>
					<span><?php echo __('Drag your files  here or click.', 'extranet');?></span>
					<div class="progress" style="width:0%;"><div class="meter"></div></div>
				</div>
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

		<div class="pure-g"><div class="pure-u-1" id="error-area"></div></div>
		
		<div id="update-area"></div>
	</div>
</div>


<form id="renamePopup" class="pure-form pure-g hidden">
	<div class="pure-u-1 pure-u-md-8-24"></div>
	<div class="pure-u-1 pure-u-md-8-24">
		<legend><?php echo __('Rename selected','extranet');?></legend>
		<p style="color:red;" id="warning"></p>
		<input type="text" name="newname" id="rename-new-name" class="pure-input-1" />
	</div>
	<div class="pure-u-1 pure-u-md-8-24"></div>

	<div class="pure-u-1"></div>
	
	<div class="pure-u-1">
		<div class="pure-controls">
			<button type="button" class="pure-button pure-button-primary" onclick="changeName();return false;"><?php echo __('Rename','extranet');?></button>
			<button type="button" class="pure-button" onclick="toggleRename();return false;"><?php echo __('Cancel','extranet');?></button>
		</div>
	</div>
</form>


<form id="createNewFolderPopup" class="pure-form pure-g hidden">
	<div class="pure-u-1 pure-u-md-8-24"></div>
	<div class="pure-u-1 pure-u-md-8-24">
		<legend><?php echo __('Create new folder at existing location','extranet');?></legend>
		<input type="text" name="newname" id="create-new-name" class="pure-input-1" placeholder="<?php echo __('Give it a name...','extranet');?>" />
	</div>
	<div class="pure-u-1 pure-u-md-8-24"></div>

	<div class="pure-u-1"></div>
	
	<div class="pure-u-1">
		<div class="pure-controls">
			<button type="button" class="pure-button pure-button-primary" onclick="createFolder();return false;"><?php echo __('Create','extranet');?></button>
			<button type="button" class="pure-button" onclick="toggleCreate();return false;"><?php echo __('Cancel','extranet');?></button>
		</div>
	</div>
</form>


<script type="text/javascript">

	var folders = '<?php echo json_encode($folders);?>';
	var files = '<?php echo json_encode($files);?>';

	var config = {
		url:'<?php echo $this->url;?>',
		language:{
			'delete':'<?php echo esc_html__('Delete','extranet');?>',
			'permissions':'<?php echo esc_html__('Permissions','extranet');?>',
			'rename':'<?php echo esc_html__('Rename','extranet');?>'
		}
	};

	var urls = 
		{
			'adminfolderrename':'<?php echo site_url() . '/wp-admin/admin.php?page=extranet&task=adminfolder.rename&nonce='.wp_create_nonce('adminfolder');?>',
			'adminfilerename':'<?php echo site_url() . '/wp-admin/admin.php?page=extranet&task=adminfile.rename&nonce='.wp_create_nonce('adminfile');?>',
			'adminfoldernew':'<?php echo site_url() . '/wp-admin/admin.php?page=extranet&task=adminfolder.newf&nonce='.wp_create_nonce('adminfolder');?>',
			'adminfolderdelete':'<?php echo site_url() . '/wp-admin/admin.php?page=extranet&task=adminfolder.delete&nonce='.wp_create_nonce('adminfolder');?>',
			'adminfiledelete':'<?php echo site_url() . '/wp-admin/admin.php?page=extranet&task=adminfile.delete&nonce='.wp_create_nonce('adminfile');?>',
			'upload':'<?php echo site_url() . '/wp-admin/admin.php?page=extranet&task=adminfolder.upload&path='. $model->app->input->get('path','','base64') . '&nonce='.wp_create_nonce('adminfolder'); ?>',
		}

	var extranet = new Extranet(config);
	var selector = '';
	var renameto = document.getElementById('rename-new-name');
	var createas = document.getElementById('create-new-name');
	var renamePopup = document.getElementById('renamePopup');
	var createNewFolderPopup = document.getElementById('createNewFolderPopup');

	var dropZone = document.getElementById('uploadarea');
	var uf1 = document.getElementById('uf1');
	var if1 = document.getElementById('afile');

	function toggleRename(id, type) {
		
		renamePopup.classList.toggle('hidden');
		document.getElementById('warning').innerHTML = (type=='folder') ? '<?php echo __('Attention! This action will remove all permissions corresponding to this folder and all sub-folders.','extranet') ;?>' : '';
		if (!renamePopup.classList.contains('hidden')) {
			selector = document.getElementById(id);
			renameto.value = '';
			renameto.focus();
		}
	}

	function toggleCreate() {
		createNewFolderPopup.classList.toggle('hidden');
		if (!createNewFolderPopup.classList.contains('hidden')) {
			createas.value = '';
			createas.focus();
		}
	}

	function toggleUploader(el) {
		uf1.classList.toggle('hidden');
		el.innerHTML = uf1.classList.contains('hidden') ? '<?php echo __('Add files', 'extranet');?>' : '<?php echo __('Close', 'extranet');?>' ;
	}

	function changeName() {

		if 	(renameto.value && selector.dataset.path) {
			var u = (selector.dataset.type == 'folder') ? urls.adminfolderrename : urls.adminfilerename;
			var p = 'new=' + encodeURI(renameto.value) + '&path=' + selector.dataset.path;
			extranet.makeAjaxCall(u,'POST',p,function(response){
				var r = JSON.parse(response);
				if (!r.error) {
					selector.querySelector('.title').innerHTML = r.renameto;
					selector.dataset.path = he.encode(r.value);
					if (r.href) {
						selector.querySelector('.title').href = atob(r.href);
					}
				} else {
					extranet.showError(r.error);
				}
			});
		}
		toggleRename();
	}


	function createFolder() {

		if (createas.value) {
			var p = 'new=' + encodeURI(createas.value) + '&path=' + '<?php echo $model->app->input->get('path','','base64'); ?>';
			extranet.makeAjaxCall(urls.adminfoldernew,'POST',p,function(response){
				var r = JSON.parse(response);
				if (!r.error) {
					var updated = JSON.parse(folders);
					updated.push(r.new);
					folders = JSON.stringify(updated);
					extranet.refreshItems('update-area', folders, files);
				} else {
					extranet.showError(r.error);
				}
			});
		}
		toggleCreate();
	}


	function deleteItem(el) {

		if(el && window.confirm('<?php echo __('Are you sure?','extranet');?>')) {
			var selected = document.getElementById(el);
			var u = (selected.dataset.type == 'folder') ? urls.adminfolderdelete : urls.adminfiledelete;
			var p = 'path=' + el;
			extranet.makeAjaxCall(u,'POST',p,function(response){
				var r = JSON.parse(response);
				if (!r.error) {
					selected.parentNode.removeChild(selected);
					reduceItems(selected.dataset.type, el);
				} else {
					extranet.showError(r.error);
				}
			});
		}
	}


	// used to reduce folders/files array when an element was deleted
	function reduceItems(type, key) {
		var i;

		if (type == 'folder') {
			var items = JSON.parse(folders);
			i = items.filter(function(el){
				return el.path != key;
			});
			folders = JSON.stringify(i);
		}

		if (type == 'file') {
			var items = JSON.parse(files);

			i = items.filter(function(el){
				return el.path != key;
			});
			files = JSON.stringify(i);
		}
	}


	uf1.addEventListener('mouseover', function(e) {
		uf1.style.border = '2px dashed #000';
	});

	uf1.addEventListener('mouseout', function(e) {
		uf1.style.border = '2px dashed #ddd';
	});

	dropZone.addEventListener('dragover', function(e) {
		if (e.preventDefault) e.preventDefault(); 
		if (e.stopPropagation) e.stopPropagation();
		uf1.style.border = '2px dashed #000';
	});


	dropZone.addEventListener('dragenter', function(e) {
		uf1.style.border = '2px dashed #000';
	});

	// Event Listener for when the dragged file leaves the drop zone.
	dropZone.addEventListener('dragleave', function(e) {
		uf1.style.border = '2px dashed #ddd';
	});

	dropZone.addEventListener('drop', function(e){
		if (e.preventDefault) e.preventDefault(); 
		if (e.stopPropagation) e.stopPropagation();

		var fileList = e.dataTransfer.files;

		if (fileList.length > 0) {
			uploadFiles(fileList);
		}
	});


	dropZone.addEventListener('click', function(e) {
		if1.click();
	});

	if1.addEventListener('change', function(e){
		uploadFiles(e.target.files);
	});


	function uploadFiles(myfiles)
	{
  		var xhr = new XMLHttpRequest();
  		var fd = new FormData();

  		xhr.open('POST', urls.upload, true);

  		for (var i=0; i<myfiles.length; i++)
		{
			fd.append('mf_file_upload[]', myfiles[i]);

			document.querySelector('.progress').style.width = '0%';

			xhr.upload.onprogress = function(e) {
				if (e.lengthComputable) {
					var percentComplete = (e.loaded / e.total) * 100;
					document.querySelector('.progress').style.width = percentComplete+'%';
				}
			};

			xhr.onload = function() {
				if (this.status == 200) {
					var resp = JSON.parse(this.response);
					if (resp.error) {
						extranet.showError(resp.error);
					}

					if (resp.new.length > 0)
					{
						var updated = resp.new.concat(JSON.parse(files));
						files = JSON.stringify(updated);
						extranet.refreshItems('update-area', folders, files);
					}
					document.querySelector('.progress').style.width = '0%';
				}
			};
		}

		xhr.send(fd);
	}


	window.addEventListener('load', function(){

		extranet.renderItems('update-area', folders, 'folder');
		extranet.renderItems('update-area', files, 'file');

		extranet.bindEnter([{"id":"rename-new-name","cback":changeName},{"id":"create-new-name","cback":createFolder}]);
	});
</script>
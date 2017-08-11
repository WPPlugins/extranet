<?php
// front-end folders layout
use Extranet\Model\ExtranetFoldersModel;

$model = new ExtranetFoldersModel();
$breadcrumb = $model->getBreadcrumb();
$rule = $model->getRule();
$folder = $model->getFolder();
?>

<div class="pure-container">
	<?php echo $this->menu->render();?>

	<div class="pure-g navigation small">
		<div class="pure-u-1">
			<?php if ($rule->is_upload()) {?>
				<button class="pure-button" type="button" onclick="showUploader(this);"><?php echo __('Add files', 'extranet');?></button>
			<?php } ?>
			<?php if ($rule->is_mkdir()) {?>
				<button class="pure-button" type="button" onclick="toggleCreate();"><?php echo __( 'Create new folder', 'extranet' );?></button>
			<?php } ?>
		</div>
	</div>

	<div class="pure-g upload-area">
		<div class="pure-u-1 hidden" id="uf1">
			<input name="afile" id="afile" style="display:none;" multiple="multiple" type="file">
			<div class="inner-upload center" id="uploadarea">
				<h4><?php echo __('Add files into the current directory','extranet');?></h4>
				<span><?php echo __('Drag your files  here or click.', 'extranet');?></span>
				<div class="progress" style="width:0%;"><div class="meter"></div></div>
			</div>
		</div>
	</div>

	<div class="pure-g breadcrumb">
		<div class="pure-u-1">
			<?php echo __('Location', 'extranet') . ': ';?>
			<a href="<?php echo $this->app->url(array('view'=>'folders'));?>"><?php echo __('Root', 'extranet') ;?></a>
			<?php if($breadcrumb) {?>
				<?php foreach ($breadcrumb as $node) { ?>
					/ <a href="<?php echo $this->app->url(array('view'=>'folders','path' => base64_encode($node->url))); ?>"> <?php echo $this->escape($node->name);?></a>
				<?php } ?>
			<?php } ?>
		</div>
	</div>

	<div class="pure-g">
		<div class="pure-u-1 small">
			<p>
				<a href="<?php echo $this->app->url(array('view'=>'folders','path'=>base64_encode($model->getPrevious())));?>"><i class="dashicons dashicons-editor-break"></i> <?php echo __('Go back', 'extranet');?></a>
			</p>
		</div>
	</div>

	<div class="pure-g">
		<div class="pure-u-1">
			<div id="error-area" class="hidden"></div>
		</div>
	</div>

	<div id="update-area"></div>

	<form id="createNewFolderPopup" class="pure-form pure-g hidden">
		<div class="pure-u-1">
			<legend><?php echo __('Create new folder at existing location','extranet');?></legend>
			<input type="text" name="newname" id="create-new-name" class="pure-input-1-2" placeholder="<?php echo __('Give it a name...','extranet');?>" />
		</div>

		<div class="pure-u-1"></div>
		
		<div class="pure-u-1">
			<div class="pure-controls">
				<button type="button" class="pure-button pure-button-primary" onclick="createFolder();return false;"><?php echo __('Create','extranet');?></button>
				<button type="button" class="pure-button" onclick="toggleCreate();return false;"><?php echo __('Cancel','extranet');?></button>
			</div>
		</div>
	</form>
</div>

<script type="text/javascript">

	var folders = '<?php echo json_encode($model->getFolders());?>';
	var files = '<?php echo json_encode($model->getFiles());?>';

	var dropZone = document.getElementById('uploadarea');
	var uf1 = document.getElementById('uf1');
	var if1 = document.getElementById('afile');

	var createas = document.getElementById('create-new-name');
	var createNewFolderPopup = document.getElementById('createNewFolderPopup');

	var urls = 
		{
			'upload':'<?php echo $this->app->url(array('task'=>'extranet.file.upload','path'=>urlencode(base64_encode($folder->getRelativePath())),'nonce'=>wp_create_nonce('extranet.file')));?>',
			'newfolder':'<?php echo $this->app->url(array('task'=>'extranet.folders.newf','path'=>urlencode(base64_encode($folder->getRelativePath())), 'nonce'=>wp_create_nonce('extranet.folders') ));?>',
			'deletefolder':'<?php echo $this->app->url(array('task'=>'extranet.folders.delete','parent'=>urlencode(base64_encode($folder->getRelativePath())), 'nonce'=>wp_create_nonce('extranet.folders') ));?>',
			'deletefile':'<?php echo $this->app->url(array('task'=>'extranet.file.delete','parent'=>urlencode(base64_encode($folder->getRelativePath())), 'nonce'=>wp_create_nonce('extranet.file') ));?>',
			'download':'<?php echo $this->app->url(array('task'=>'extranet.file.download','parent'=>urlencode(base64_encode($folder->getRelativePath())), 'nonce'=>wp_create_nonce('extranet.file') ));?>',
			'favorite':'<?php echo $this->app->url(array('task'=>'extranet.file.favorite','parent'=>urlencode(base64_encode($folder->getRelativePath())), 'nonce'=>wp_create_nonce('extranet.file') ));?>',
		};

	var config = {
		'rule':'<?php echo $model->getRule()->value();?>',
		'permissions':{
			'download':<?php echo (int) $rule->is_download();?>,
			'delete':<?php echo (int) $rule->is_unlink();?>,
			'rmdir':<?php echo (int) $rule->is_rmdir();?>,
			'view':<?php echo (int) $rule->is_preview();?>,
			'share':<?php echo $this->app->get('allow_user_sharing', 0);?>,
		},
		'language':{
			'delete':'<?php echo esc_html__('Delete','extranet');?>',
			'download':'<?php echo esc_html__('Download','extranet');?>',
			'bookmark':'<?php echo esc_html__('Save to favorites','extranet');?>',
			'view':'<?php echo esc_html__('View','extranet');?>',
			'share':'<?php echo esc_html__('Share download','extranet');?>',
			'copylink':'<?php echo esc_html__('Copy the link from the input.','extranet');?>'
		},
		'extensions':<?php echo $model->getExtensions();?>
	};

	var extranet = new Extranet(config);

	function showActions(el) {
		hideActions(el.path);
		var selected = document.getElementById('more-el-' + el.path);
		selected.classList.toggle('hidden');
	}

	function hideActions(path) {
		var actions = document.getElementsByClassName('more-actions');
		for (var i=0;i<actions.length;i++)
		{
			(actions[i].id != 'more-el-' + path) && actions[i].classList.add('hidden');
		}
	}

	function showUploader(el) {
		uf1.classList.toggle('hidden');
		el.innerHTML = uf1.classList.contains('hidden') ? '<?php echo __('Add files', 'extranet');?>' : '<?php echo __('Close', 'extranet');?>' ;
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


	function createFolder() {

		if (createas.value) {
			var p = 'new=' + encodeURI(createas.value);
			extranet.makeAjaxCall(urls.newfolder,'POST',p,function(response){
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


	function toggleCreate() {
		createNewFolderPopup.classList.toggle('hidden');
		if (!createNewFolderPopup.classList.contains('hidden')) {
			createas.value = '';
			createas.focus();
		}
	}


	function fdelete(e) {

		if(e && window.confirm('<?php echo __('Are you sure?','extranet');?>')) {
			var selected = document.getElementById(e.path);
			var u = (e.type == 'folder') ? urls.deletefolder : urls.deletefile;
			var p = 'item=' + e.path;

			extranet.makeAjaxCall(u,'POST',p,function(response){
				var r = JSON.parse(response);
				if (!r.error) {
					selected.parentNode.removeChild(selected);
					reduceItems(e);
				} else {
					extranet.showError(r.error);
				}
			});
		}
	}


	// used to reduce folders/files array when an element was deleted
	function reduceItems(e) {
		var i;

		if (e.type == 'folder') {
			var items = JSON.parse(folders);
			i = items.filter(function(el){
				return el.path != e.path;
			});
			folders = JSON.stringify(i);
		}

		if (e.type == 'file') {
			var items = JSON.parse(files);

			i = items.filter(function(el){
				return el.path != e.path;
			});
			files = JSON.stringify(i);
		}
	}

	function download(e) {
		if (e.type == 'file') {
			window.location.href = urls.download + '&item=' + e.path;
		}
	}

	function favorite(e){
	
		var p = 'item=' + btoa(JSON.stringify(e));
		extranet.makeAjaxCall(urls.favorite,'POST',p,function(response){
			var r = JSON.parse(response);
			if (r.error) {
				extranet.showError(r.error);
				hideActions();
			}
		});
	}

	function preview(e) {
		if (e.type == 'file') {
			window.location.href = urls.download + '&item=' + e.path + '&preview=1';
		}
	}

	window.addEventListener('load', function(){ 

		extranet.bindEnter([{'id':'create-new-name','cback':createFolder}]);

		document.getElementById('update-area').innerHTML = '';
		extranet.renderItems('update-area', folders, 'folder');
		extranet.renderItems('update-area', files, 'file');
	});
</script>
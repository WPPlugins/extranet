<?php
// front-end favorites layout
use Extranet\Model\ExtranetFavoritesModel;

$model = new ExtranetFavoritesModel();
$items = $model->getFavorites();
?>


<div class="pure-container">
	<?php echo $this->menu->render();?>

	<div class="pure-g">
		<div class="pure-u-1 small">
			<p>&nbsp;</p>
		</div>
	</div>
	
	<form name="favForm" id="favForm" method="post">
	<?php if (!empty($items)) {?>
		<?php foreach ($items as $key => $item) { ?>
			<?php if ($item->rule->is_list() || $item->rule->is_download()) {?>
			<div class="pure-g line line<?php echo $key%2;?>">
				<div class="pure-u-2-24"><i class="dashicons dashicons-media-default"></i></div>
				<div class="pure-u-21-24 pure-u-md-18-24"><span class="title"><?php echo $this->escape($item->name);?></span></div>
				<div class="pure-u-12-24 pure-u-md-2-24 small">
					<?php if ($item->rule->is_download()) { ?>
					<i onclick="download('<?php echo urlencode($item->path);?>','<?php echo urlencode($item->parent);?>');return false;" class="download-el dashicons dashicons-download"></i>
					<?php } ?>
				</div>
				<div class="pure-u-12-24 pure-u-md-2-24 small">
					<i onclick="unlike('<?php echo urlencode($item->path);?>');return false;" class="download-el dashicons dashicons-no"></i>
				</div>
			</div>
			<?php } ?>
		<?php } ?>
	<?php } ?>
	</form>
</div>


<script type="text/javascript">
	function download(path, parent) {
		window.location.href = '<?php echo $this->app->url(array('task'=>'extranet.file.download', 'nonce'=>wp_create_nonce('extranet.file') ));?>' + '&item=' + path + '&parent=' + parent;
	}

	function unlike(path) {
		document.favForm.action = '<?php echo $this->app->url(array('task'=>'extranet.file.unfavorite', 'nonce'=>wp_create_nonce('extranet.file') ));?>' + '&path=' + path;
		document.favForm.submit();
	}
</script>
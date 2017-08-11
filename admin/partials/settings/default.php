<?php
// admin settings view
use Extranet\Model\SettingsModel;
use Joomla\Language\LanguageFactory;

$model = new SettingsModel();

?>

<div class="wrap">
	<h1 class="wp-heading-inline"><?php echo __( 'Settings', 'extranet' );?></h1>

	<div class="pure-container">
		<div class="pure-g navigation">
			<div class="pure-u-1">
				<button class="button" type="button" onclick="save();"><?php echo __( 'Save settings', 'extranet' );?></button>
			</div>
		</div>

		<div class="pure-g"><div class="pure-u-1"><p></p></div></div>

		<form name="settingsForm" id="settingsForm" method="post" class="pure-form pure-form-aligned">
			<?php foreach ($model->form->getFieldsets() as $field) { ?>
			<fieldset>
				<legend><?php echo strtoupper($this->escape($field->name));?></legend>
				<?php foreach ($model->form->getFieldset($field->name) as $f) { ?>
				<div class="pure-g pure-settings-group">
					<div class="pure-u-1 pure-u-md-1-5">
						<strong><?php echo $f->label;?></strong>
					</div>
					<div class="pure-u-1 pure-u-md-1-5">
						<?php echo $f->input;?>
					</div>
					<div class="pure-u-1 pure-u-md-3-5">
						<div class="description"> -> <?php echo $f->__get('description');?></div>
					</div>
				</div>
				<?php } ?>
			</fieldset>
			<?php } ?>
		</form>
	</div>
</div>

<script type="text/javascript">
	function save() {
		document.settingsForm.action = '<?php echo $this->url . '&task=adminsettings.save&nonce='.wp_create_nonce('adminsettings');?>';
		document.settingsForm.submit();
	}
</script>
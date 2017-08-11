<?php if (!$this->user->exists()) {?>
<div class="pure-container">
	<div class="pure-u-1 pure-u-md-1-3"></div>
	<form class="pure-form pure-u-1 pure-u-md-1-3" method="post" name="loginform">
	    <fieldset class="pure-group">
	        <input type="text" name="user_login" class="pure-input-1" placeholder="<?php echo __('Username', 'extranet');?>" />
	        <input type="password" name="user_password" class="pure-input-1" placeholder="<?php echo __('Password', 'extranet');?>" />
	    </fieldset>

	    <button onclick="extranet_login();return false;" type="submit" class="pure-button pure-input-1 pure-button-primary"><?php echo __('Sign in', 'extranet');?></button>
	</form>
	<div class="pure-u-1 pure-u-md-1-3"></div>
<div>
<script type="text/javascript">
	function extranet_login() {
		document.loginform.action = '<?php echo $this->app->url(array('task'=>'extranet.login','nonce'=>wp_create_nonce('extranet.login')));?>';
		document.loginform.submit();
	}
</script>
<?php } else { ?>
	<script type="text/javascript">document.location.href='<?php echo $this->app->url(array('view' => 'dashboard'));?>';</script>
<?php } ?>
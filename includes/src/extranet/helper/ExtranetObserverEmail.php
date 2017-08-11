<?php
// observer for front-end activities
namespace Extranet\Helper;

use Extranet\ExtranetApp;

class ExtranetObserverEmail 
{
	protected $app;
	protected $to;
	protected $subject;
	protected $message;


	public function trigger(ExtranetApp $app)
	{
		$this->app = $app;
		$this->to = $this->app->get('notify_me_email_address');

		if (filter_var($this->to, FILTER_VALIDATE_EMAIL) && !empty($this->app->get('notify_me_for')) && method_exists($this, $this->app->event->getName()))
		{
			if(in_array($this->app->event->getName(), $this->app->get('notify_me_for'))) 
			{
				call_user_func(array($this, $this->app->event->getName()));
			}
		}
	}

	protected function download()
	{	
		$this->subject = __('New download event', 'extranet');
		$this->message = __('File downloaded.', 'extranet');
		$this->sendEmail();
	}

	protected function upload()
	{
		$this->subject = __('New upload event', 'extranet');
		$this->message = __('File uploaded.', 'extranet');
		$this->sendEmail();
	}

	protected function unlink()
	{
		$this->subject = __('New file has been deleted.', 'extranet');
		$this->message = __('File deleted.', 'extranet');
		$this->sendEmail();
	}


	protected function mkdir()
	{
		$this->subject = __('New folder has been created.', 'extranet');
		$this->message = __('Folder created.', 'extranet');
		$this->sendEmail();
	}


	protected function rmdir()
	{
		$this->subject = __('New folder has been removed.', 'extranet');
		$this->message = __('Removed folder.', 'extranet');
		$this->sendEmail();
	}
	

	protected function sendEmail()
	{
		wp_mail($this->to, $this->subject, $this->message);
	}
}
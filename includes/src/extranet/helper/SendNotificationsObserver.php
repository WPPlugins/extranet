<?php
// observer for back-end activities
namespace Extranet\Helper;

use Extranet\ExtranetApp;

class SendNotificationsObserver 
{
	public function trigger(ExtranetApp $app)
	{
		$this->app = $app;

		if ($this->canSend())
		{
			call_user_func(array($this, $this->app->event->getName()));
		}
	}


	protected function canSend()
	{
		if ($this->app->get('send_notifications') == 0 || !$this->app->get('send_notifications_subject') || !$this->app->get('send_notifications_message'))
		{
			return false;
		}

		if (!method_exists($this, $this->app->event->getName()))
		{
			return false;
		}

		return true;
	}


	protected function adminuploadfiles()
	{
		$details = $this->app->event->getDetails();
		$users = $this->app->getUsers();

		if (!empty($users))
		{
			foreach ($users as $user)
			{
				if ($details['location']->permissions()->extractAggregate($user->ID)->is_download())
				{	
					$this->sendEmail($user->user_email, $this->getSubject(), $this->getBody());
				}
			}
		}
	}


	protected function getSubject()
	{
		return $this->app->get('send_notifications_subject');
	}


	protected function getBody()
	{
		return $this->app->get('send_notifications_message');
	}


	protected function sendEmail($to, $subject, $message)
	{
		wp_mail($to, $subject, $message);
	}
}
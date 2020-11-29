<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\AdminSettings;
use App\Models\User;
use App\Models\Notifications;
use Mail;

class Subscriptions extends Model
{

	protected $guarded = array();
	public $timestamps = false;

	public function user()
	{
		return $this->belongsTo('App\Models\User')->first();

	}

		public function subscribed()
		{
			return $this->belongsTo('App\Models\User', 'stripe_plan', 'plan')->first();
		}

		public static function sendEmailAndNotify($subscriber_id, $subscriber, $user)
		{
			$user = User::find($user);
			$settings = AdminSettings::first();
			$titleSite = $settings->title;
			$sender    = $settings->email_no_reply;
			$emailUser   = $user->email;
			$fullNameUser = $user->name;
			$subject = $subscriber.' '.trans('users.has_subscribed');

			if ($user->email_new_subscriber == 'yes') {
				//<------ Send Email to User ---------->>>
				Mail::send('emails.new_subscriber', [
					'body' => $subject,
					'title_site' => $titleSite,
					'fullname'   => $fullNameUser,
					'url_for' => 'subscribers'
				],
					function($message) use ($sender, $subject, $fullNameUser, $titleSite, $emailUser)
						{
					    $message->from($sender, $titleSite)
										  ->to($emailUser, $fullNameUser)
											->subject($subject.' - '.$titleSite);
						});
					//<------ End Send Email to User ---------->>>
			}

			if ($user->notify_new_subscriber == 'yes') {
				// Send Notification to User --- destination, author, type, target
				Notifications::send($user->id, $subscriber_id, '1', $user->id);
			}
		}

		public static function sendEmailAndNotifytoCurrentUser($subscriber_id, $subscriber, $user)
		{
			$user = User::find($user);
			if($user->promotion_price && Helper::check_promotion_end_date($user->promotion_end_date)){
				$price = $user->promotion_price;
			  }
			  else{
				$price = $user->price;
			  }
			$subscriber_user = User::find($subscriber_id);
			$settings = AdminSettings::first();
			$titleSite = $settings->title;
			$sender    = $settings->email_no_reply;
			$emailUser   = $subscriber_user->email;
			$fullNameUser = $subscriber_user->name;
			$subject = 'You have subscribed to'.ucfirst($user->name).' for $'.$price;

			if ($user->email_new_subscriber == 'yes') {
				//<------ Send Email to User ---------->>>
				Mail::send('emails.new_subscriber', [
					'body' => $subject,
					'title_site' => $titleSite,
					'fullname'   => $fullNameUser,
					'url_for' => 'subscriptions'
				],
					function($message) use ($sender, $subject, $fullNameUser, $titleSite, $emailUser)
						{
					    $message->from($sender, $titleSite)
										  ->to($emailUser, $fullNameUser)
											->subject($subject.' - '.$titleSite);
						});
					//<------ End Send Email to User ---------->>>
			}

			if ($user->notify_new_subscriber == 'yes') {
				// Send Notification to User --- destination, author, type, target
				// Notifications::send(auth()->user()->id, auth()->user()->id, '1', $user->id);
			}
		}
}

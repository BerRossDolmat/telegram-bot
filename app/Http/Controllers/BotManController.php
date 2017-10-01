<?php
namespace App\Http\Controllers;
use App\Conversations\ExampleConversation;
use Illuminate\Http\Request;
use Mpociot\BotMan\BotMan;
use GuzzleHttp\Client;
use Mpociot\BotMan\BotManFactory;
use SslCertificate;
use App\SubscribedUser;
use Carbon\Carbon;

class BotManController extends Controller
{
    public function handle()
    {
        $botman = BotManFactory::create(config('services.botman'));

        $botman->hears('ssl-info {domain}', function($bot, $domain) {
            $bot->types();
            $domain = str_replace(['http://', 'https://', 'www.', '/'], '', $domain);
            if(!filter_var(gethostbyname($domain), FILTER_VALIDATE_IP))
            {
                return $bot->reply('Error! Check domain again.');
            }
            try {
                $ssl_info = SslCertificate::createForHostName($domain);

                $message = 'Issuer: '.$ssl_info->getIssuer()."\n\n";

                $is_valid = $ssl_info->isValid() ? 'true' : 'false';
                $message .= 'Is Valid: '.$is_valid."\n\n";

                $now = Carbon::now();
                $expirationDate = $ssl_info->expirationDate()->diffInDays($now);

                $message .= 'Expired In: '.$expirationDate." days\n\n";
            } catch (\Exception $e) {
                $message = 'Error! Check domain again.';
            }
            $bot->reply($message);
        });
        $botman->hears('/subscribe', function($bot) {
            $bot->types();
            $user = $bot->getUser();
            $user_id = $user->getId();
            $username = $user->getUsername();
            $first_name = $user->getFirstName();
            $last_name = $user->getLastName();

            $subscribed_user = SubscribedUser::where('telegram_id', $user_id)->first();

            if (!$subscribed_user) {
                SubscribedUser::create([
                    'telegram_id' => $user_id,
                    'username' => $username,
                    'first_name' => $first_name,
                    'last_name' => $last_name
                ]);
                $msg = 'You successfully subscribed on the mailing of messages from the admin!';
            } else {
                $msg = 'You are already subscribed.';
            }
            $bot->reply($msg);
        });
        $botman->hears('/unsubscribe', function($bot) {
            $bot->types();
            $user = $bot->getUser();
            $user_id = $user->getId();
            $username = $user->getUsername();
            $first_name = $user->getFirstName();
            $last_name = $user->getLastName();

            $subscribed_user = SubscribedUser::where('telegram_id', $user_id)->first();
            if ($subscribed_user) {
                $subscribed_user->delete();
                $msg = 'You successfully unsubscribed on the mailing of messages from the admin!';
            } else {
                $msg = 'You are already subscribed.';
            }
            $bot->reply($msg);
        });
        $botman->hears('/help', function($bot) {
            $bot->types();
            $msg = "You cancontrol this bot with next commands:\n\n";
            $msg .= "ssl-info {domain} - accept info about ssl certificate.\n";
            $msg .= "/subscribe - subscribed to messages from admin.\n";
            $msg .= "/unsubscribe - unsubscribed from messages from admin.\n";
            $bot->reply($msg);
        });

        $botman->listen();
    }
    public function sendAdminMessage(Request $request)
    {
        $botman = BotManFactory::create(config('services.botman'));
        $users = SubscribedUser::all();
        $message = array_get($request, 'message');
        if (!$message) {
            return;
        }
        foreach ($users as $user) {
            $botman->say($message, $user->telegram_id);
        }
    }
    public function form(Request $request)
    {
        return view('index');
    }
}

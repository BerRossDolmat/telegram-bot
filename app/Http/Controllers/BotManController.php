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
            if(!filter_var(gethostbyname($domain), FILTER_VALIDATE_IP))
            {
                return $bot->reply('Error! Check domain again.');
            }
            $ssl_info = SslCertificate::createForHostName($domain);

            $message = 'Issuer: '.$ssl_info->getIssuer()."\n\n";

            $is_valid = $ssl_info->isValid() ? 'true' : 'false';
            $message .= 'Is Valid: '.$is_valid."\n\n";

            $now = Carbon::now();
            $expirationDate = $ssl_info->expirationDate()->diffInDays($now);

            $message .= 'Expired In: '.$expirationDate." days\n\n";
            $bot->reply($message);
        });
        $botman->hears('subscribe', function($bot) {
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
            }
        });
        $botman->hears('unsubscribe', function($bot) {
            $user = $bot->getUser();
            $user_id = $user->getId();
            $username = $user->getUsername();
            $first_name = $user->getFirstName();
            $last_name = $user->getLastName();

            $subscribed_user = SubscribedUser::where('telegram_id', $user_id)->first();

            if ($subscribed_user) {
                $subscribed_user->delete();
            }
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

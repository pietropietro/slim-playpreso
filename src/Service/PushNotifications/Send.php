<?php

declare(strict_types=1);

namespace App\Service\PushNotifications;

use App\Service\BaseService;
use App\Repository\DeviceTokenRepository;
use Pushok\AuthProvider\Token as ApnsToken;
use Pushok\Client as ApnsClient;
use Pushok\Notification as ApnsNotification;
use Pushok\Payload as ApnsPayload;
use Pushok\Payload\Alert as ApnsAlert;
use Kreait\Firebase\Factory as FcmFactory;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification as FcmNotification;


final class Send extends BaseService{
    private $firebaseMessaging;

    public function __construct(
        protected DeviceTokenRepository $deviceTokenRepository,
    ) {
        $firebaseServiceAccount = $_SERVER['FCM_SERVICE_ACCOUNT'];
        $this->firebaseMessaging = (new FcmFactory)->withServiceAccount($firebaseServiceAccount)->createMessaging();
    }

    public function hasToken(int $userId){
        return $this->deviceTokenRepository->hasToken($userId);
    }

    public function send(int $userId, string $title, string $body)
    {
        $tokens = $this->deviceTokenRepository->getTokensByUserId($userId);

        foreach ($tokens as $token) {
            if ($token['platform'] === 'ios') {
                $this->sendApnsNotification($token['token'], $title, $body);
            } elseif ($token['platform'] === 'android') {
                $this->sendFcmNotification($token['token'], $title, $body);
            }
        }
    }

    private function sendApnsNotification(string $deviceToken, string $title, string $body)
    {
        // Path to your .p8 APNs authentication file
        // Your Apple Developer team ID
        // Your app's bundle ID
        // Your APNs key ID 

        $authProvider = ApnsToken::create([
            'key_id' => $_SERVER['APNS_KEY_ID'],
            'team_id' => $_SERVER['APNS_TEAM_ID'],
            'app_bundle_id' => $_SERVER['APNS_BUNDLE_ID'],
            'private_key_path' => $_SERVER['APNS_KEY_FILE'],
        ]);

        $environment = $_SERVER['DEBUG'] ? false : true; // false for sandbox, true for production
        $client = new ApnsClient($authProvider, $environment);

        $alert = ApnsAlert::create()->setTitle($title)->setBody($body);
        $payload = ApnsPayload::create()->setAlert($alert);

        $notification = new ApnsNotification($payload, $deviceToken);
        $client->addNotification($notification);
        $client->push(); // Handle response and errors as needed
    }

    private function sendFcmNotification(string $deviceToken, string $title, string $body)
    {
        $message = CloudMessage::withTarget('token', $deviceToken)
            ->withNotification(FcmNotification::create($title, $body));

        $result = $this->firebaseMessaging->send($message); // Handle response and errors as needed
        return;
    }

    
}

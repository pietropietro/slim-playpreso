<?php

declare(strict_types=1);

namespace App\Service\PushNotifications;

use App\Service\BaseService;
use App\Repository\DeviceTokenRepository;
use Pushok\Client as ApnsClient;
use Pushok\Notification as ApnsNotification;
use Pushok\Payload as ApnsPayload;
use Pushok\Payload\Alert as ApnsAlert;
use Kreait\Firebase\Factory as FcmFactory;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification as FcmNotification;


final class Send extends BaseService{

    public function __construct(
        protected DeviceTokenRepository $deviceTokenRepository,
        protected ApnsClient $apnsClient,
        protected FcmFactory $firebaseMessaging
    ) {}

    public function hasToken(int $userId){
        return $this->deviceTokenRepository->hasToken($userId);
    }

    public function send(int $userId, string $title, string $body)
    {
        $tokens = $this->deviceTokenRepository->getTokensByUserId($userId);

        foreach ($tokens as $token) {
            try {
                if ($token['platform'] === 'ios') {
                    $this->sendApnsNotification($token['token'], $title, $body);
                } elseif ($token['platform'] === 'android') {
                    $this->sendFcmNotification($token['token'], $title, $body);
                }
            } catch (\Kreait\Firebase\Exception\Messaging\InvalidMessage $e) {
                // Log the error and handle invalid token, e.g., remove it from the database
                error_log("Invalid FCM registration token for user $userId: {$e->getMessage()}");
                $this->deviceTokenRepository->remove($userId, $token['token']); 
            } catch (\Exception $e) {
                // Log unexpected errors
                error_log("Error sending notification to user $userId: {$e->getMessage()}");
                $this->deviceTokenRepository->remove($userId, $token['token']); 
            }
        }
    }

    private function sendApnsNotification(string $deviceToken, string $title, string $body)
    {
        $alert = ApnsAlert::create()->setTitle($title)->setBody($body);
        $payload = ApnsPayload::create()->setAlert($alert)->setBadge(1)->setCustomValue('route', '/notification');

        $notification = new ApnsNotification($payload, $deviceToken);
        $this->apnsClient->addNotification($notification);
        $this->apnsClient->push(); // Handle response and errors as needed
    }

    private function sendFcmNotification(string $deviceToken, string $title, string $body)
    {
        $message = CloudMessage::withTarget('token', $deviceToken)
            ->withNotification(FcmNotification::create($title, $body))
            ->withData([
                'route' => '/notification',  // Add the route/path or some identifier
            ]);
        $result = $this->firebaseMessaging->send($message); // Handle response and errors as needed
        return;
    }

    
}

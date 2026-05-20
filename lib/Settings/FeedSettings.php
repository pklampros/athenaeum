<?php
namespace OCA\Athenaeum\Settings;

use OCP\AppFramework\Http\TemplateResponse;
use OCP\IConfig;
use OCP\IURLGenerator;
use OCP\Security\ISecureRandom;
use OCP\Settings\ISettings;

class FeedSettings implements ISettings {
    public function __construct(
        private IConfig $config,
        private IURLGenerator $urlGenerator,
        private ISecureRandom $random,
        private ?string $userId,
    ) {}

    public function getForm(): TemplateResponse {
        $token = $this->config->getUserValue($this->userId, 'athenaeum', 'feed_token', '');
        if ($token === '') {
            $token = $this->random->generate(32, ISecureRandom::CHAR_ALPHANUMERIC);
            $this->config->setUserValue($this->userId, 'athenaeum', 'feed_token', $token);
        }
        $url = $this->urlGenerator->linkToRouteAbsolute(
            'athenaeum.item_feed_api.feed',
            ['token' => $token]
        );
        return new TemplateResponse('athenaeum', 'settings/feed', ['feedUrl' => $url], '');
    }

    public function getSection(): string { return 'athenaeum-personal'; }
    public function getPriority(): int { return 20; }  // after the declarative form
}
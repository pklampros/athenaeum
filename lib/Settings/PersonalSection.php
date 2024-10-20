<?php
namespace OCA\Athenaeum\Settings;

use OCP\IL10N;
use OCP\IURLGenerator;
use OCP\Settings\IIconSection;

class PersonalSection implements IIconSection {

        /** @var IL10N */
        private $l;

        /** @var IURLGenerator */
        private $urlGenerator;

        public function __construct(IL10N $l, IURLGenerator $urlGenerator) {
                $this->l = $l;
                $this->urlGenerator = $urlGenerator;
        }

        public function getID() {
                return 'athenaeum-personal'; //or a generic id if feasible
        }

        public function getName() {
                return $this->l->t('Athenaeum');
        }

        public function getPriority() {
                return 98;
        }

        public function getIcon() {
                // return $this->urlGenerator->imagePath('athenaeum', 'img/app.svg');
				return $this->urlGenerator->imagePath('core', 'actions/settings-dark.svg');
        }

}

<div class="section">
	<h2><?php p($l->t('Move item to %s?', [$_['folder']])); ?></h2>
	<p><?php p($_['item']->getTitle()); ?></p>

	<form method="post" action="<?php p(\OC::$server->getURLGenerator()->linkToRoute(
		'athenaeum.item.changeFolder'
	)); ?>">
		<input type="hidden" name="requesttoken"
			value="<?php p(\OCP\Util::callRegister()); ?>">
		<input type="hidden" name="id" value="<?php p($_['item']->getId()); ?>">
		<input type="hidden" name="folder" value="<?php p($_['folder']); ?>">
		<button type="submit" class="primary"><?php p($l->t('Move it')); ?></button>
		<a href="<?php p(\OC::$server->getURLGenerator()->linkToRouteAbsolute(
				'athenaeum.page.itemsDetails', [
				 	'folder' => 'inbox',
				 	'itemId' => $_['item']->getId()
				 ]
			)); ?>"><?php p($l->t('Cancel')); ?></a>
	</form>
</div>
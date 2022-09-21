<?php

/**
 * @file PluginTemplatePlugin.inc.php
 *
 * Copyright (c) 2017-2021 Simon Fraser University
 * Copyright (c) 2017-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class PluginTemplatePlugin
 * @brief Plugin class for the PluginTemplate plugin.
 */
import('lib.pkp.classes.plugins.GenericPlugin');
class AnnouncementMailFixPlugin extends GenericPlugin
{

	/**
	 * @copydoc GenericPlugin::register()
	 */
	public function register($category, $path, $mainContextId = NULL)
	{
		$success = parent::register($category, $path);
		HookRegistry::register('LoadHandler', [$this, 'setPageHandler']);
		if ($success && $this->getEnabled()) {
			// Display the publication statement on the article details page
			HookRegistry::register('Announcement::add', [$this, 'announcementHooks']);
			// HookRegistry::register('Announcement::edit', [$this, 'announcementHooks']);
		}
		return $success;
	}

	/**
	 * Provide a name for this plugin
	 *
	 * The name will appear in the Plugin Gallery where editors can
	 * install, enable and disable plugins.
	 *
	 * @return string
	 */
	public function getDisplayName()
	{
		return __('plugins.generic.announcementMailFix.displayName');
	}

	/**
	 * Provide a description for this plugin
	 *
	 * The description will appear in the Plugin Gallery where editors can
	 * install, enable and disable plugins.
	 *
	 * @return string
	 */
	public function getDescription()
	{
		return __('plugins.generic.announcementMailFix.description');
	}

	public function announcementHooks($hookName, $args)
	{
		if (!$this->getRequest()->getUserVar('sendEmail')) {
			return false;
		};

		$announcement = &$args[0];

		$this->sendAnnouncementMail($announcement);

		return true;
	}

	private function sendAnnouncementMail($announcement)
	{
		import('lib.pkp.classes.notification.managerDelegate.AnnouncementNotificationManager');
		$announcementNotificationManager = new AnnouncementNotificationManager(NOTIFICATION_TYPE_NEW_ANNOUNCEMENT);
		$announcementNotificationManager->initialize($announcement);

		$notificationSubscriptionSettingsDao = DAORegistry::getDAO('NotificationSubscriptionSettingsDAO'); /* @var $notificationSubscriptionSettingsDao NotificationSubscriptionSettingsDAO */
		$userGroupDao = DAORegistry::getDAO('UserGroupDAO'); /* @var $userGroupDao UserGroupDAO */
		$allUsers = $userGroupDao->getUsersByContextId($this->getCurrentContextId());
		while ($user = $allUsers->next()) {
			if ($user->getDisabled()) continue;
			$blockedEmails = $notificationSubscriptionSettingsDao->getNotificationSubscriptionSettings('blocked_emailed_notification', $user->getId(), $this->getCurrentContextId());
			if (!in_array(NOTIFICATION_TYPE_NEW_ANNOUNCEMENT, $blockedEmails)) {
				$announcementNotificationManager->notify($user);
			}
		}
	}

	public function setPageHandler($hookName, $params)
	{
		$page = $params[0];
		switch ($page) {
			case $this->getName():
				define('HANDLER_CLASS', 'AnnouncementMailFixPageHandler');
				$this->import('AnnouncementMailFixPageHandler');

				return true;
				break;
		}

		return false;
	}
}

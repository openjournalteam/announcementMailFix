<?php

import('classes.handler.Handler');
import('plugins.generic.ojtPlugin.helpers.OJTHelper');
import('lib.pkp.classes.plugins.Plugin');

class AnnouncementMailFixPageHandler extends Handler
{
  public function __construct($request)
  {
  }

  public function index($args, $request)
  {
    $templateMgr            = TemplateManager::getManager($request);

    return $templateMgr->display($this->getPlugin()->getTemplateResource('frontend/backlink.html'));
  }

  protected function getPlugin()
  {
    return PluginRegistry::getPlugin('generic', 'announcementmailfixplugin');
  }
}

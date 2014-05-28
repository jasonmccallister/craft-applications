<?php
namespace Craft;

/**
 * Application plugin class
 */
class ApplicationsPlugin extends BasePlugin
{
    public function getName()
    {
        return 'Applications';
    }

    public function getVersion()
    {
        return '0.9';
    }

    public function getDeveloper()
    {
        return 'Jason McCallister';
    }

    public function getDeveloperUrl()
    {
        return 'http://themccallister.com';
    }

    public function hasCpSection()
    {
        return true;
    }

    public function registerCpRoutes()
    {
        return array(
            'applications/forms'                                           => array('action' => 'applications/forms/formIndex'),
            'applications/forms/new'                                       => array('action' => 'applications/forms/editForm'),
            'applications/forms/(?P<formId>\d+)'                           => array('action' => 'applications/forms/editForm'),
            'applications'                                                 => array('action' => 'applications/applicationIndex'),
            'applications/(?P<formHandle>{handle})/new'                    => array('action' => 'applications/editApplication'),
            'applications/(?P<formHandle>{handle})/(?P<applicationId>\d+)' => array('action' => 'applications/editApplication'),
        );
    }
}

<?php
namespace Craft;

// include enums for custom statuses
include(dirname(__FILE__) . '/enums/ApplicationStatus.php');

/**
 * Applications by Jason McCallister
 *
 * @package   Application
 * @author    Jason McCallister
 * @copyright Copyright (c) 2014, Jason McCallister
 * @link      http://themccallister.com
 */
class ApplicationsPlugin extends BasePlugin
{

    /**
     * @return string
     */
    public function getName()
    {
        return Craft::t('Applications');
    }

    /**
     * @return string
     */
    public function getVersion()
    {
        return '1.0';
    }

    /**
     * @return string
     */
    public function getDeveloper()
    {
        return 'Jason McCallister';
    }

    /**
     * @return string
     */
    public function getDeveloperUrl()
    {
        return 'http://themccallister.com';
    }

    /**
     * @return bool
     */
    public function hasCpSection()
    {
        return true;
    }

    /**
     * @return array
     */
    protected function defineSettings()
    {
        return array(
            'notificationEmail' => array(
                AttributeType::Email,
                'required' => true
            ),
            'notificationMessage' => array(
                AttributeType::String,
                'required' => true,
                'default' => "You have a new application submission on your website"
            )
        );
    }

    /**
     * @return string
     */
    public function getSettingsHtml()
    {
       return craft()->templates->render('applications/_settings', array(
           'settings' => $this->getSettings()
       ));
   }

    /**
     * @return array
     */
    public function registerCpRoutes()
    {
        return array(
            'applications/forms' => array(
                'action' => 'applications/forms/index'
            ),
            'applications/forms/new' => array(
                'action' => 'applications/forms/edit'
            ),
            'applications/forms/(?P<formId>\d+)' => array(
                'action' => 'applications/forms/edit'
            ),
            'applications' => array(
                'action' => 'applications/index'
            ),
            'applications/(?P<formHandle>{handle})/new' => array(
                'action' => 'applications/new'
            ),
            'applications/(?P<formHandle>{handle})/(?P<applicationId>\d+)' => array(
                'action' => 'applications/edit'
            )
        );
    }
}

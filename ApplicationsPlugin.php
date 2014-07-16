<?php
namespace Craft;

/**
 * Applications by Jason McCallister
 *
 * @package   Application
 * @author    Jason McCallister
 * @copyright Copyright (c) 2014, Jason McCallister
 * @link      http://themccallister.com
 */

// include enums for custom statuses
include(dirname(__FILE__) . '/enums/ApplicationStatus.php');

class ApplicationsPlugin extends BasePlugin
{

    /**
     * Return the plugin name
     *
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
     * Return the developer name
     *
     * @return string
     */
    public function getDeveloper()
    {
        return 'Jason McCallister';
    }

    /**
     * Return the developer URL
     *
     * @return string
     */
    public function getDeveloperUrl()
    {
        return 'http://themccallister.com';
    }

    /**
     * Tell Craft we have a cp section
     *
     * @return bool
     */
    public function hasCpSection()
    {
        return true;
    }

    /**
     * Define the plugin settings
     *
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
     * Render the plugin settings HTML template
     *
     * @return string
     */
    public function getSettingsHtml()
    {
       return craft()->templates->render('applications/_settings', array(
           'settings' => $this->getSettings()
       ));
    }

    /**
     * Register CP routes, best practice to route to controllers
     *
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

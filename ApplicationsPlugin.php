<?php

namespace Craft;

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
        return '0.9.1';
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
        return 'https://mccallister.io';
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
            'notificationSubject' => array(
                AttributeType::String,
                'required' => true,
                'default' => "You have a new application on your website"
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
    public function getSettingsHtml() {
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
        return [
            'applications/forms' => [
                'action' => 'applications/forms/index'
            ],
            'applications/forms/new' => [
                'action' => 'applications/forms/edit'
            ],
            'applications/forms/(?P<formId>\d+)' => [
                'action' => 'applications/forms/edit'
            ],
            'applications' => [
                'action' => 'applications/index'
            ],
            'applications/(?P<formHandle>{handle})/new' => [
                'action' => 'applications/new'
            ],
            'applications/(?P<formHandle>{handle})/(?P<applicationId>\d+)' => [
                'action' => 'applications/edit'
            ]
        ];
    }
}

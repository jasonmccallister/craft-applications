<?php
namespace Craft;

/**
 * Applications controller
 */
class ApplicationsController extends BaseController
{
    /**
     * Application index
     */
    public function actionApplicationIndex()
    {
        $variables['forms'] = craft()->applications_forms->getAllForms();

        $this->renderTemplate('applications/_index', $variables);
    }

    /**
     * Edit an application.
     *
     * @param array $variables
     * @throws HttpException
     */
    public function actionEditApplication(array $variables = array())
    {
        if (!empty($variables['formHandle']))
        {
            $variables['form'] = craft()->applications_forms->getFormByHandle($variables['formHandle']);
        }
        else if (!empty($variables['formId']))
        {
            $variables['form'] = craft()->applications_forms->getFormById($variables['formId']);
        }

        if (empty($variables['form']))
        {
            throw new HttpException(404);
        }

        // Now let's set up the actual application
        if (empty($variables['application']))
        {
            if (!empty($variables['applicationId']))
            {
                $variables['application'] = craft()->applications->getApplicationById($variables['applicationId']);

                if (!$variables['application'])
                {
                    throw new HttpException(404);
                }
            }
            else
            {
                $variables['application'] = new Applications_ApplicationModel();
                $variables['application']->formId = $variables['form']->id;
            }
        }

        // Tabs
        $variables['tabs'] = array();

        foreach ($variables['form']->getFieldLayout()->getTabs() as $index => $tab)
        {
            // Do any of the fields on this tab have errors?
            $hasErrors = false;

            if ($variables['application']->hasErrors())
            {
                foreach ($tab->getFields() as $field)
                {
                    if ($variables['application']->getErrors($field->getField()->handle))
                    {
                        $hasErrors = true;
                        break;
                    }
                }
            }

            $variables['tabs'][] = array(
                'label' => $tab->name,
                'url'   => '#tab'.($index+1),
                'class' => ($hasErrors ? 'error' : null)
            );
        }

        if (!$variables['application']->id)
        {
            $variables['title'] = Craft::t('Create a new application');
        }
        else
        {
            $variables['title'] = $variables['application']->title;
        }

        // Breadcrumbs
        $variables['crumbs'] = array(
            array(
                'label' => Craft::t('Applications'),
                'url' => UrlHelper::getUrl('applications'
            )),
            array(
                'label' => $variables['form']->name,
                'url' => UrlHelper::getUrl('applications'
            ))
        );

        // Set the "Continue Editing" URL
        $variables['continueEditingUrl'] = 'applications/'.$variables['form']->handle.'/{id}';

        // Render the template!
        $this->renderTemplate('applications/_edit', $variables);
    }

    /**
     * Saves an application.
     */
    public function actionSaveApplication()
    {
        $this->requirePostRequest();

        $applicationId = craft()->request->getPost('applicationId');

        if ($applicationId)
        {
            $application = craft()->applications->getApplicationById($applicationId);

            if (!$application)
            {
                throw new Exception(Craft::t('No application exists with the ID “{id}”', array('id' => $applicationId)));
            }
        }
        else
        {
            $application = new Applications_ApplicationModel();
        }

        // Set the application attributes, defaulting to the existing values for whatever is missing from the post data
        $application->formId = craft()->request->getPost('formId', $application->formId);
        $application->submitDate  = (($submitDate = craft()->request->getPost('submitDate')) ? DateTime::createFromString($submitDate, craft()->timezone) : null);

        $application->getContent()->title = craft()->request->getPost('title', $application->title);
        $application->setContentFromPost('fields');

        if (craft()->applications->saveApplication($application))
        {
            craft()->userSession->setNotice(Craft::t('Application saved.'));
            $this->redirectToPostedUrl($application);
        }
        else
        {
            craft()->userSession->setError(Craft::t('Couldn’t save application.'));

            // Send the application back to the template
            craft()->urlManager->setRouteVariables(array(
                'application' => $application
            ));
        }
    }

    /**
     * Deletes an application.
     */
    public function actionDeleteApplication()
    {
        $this->requirePostRequest();

        $applicationId = craft()->request->getRequiredPost('applicationId');

        if (craft()->elements->deleteElementById($applicationId))
        {
            craft()->userSession->setNotice(Craft::t('Application deleted.'));
            $this->redirectToPostedUrl();
        }
        else
        {
            craft()->userSession->setError(Craft::t('Couldn’t delete application.'));
        }
    }
}

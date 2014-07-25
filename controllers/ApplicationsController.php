<?php
namespace Craft;

class ApplicationsController extends BaseController
{
    /**
     * @var
     */
    private $application;
    private $plugin;

    /**
     * @throws Exception
     */
    public function init()
    {
        $this->plugin = craft()->plugins->getPlugin('applications');

        if (!$this->plugin)
        {
            throw new Exception("Couldn't find the Applications plugin!");
        }
    }

    /**
     * Render the application index
     */
    public function actionIndex()
    {
        $variables['forms'] = craft()->applications_forms->getAllForms();

        $this->renderTemplate('applications/_index', $variables);
    }

    /**
     * Render a application form
     */
    public function actionNew(array $variables = array())
    {

        if (!empty($variables['formHandle']))
        {
            $variables['form'] = craft()->applications_forms->getFormByHandle($variables['formHandle']);
        }
        else if (!empty($variables['formId']))
        {
            $variables['form'] = craft()->applications_forms->getFormById($variables['formId']);
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

        // Set the "Continue Editing" URL
        $variables['continueEditingUrl'] = 'applications/'.$variables['form']->handle.'/{id}';

        // Set a list of the variables to use in the select dropdown.
        $variables['customStatuses'] = array(
            ApplicationStatus::Approved => 'approved',
            ApplicationStatus::Denied   => 'denied',
            ApplicationStatus::Pending  => 'pending',
        );

        // render all the template!
        $this->renderTemplate('applications/_new', $variables);
    }

    /**
     * Edit an application.
     *
     * @param array $variables
     * @throws HttpException
     */
    public function actionEdit(array $variables = array())
    {
        // if formHandle is not empty
        if (!empty($variables['formHandle']))
        {
            // grab the form by the forms handle
            $variables['form'] = craft()->applications_forms->getFormByHandle($variables['formHandle']);
        }
        // if formId is not empty
        else if (!empty($variables['formId']))
        {
            // grab the form by the forms id
            $variables['form'] = craft()->applications_forms->getFormById($variables['formId']);
        }

        // if form is empty
        if (empty($variables['form']))
        {
            throw new HttpException(404);
        }

        // setup the application
        if (empty($variables['application']))
        {
            // if applicationId is NOT empty
            if (!empty($variables['applicationId']))
            {
                // get the applicaiton by its id
                $variables['application'] = craft()->applications->getApplicationById($variables['applicationId']);

                // if application is null
                if (!$variables['application'])
                {
                    // throw 404 exception
                    throw new HttpException(404);
                }
            }
            // if applicationId is empty
            else
            {
                // setup a new application
                $variables['application'] = new Applications_ApplicationModel();
                $variables['application']->formId = $variables['form']->id;
            }
        }

        // tabs
        $variables['tabs'] = array();

        foreach ($variables['form']->getFieldLayout()->getTabs() as $index => $tab)
        {
            // do any of the fields on this tab have errors?
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

        // breadcrumbs
        $variables['crumbs'] = array(
            array(
                'label' => Craft::t('Applications'),
                'url' => UrlHelper::getUrl('applications'
            )),
            array(
                'label' => $variables['form']->name,
                'url' => UrlHelper::getUrl('applications')
            )
        );

        // set the "Continue Editing" URL
        $variables['continueEditingUrl'] = 'applications/'.$variables['form']->handle.'/{id}';

        // set a list of the enums to use in the status select dropdown
        $variables['customStatuses'] = array(
            ApplicationStatus::Approved => 'approved',
            ApplicationStatus::Denied   => 'denied',
            ApplicationStatus::Pending  => 'pending',
        );

        // render all the css!
        craft()->templates->includeCssResource('applications/css/applications.css');

        // render the template!
        $this->renderTemplate('applications/_edit', $variables);
    }

    public function actionSubmit()
    {
        // require POST
        $this->requirePostRequest();

        // assume new element type
        $application = new Applications_ApplicationModel();

        // assign the attributes specific to the element type
        $application->formId    = craft()->request->getPost('formId');
        $application->firstName = craft()->request->getPost('firstName');
        $application->lastName  = craft()->request->getPost('lastName');
        $application->email     = craft()->request->getPost('email');
        $application->phone     = craft()->request->getPost('phone');

        // if form id does NOT exist
        if (!$application->formId)
        {
            // @TODO figure out how to return errors/validation
            dd('need a form id');
        }
        // if form id exists but we can't find it in the system
        elseif (craft()->applications_forms->getFormById($application->formId) == null)
        {
            // @TODO figure out how to return errors/validation
            dd('form doesnt exist');
        }

        // setup a new form model
        $form = new Applications_FormModel();

        // shared attributes
        $form->id = $application->formId;

        // set the field layout
        $fieldLayout = craft()->fields->assembleLayoutFromPost();
        $fieldLayout->type = ElementType::Asset;
        $form->setFieldLayout($fieldLayout);

        // set content from post with the fields namespace
        $application->setContentFromPost('fields');

        // Save it
        if (craft()->applications->save($application))
        {
            $this->redirectToPostedUrl($application);
        }
        else
        {
            dd('not saved');
        }

    }

    /**
     * Saves an application.
     */
    public function actionSave()
    {
        // require post request
        $this->requirePostRequest();

        // set the applicationId from POST
        $applicationId = craft()->request->getPost('applicationId');

        // if applicationId, assume we are editing and get the application by id
        if ($applicationId)
        {
            // grab the application by the id
            $application = craft()->applications->getApplicationById($applicationId);

            // if application id does not exist
            if (!$application)
            {
                // throw new exception that the application does not exist
                throw new Exception(Craft::t('No application exists with the ID “{id}”', array(
                    'id' => $applicationId
                    )
                ));
            }
        }
        // else assume this is a new application
        else
        {
            // setup a new application
            $application = new Applications_ApplicationModel();
        }

        // set the application attributes from POST
        $application->formId     = craft()->request->getPost('formId', $application->formId);
        $application->firstName  = craft()->request->getPost('firstName');
        $application->lastName   = craft()->request->getPost('lastName');
        $application->email      = craft()->request->getPost('email');
        $application->phone      = craft()->request->getPost('phone');

        // set the content from POST
        $application->setContentFromPost('fields');

        // if we could save the application
        if (craft()->applications->save($application))
        {
            // notify the user that the application saved
            craft()->userSession->setNotice(Craft::t('Application saved.'));
            $this->redirectToPostedUrl($application);
        }
        // if we could NOT save the application
        else
        {
            // notify the user the application did NOT save
            craft()->userSession->setError(Craft::t('Couldn’t save application.'));

            // send the user back to the edit template
            craft()->urlManager->setRouteVariables(array(
                'application' => $application
            ));
        }
    }

    /**
     * Changes an application to approved.
     */
    public function actionApprove()
    {
        $this->requirePostRequest();

        $status = ApplicationStatus::Approved;
        $applicationId = craft()->request->getRequiredPost('applicationId');

        if (craft()->applications->updateStatus($applicationId, $status))
        {
            craft()->userSession->setNotice(Craft::t('The application was {status}.', array(
                'status' => $status
            )));
            $this->redirectToPostedUrl();
        }
        else
        {
            craft()->userSession->setError(Craft::t('Unable to change the application to {status}.', array(
                'status' => $status
            )));
        }
    }

    /**
     * Changes an application to denied.
     */
    public function actionDeny()
    {
        $this->requirePostRequest();

        $status  = ApplicationStatus::Denied;
        $applicationId = craft()->request->getRequiredPost('applicationId');

        if (craft()->applications->updateStatus($applicationId, $status))
        {
            craft()->userSession->setNotice(Craft::t('The application was {status}.', array(
                'status' => $status
            )));
            $this->redirectToPostedUrl();
        }
        else
        {
            craft()->userSession->setError(Craft::t('Unable to change the application to {status}.', array(
                'status' => $status
            )));
        }
    }

    /**
     * Changes an application to pending.
     */
    public function actionPending()
    {
        $this->requirePostRequest();

        $status  = ApplicationStatus::Pending;
        $applicationId = craft()->request->getRequiredPost('applicationId');

        if (craft()->applications->updateStatus($applicationId, $status))
        {
            craft()->userSession->setNotice(Craft::t('The application was {status}.', array(
                'status' => $status
            )));
        }
        else
        {
            craft()->userSession->setError(Craft::t('Unable to change the application to {status}.', array(
                'status' => $status
            )));
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

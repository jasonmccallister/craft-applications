<?php
namespace Craft;

class Applications_FormsController extends BaseController
{
    /**
     * Form index
     */
    public function actionIndex()
    {
        $variables['forms'] = craft()->applications_forms->getAllForms();

        $this->renderTemplate('applications/forms', $variables);
    }

    /**
     * Edit a form.
     *
     * @param array $variables
     * @throws HttpException
     * @throws Exception
     */
    public function actionEdit(array $variables = array())
    {
        $variables['brandNewForm'] = false;

        if (!empty($variables['formId']))
        {
            if (empty($variables['form']))
            {
                $variables['form'] = craft()->applications_forms->getFormById($variables['formId']);

                if (!$variables['form'])
                {
                    throw new HttpException(404);
                }
            }

            $variables['title'] = $variables['form']->name;
        }
        else
        {
            if (empty($variables['form']))
            {
                $variables['form'] = new Applications_FormModel();
                $variables['brandNewForm'] = true;
            }

            $variables['title'] = Craft::t('Create a new form');
        }

        $variables['crumbs'] = array(
            array(
                'label' => Craft::t('Applications'),
                'url' => UrlHelper::getUrl('applications')
            ),
            array(
                'label' => Craft::t('Forms'),
                'url' => UrlHelper::getUrl('applications/forms')
            ),
        );

        $this->renderTemplate('applications/forms/_edit', $variables);
    }

    /**
     * Saves a form
     */
    public function actionSaveForm()
    {
        $this->requirePostRequest();

        $form = new Applications_FormModel();

        // Shared attributes
        $form->id     = craft()->request->getPost('formId');
        $form->name   = craft()->request->getPost('name');
        $form->handle = craft()->request->getPost('handle');

        // Set the field layout
        $fieldLayout = craft()->fields->assembleLayoutFromPost();
        $fieldLayout->type = ElementType::Asset;
        $form->setFieldLayout($fieldLayout);

        // Save it
        if (craft()->applications_forms->saveForm($form))
        {
            craft()->userSession->setNotice(Craft::t('Form saved.'));
            $this->redirectToPostedUrl($form);
        }
        else
        {
            craft()->userSession->setError(Craft::t('Couldn’t save the form.'));
        }

        // Send the form back to the template
        craft()->urlManager->setRouteVariables(array(
            'form' => $form
        ));
    }

    /**
     * Deletes a form.
     */
    public function actionDeleteForm()
    {
        $this->requirePostRequest();
        $this->requireAjaxRequest();

        $formId = craft()->request->getRequiredPost('id');

        craft()->applications_forms->deleteFormById($formId);
        $this->returnJson(array(
            'success' => true
        ));
    }
}

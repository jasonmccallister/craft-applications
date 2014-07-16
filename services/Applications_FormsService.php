<?php
namespace Craft;

/**
 * Class Applications_FormsService
 * @package Craft
 */
class Applications_FormsService extends BaseApplicationComponent
{
    private $_allFormIds;
    private $_formsById;
    private $_fetchedAllForms = false;

    /**
     * Returns all of the form IDs.
     *
     * @return array
     */
    public function getAllFormIds()
    {
        if (!isset($this->_allFormIds))
        {
            if ($this->_fetchedAllForms)
            {
                $this->_allFormIds = array_keys($this->_formsById);
            }
            else
            {
                $this->_allFormIds = craft()->db->createCommand()
                    ->select('id')
                    ->from('applications_forms')
                    ->queryColumn();
            }
        }

        return $this->_allFormIds;
    }

    /**
     * Returns all forms.
     *
     * @param string|null $indexBy
     * @return array
     */
    public function getAllForms($indexBy = null)
    {
        if (!$this->_fetchedAllForms)
        {
            $formRecords = Applications_FormRecord::model()->ordered()->findAll();
            $this->_formsById = Applications_FormModel::populateModels($formRecords, 'id');
            $this->_fetchedAllForms = true;
        }

        if ($indexBy == 'id')
        {
            return $this->_formsById;
        }
        else if (!$indexBy)
        {
            return array_values(
                $this->_formsById
            );
        }
        else
        {
            $forms = array();

            foreach ($this->_formsById as $form)
            {
                $forms[$form->$indexBy] = $form;
            }

            return $forms;
        }
    }

    /**
     * Gets the total number of forms.
     *
     * @return int
     */
    public function getTotalForms()
    {
        return count(
            $this->getAllFormIds()
        );
    }

    /**
     * Returns a form by its ID.
     *
     * @param $formId
     * @return Applications_FormModel|null
     */
    public function getFormById($formId)
    {
        if (!isset($this->_formsById) || !array_key_exists($formId, $this->_formsById))
        {
            $formRecord = Applications_FormRecord::model()->findById($formId);

            if ($formRecord)
            {
                $this->_formsById[$formId] = Applications_FormModel::populateModel($formRecord);
            }
            else
            {
                $this->_formsById[$formId] = null;
            }
        }

        return $this->_formsById[$formId];
    }

    /**
     * Gets a form by its handle.
     *
     * @param string $formHandle
     * @return Applications_FormModel|null
     */
    public function getFormByHandle($formHandle)
    {
        $formRecord = Applications_FormRecord::model()->findByAttributes(array(
            'handle' => $formHandle
        ));

        if ($formRecord)
        {
            return Applications_FormModel::populateModel($formRecord);
        }
    }

    /**
     * Saves a form.
     *
     * @param Applications_FormModel $form
     * @throws \Exception
     * @return bool
     */
    public function saveForm(Applications_FormModel $form)
    {
        if ($form->id)
        {
            $formRecord = Applications_FormRecord::model()->findById($form->id);

            if (!$formRecord)
            {
                throw new Exception(Craft::t('No form exists with the ID “{id}”', array(
                    'id' => $form->id
                )));
            }

            $oldForm = Applications_FormModel::populateModel($formRecord);
            $isNewForm = false;
        }
        else
        {
            $formRecord = new Applications_FormRecord();
            $isNewForm = true;
        }

        $formRecord->name       = $form->name;
        $formRecord->handle     = $form->handle;

        $formRecord->validate();
        $form->addErrors($formRecord->getErrors());

        if (!$form->hasErrors())
        {
            $transaction = craft()->db->getCurrentTransaction() === null ? craft()->db->beginTransaction() : null;
            try
            {
                if (!$isNewForm && $oldForm->fieldLayoutId)
                {
                    // Drop the old field layout
                    craft()->fields->deleteLayoutById($oldForm->fieldLayoutId);
                }

                // Save the new one
                $fieldLayout = $form->getFieldLayout();
                craft()->fields->saveLayout($fieldLayout);

                // Update the form record/model with the new layout ID
                $form->fieldLayoutId = $fieldLayout->id;
                $formRecord->fieldLayoutId = $fieldLayout->id;

                // Save it!
                $formRecord->save(false);

                // Now that we have a form ID, save it on the model
                if (!$form->id)
                {
                    $form->id = $formRecord->id;
                }

                // Might as well update our cache of the form while we have it.
                $this->_formsById[$form->id] = $form;

                if ($transaction !== null)
                {
                    $transaction->commit();
                }
            }
            catch (\Exception $e)
            {
                if ($transaction !== null)
                {
                    $transaction->rollback();
                }

                throw $e;
            }

            return true;
        }
        else
        {
            return false;
        }
    }

    /**
     * Deletes a form by its ID.
     *
     * @param int $formId
     * @throws \Exception
     * @return bool
     */
    public function deleteFormById($formId)
    {
        if (!$formId)
        {
            return false;
        }

        $transaction = craft()->db->getCurrentTransaction() === null ? craft()->db->beginTransaction() : null;
        try
        {
            // Delete the field layout
            $fieldLayoutId = craft()->db->createCommand()
                ->select('fieldLayoutId')
                ->from('applications_forms')
                ->where(array(
                    'id' => $formId
                ))
                ->queryScalar();

            if ($fieldLayoutId)
            {
                craft()->fields->deleteLayoutById($fieldLayoutId);
            }

            // Grab the application ids so we can clean the elements table.
            $applicationIds = craft()->db->createCommand()
                ->select('id')
                ->from('applications')
                ->where(array(
                    'formId' => $formId
                ))
                ->queryColumn();

            craft()->elements->deleteElementById($applicationIds);

            $affectedRows = craft()->db->createCommand()->delete('applications_forms', array(
                'id' => $formId
            ));

            if ($transaction !== null)
            {
                $transaction->commit();
            }

            return (bool) $affectedRows;
        }
        catch (\Exception $e)
        {
            if ($transaction !== null)
            {
                $transaction->rollback();
            }

            throw $e;
        }
    }
}

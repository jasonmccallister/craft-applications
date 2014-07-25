<?php
namespace Craft;

class ApplicationsService extends BaseApplicationComponent
{
    /**
     * Returns an application by its ID
     *
     * @param int $applicationId
     * @return Applications_ApplicationModel|null
     */
    public function getApplicationById($applicationId)
    {
        return craft()->elements->getElementById($applicationId, 'Applications_Application');
    }

    /**
     * Returns all notes by application ID
     *
     * @param $applicationId
     * @return Applications_NoteModel|null
     */
    public function getNotesByApplicationId($applicationId)
    {
        $query = craft()->db->createCommand()
            ->select('*')
            ->from('applications_notes')
            ->where('applicationId = :applicationId', array(
                ':applicationId' => $applicationId
            ))
            ->queryRow();

        $model = Applications_NoteModel::populateModel($query);

        return $model;
    }


    /**
     * Update an applications status
     *
     * @param $applicationId
     * @param $status
     * @throws Exception
     */
    public function updateStatus($applicationId, $status)
    {
        $record = Applications_ApplicationRecord::model()->findById($applicationId);

        if ($record)
        {
            $record->setAttribute('status', $status);

            return $record->save();
        }
        else
        {
            throw new Exception(Craft::t('No record was found with id {id}', array(
                'id' => $applicationId
            )));
        }
    }

    /**
     * Save an application
     *
     * @param Applications_ApplicationModel $application
     * @return bool
     * @throws Exception
     * @throws \CDbException
     * @throws \Exception
     */
    public function save(Applications_ApplicationModel $application)
    {
        $isNewApplication = !$application->id;

        // Application data
        if (!$isNewApplication)
        {
            $applicationRecord = Applications_ApplicationRecord::model()->findById($application->id);

            if (!$applicationRecord)
            {
                throw new Exception(Craft::t('No application exists with the ID “{id}”', array(
                    'id' => $application->id
                    )
                ));
            }
        }
        else
        {
            $applicationRecord = new Applications_ApplicationRecord();
        }

        $applicationRecord->formId     = $application->formId;
        $applicationRecord->firstName  = $application->firstName;
        $applicationRecord->lastName   = $application->lastName;
        $applicationRecord->email      = $application->email;
        $applicationRecord->phone      = $application->phone;

        $applicationRecord->validate();
        $application->addErrors($applicationRecord->getErrors());

        if (!$application->hasErrors())
        {
            $transaction = craft()->db->getCurrentTransaction() === null ? craft()->db->beginTransaction() : null;
            try
            {

                // Fire an 'onBeforeSaveApplication' event
                $this->onBeforeSave(new Event($this, array(
                    'application'      => $application,
                    'isNewApplication' => $isNewApplication
                )));

                if (craft()->elements->saveElement($application))
                {
                    // Now that we have an element ID, save it on the other stuff
                    if ($isNewApplication)
                    {
                        $applicationRecord->id = $application->id;
                    }

                    $applicationRecord->save(false);

                    // Fire an 'onSaveEvent' event
                    $this->onSave(new Event($this, array(
                        'application'      => $application,
                        'isNewApplication' => $isNewApplication
                    )));

                    if ($transaction !== null)
                    {
                        $transaction->commit();
                    }

                    return true;
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
        }

        return false;
    }

    // Events

    /**
     * Fires an 'onBeforeSaveApplication' event.
     *
     * @param Event $event
     */
    public function onBeforeSave(Event $event)
    {
        $this->raiseEvent('onBeforeSave', $event);
    }

    /**
     * Fires an 'onSaveApplication' event.
     *
     * @param Event $event
     */
    public function onSave(Event $event)
    {
        $this->raiseEvent('onSave', $event);

        $settings = craft()->plugins->getPlugin('applications')->getSettings();

        if (!empty($settings->notificationEmail)) {
            $email = new EmailModel();
            $email->toEmail = $settings->notificationEmail;
            $email->subject = $settings->notificationSubject;
            $email->body    = $settings->notificationMessage;

            craft()->email->sendEmail($email);
        }
    }
}

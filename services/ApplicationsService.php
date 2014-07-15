<?php
namespace Craft;

/**
 * Class ApplicationsService
 * @package Craft
 */
class ApplicationsService extends BaseApplicationComponent
{
    /**
     * Returns an application by its ID.
     *
     * @param int $applicationId
     * @return Applications_ApplicationModel|null
     */
    public function getApplicationById($applicationId)
    {
        return craft()->elements->getElementById($applicationId, 'Applications_Application');
    }


    /**
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
                throw new Exception(Craft::t('No application exists with the ID “{id}”', array('id' => $application->id)));
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
        // @TODO uncomment when you determine how to add enums to dropdown
        $applicationRecord->status     = $application->status;
        $applicationRecord->submitDate = $application->submitDate;

        $applicationRecord->validate();
        $application->addErrors($applicationRecord->getErrors());

        if (!$application->hasErrors())
        {
            $transaction = craft()->db->getCurrentTransaction() === null ? craft()->db->beginTransaction() : null;
            try
            {
                // Set a default status of pending, if one wasn't supplied.
				if (!$application->status)
				{
					$application->status = ApplicationStatus::Pending;
				}

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
    }
}

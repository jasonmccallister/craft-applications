<?php
namespace Craft;

/**
 * Applications - Application model
 */
class Applications_ApplicationModel extends BaseElementModel
{
    protected $elementType = 'Applications_Application';

    /**
     * Use the element's applicant name as its string representation.
     *
     * @return string
     */
    function __toString()
    {
        return $this->id;
    }

    /**
     * Use the element's custom statuses
     *
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @access protected
     * @return array
     */
    protected function defineAttributes()
    {
        return array_merge(
            parent::defineAttributes(), array(
                'formId'     => AttributeType::Number,
                'firstName'  => AttributeType::String,
                'lastName'   => AttributeType::String,
                'email'      => AttributeType::Email,
                'phone'      => AttributeType::String,
                'status'     => array(
                    AttributeType::Enum,
                    'values' => array(
                        ApplicationStatus::Approved,
                        ApplicationStatus::Denied,
                        ApplicationStatus::Pending
                    ),
                    'default' => ApplicationStatus::Pending
                ),
                // 'submitDate'  => AttributeType::DateTime
            )
        );
    }

    /**
     * Returns whether the current user can edit the element.
     *
     * @return bool
     */
    public function isEditable()
    {
        return true;
    }

    /**
     * Returns the element's CP edit URL.
     *
     * @return string|false
     */
    public function getCpEditUrl()
    {
        $form = $this->getForm();

        if ($form)
        {
            return UrlHelper::getCpUrl('applications/'.$form->handle.'/'.$this->id);
        }
    }

    /**
     * Returns the field layout used by this element.
     *
     * @return FieldLayoutModel|null
     */
    public function getFieldLayout()
    {
        $form = $this->getForm();

        if ($form)
        {
            return $form->getFieldLayout();
        }
    }

    /**
     * Returns the application's form.
     *
     * @return Applications_FormModel|null
     */
    public function getForm()
    {
        if ($this->formId)
        {
            return craft()->applications_forms->getFormById($this->formId);
        }
    }

}

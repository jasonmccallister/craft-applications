<?php
namespace Craft;

/**
 * Applications - Application record
 */
class Applications_ApplicationRecord extends BaseRecord
{
    /**
     * @return string
     */
    public function getTableName()
    {
        return 'applications';
    }

    /**
     * @access protected
     * @return array
     */
    protected function defineAttributes()
    {
        return array(
            'applicantName' => array(
                AttributeType::String, 'required' => true
            ),
            'applicantEmail' => array(
                AttributeType::String, 'required' => true
            ),
            'agreedToTerms' => array(
                AttributeType::Bool, 'required' => true
            ),
            'submitDate' => array(
                AttributeType::DateTime, 'required' => true
            ),
        );
    }

    /**
     * @return array
     */
    public function defineRelations()
    {
        return array(
            'element' => array(
                static::BELONGS_TO, 'ElementRecord', 'id', 'required' => true, 'onDelete' => static::CASCADE
            ),
            'form'    => array(
                static::BELONGS_TO, 'Applications_FormRecord', 'required' => true, 'onDelete' => static::CASCADE
            ),
        );
    }
}

<?php
namespace Craft;

/**
 * Applications - Form record
 */
class Applications_FormRecord extends BaseRecord
{
    /**
     * @return string
     */
    public function getTableName()
    {
        return 'applications_forms';
    }

    /**
     * @access protected
     * @return array
     */
    protected function defineAttributes()
    {
        return array(
            'name'          => array(
                AttributeType::Name, 'required' => true
            ),
            'handle'        => array(
                AttributeType::Handle, 'required' => true
            ),
            'fieldLayoutId' => AttributeType::Number,
        );
    }

    /**
     * @return array
     */
    public function defineRelations()
    {
        return array(
            'fieldLayout'  => array(
                static::BELONGS_TO, 'FieldLayoutRecord', 'onDelete' => static::SET_NULL
            ),
            'applications' => array(
                static::HAS_MANY, 'Applications_ApplicationRecord', 'applicationId'
            ),
        );
    }

    /**
     * @return array
     */
    public function defineIndexes()
    {
        return array(
            array(
                'columns' => array(
                    'name'
                ), 'unique' => true
            ),
            array(
                'columns' => array(
                    'handle'
                ), 'unique' => true
            ),
        );
    }

    /**
     * @return array
     */
    public function scopes()
    {
        return array(
            'ordered' => array(
                'order' => 'name'
            ),
        );
    }
}

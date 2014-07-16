<?php
namespace Craft;

class Applications_NoteRecord extends BaseRecord
{
    public function getTableName()
    {
        return 'applications_notes';
    }

    public function defineAttributes()
    {
        return array(
            'name' => array(
                AttributeType::Name,
                'required' => true
            ),
			'comment' => array(
                AttributeType::String,
                'required' => true
            )
        );
    }

    /**
     * @return array
     */
    public function defineRelations()
    {
        return array(
            'application' => array(
                static::BELONGS_TO,
                'Applications_ApplicationRecord',
                'required' => true,
                'onDelete' => static::CASCADE
            ),
        );
    }
}

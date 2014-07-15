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
			'noteDate' => array(
                AttributeType::DateTime,
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
        // @TODO looks at the proper relationship for notes to
        // applications
        return array(
            'applications' => array(
                static::HAS_MANY,
                'Applications_ApplicationRecord',
                'id',
                'required' => true,
                'onDelete' => static::CASCADE
            ),
        );
    }

}

<?php
namespace Craft;

class Applications_NoteModel extends BaseModel
{

    protected function defineAttributes()
    {
        return array(
            'author' => array(
                AttributeType::String,
                'required' => true
            ),
            'comment' => array(
                AttributeType::String,
                'required' => true
            )
        );
    }

}

<?php
namespace Craft;

class ApplicationsFieldType extends BaseElementFieldType
{
    /**
     * @access protected
     * @var string $elementType The element type this field deals with.
     */
    protected $elementType = 'Applications_Application';

    /**
     * Returns the label for the "Add" button.
     *
     * @access protected
     * @return string
     */
    protected function getAddButtonLabel()
    {
        return Craft::t('Add an application');
    }
}

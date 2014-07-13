<?php
namespace Craft;

/**
 * Class ApplicationsVariable
 * @package Craft
 */
class ApplicationsVariable
{
    /**
     * @return ElementCriteriaModel
     * @throws Exception
     */
    function applications()
	{
		return craft()->elements->getCriteria('Applications_Application');
	}
}

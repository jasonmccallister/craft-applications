<?php
namespace Craft;

class ApplicationsVariable
{
	function applications()
	{
		return craft()->elements->getCriteria('Applications_Application');
	}
}

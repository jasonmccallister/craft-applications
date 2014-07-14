<?php
namespace Craft;

abstract class ApplicationsApplicationStatus extends BaseEnum
{
	const Approved = 'approved';
	const Denied   = 'denied';
	const Pending  = 'pending';
}

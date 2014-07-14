<?php
namespace Craft;

abstract class ApplicationStatus extends BaseEnum
{
	const Approved = 'approved';
	const Denied   = 'denied';
	const Pending  = 'pending';
}

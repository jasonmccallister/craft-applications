<?php namespace Craft;

class Applications_ApplicationRecord extends BaseRecord {

	/**
	 * Get the table name.
	 *
	 * @return string
	 */
	public function getTableName()
	{
		return 'applications';
	}

	/**
	 * Define the attributes for the record.
	 *
	 * @access protected
	 * @return array
	 */
	protected function defineAttributes()
	{
		return array(
			'firstName' => array(
				AttributeType::String,
				'required' => true
			),
			'lastName'  => array(
				AttributeType::String,
				'required' => true
			),
			'email'     => array(
				AttributeType::Email,
				'required' => true
			),
			'phone'     => array(
				AttributeType::String,
				'required' => true
			),
			'status'    => array(
				AttributeType::Enum,
				'values'  => array(
					ApplicationStatus::Approved,
					ApplicationStatus::Denied,
					ApplicationStatus::Pending
				),
				'default' => ApplicationStatus::Pending
			),
		);
	}

	/**
	 * Define the application relations.
	 *
	 * @return array
	 */
	public function defineRelations()
	{
		return array(
			'element' => array(
				static::BELONGS_TO,
				'ElementRecord',
				'id',
				'required' => true,
				'onDelete' => static::CASCADE
			),
			'form'    => array(
				static::BELONGS_TO,
				'Applications_FormRecord',
				'required' => true,
				'onDelete' => static::CASCADE
			),
		);
	}
}

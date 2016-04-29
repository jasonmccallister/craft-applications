<?php
namespace Craft;

class Applications_ApplicationElementType extends BaseElementType {

	/**
	 * Returns the element type name.
	 *
	 * @return string
	 */
	public function getName()
	{
		return Craft::t('Applications');
	}

	/**
	 * Returns whether this element type has content.
	 *
	 * @return bool
	 */
	public function hasContent()
	{
		return true;
	}

	/**
	 * Returns whether this element type has titles.
	 *
	 * @return bool
	 */
	public function hasTitles()
	{
		return false;
	}

	/**
	 * Returns whether this element type can have statuses.
	 *
	 * @return bool
	 */
	public function hasStatuses()
	{
		return true;
	}

	/**
	 * Returns all of the possible statuses that elements of this type may have.
	 *
	 * @return array|null
	 */
	public function getStatuses()
	{
		return array(
			ApplicationStatus::Approved => Craft::t('Approved'),
			ApplicationStatus::Denied   => Craft::t('Denied'),
			ApplicationStatus::Pending  => Craft::t('Pending'),
		);
	}

	/**
	 * Returns this element type's sources.
	 *
	 * @param string|null $context
	 * @return array|false
	 */
	public function getSources($context = null)
	{
		$sources = array(
			'*' => array(
				'label' => Craft::t('All Applications'),
			)
		);

		foreach (craft()->applications_forms->getAllForms() as $form) {
			$key = 'form:' . $form->id;

			$sources[$key] = array(
				'label'    => $form->name,
				'criteria' => array(
					'formId' => $form->id
				)
			);
		}

		return $sources;
	}

	/**
	 * Returns the attributes that can be shown/sorted by in table views.
	 *
	 * @param string|null $source
	 * @return array
	 */
	public function defineTableAttributes($source = null)
	{
		return array(
			'id'          => Craft::t('Application ID'),
			'firstName'   => Craft::t('First Name'),
			'lastName'    => Craft::t('Last Name'),
			'email'       => Craft::t('Email'),
			'phone'       => Craft::t('Phone'),
			'dateCreated' => Craft::t('Date Created')
		);
	}

	/**
	 * Defines which model attributes should be searchable.
	 *
	 * @return array
	 */
	public function defineSearchableAttributes()
	{
		return array(
			'firstName',
			'lastName',
			'email',
			'phone',
			'dateCreated'
		);
	}

	/**
	 * Returns the table view HTML for a given attribute.
	 *
	 * @param BaseElementModel $element
	 * @param string $attribute
	 * @return string
	 */
	public function getTableAttributeHtml(BaseElementModel $element, $attribute)
	{
		switch ($attribute) {

			case 'email': {
				$email = $element->email;

				if ($email) {
					return '<a href="mailto:' . $email . '">' . $email . '</a>';
				}
				else {
					return '';
				}
			}

			// @TODO fix this to sort by date created since the date field is not actually an element attribute.
			case 'dateCreated': {
				$date = $element->$attribute;

				if ($date) {
					return $date->localeDate();
				}
				else {
					return '';
				}
			}

			default: {
				return parent::getTableAttributeHtml($element, $attribute);
			}
		}
	}

	/**
	 * Defines any custom element criteria attributes for this element type.
	 *
	 * @return array
	 */
	public function defineCriteriaAttributes()
	{
		return array(
			'id'        => AttributeType::Mixed,
			'form'      => AttributeType::Mixed,
			'formId'    => AttributeType::Mixed,
			'firstName' => AttributeType::String,
			'lastName'  => AttributeType::String,
			'email'     => AttributeType::Email,
			'phone'     => AttributeType::String,
			'status'    => array(
				AttributeType::Enum,
				'values'  => array(
					ApplicationStatus::Approved,
					ApplicationStatus::Denied,
					ApplicationStatus::Pending
				),
				'default' => ApplicationStatus::Pending
			),
			// @TODO might remove, look into this later
			// 'dateCreated'  => AttributeType::Mixed,
			'order'     => array(
				AttributeType::String,
				'default' => 'applications.dateCreated asc'
			),
		);
	}

	/**
	 * Returns the element query condition for a custom status criteria.
	 *
	 * @param DbCommand $query
	 * @param string $status
	 * @return string|false
	 */
	public function getElementQueryStatusCondition(DbCommand $query, $status)
	{
		return 'applications.status = "' . $status . '"';
	}

	/**
	 * Modifies an element query targeting elements of this type.
	 *
	 * @param DbCommand $query
	 * @param ElementCriteriaModel $criteria
	 * @return mixed
	 */
	public function modifyElementsQuery(DbCommand $query, ElementCriteriaModel $criteria)
	{
		// you must add the columns here when adding a new field
		$query->addSelect('applications.formId, applications.firstName,
            applications.lastName, applications.email, applications.status,
            applications.phone, applications.dateCreated,')
			->join('applications applications', 'applications.id = elements.id');

		if ($criteria->formId) {
			$query->andWhere(DbHelper::parseParam('applications.formId', $criteria->formId, $query->params));
		}

		if ($criteria->form) {
			$query->join('applications_forms applications_forms', 'applications_forms.id = applications.formId');
			$query->andWhere(DbHelper::parseParam('applications_forms.handle', $criteria->form, $query->params));
		}

		if ($criteria->dateCreated) {
			$query->andWhere(DbHelper::parseDateParam('entries.dateCreated', $criteria->dateCreated, $query->params));
		}

	}

	/**
	 * Populates an element model based on a query result.
	 *
	 * @param array $row
	 * @return array
	 */
	public function populateElementModel($row)
	{
		return Applications_ApplicationModel::populateModel($row);
	}

	/**
	 * Returns the HTML for an editor HUD for the given element.
	 *
	 * @param BaseElementModel $element
	 * @return string
	 */
	public function getEditorHtml(BaseElementModel $element)
	{
		// Start/End Dates
		$html = craft()->templates->render('applications/_edit', array(
			'element' => $element,
		));

		// Everything else
		$html .= parent::getEditorHtml($element);

		return $html;
	}
}

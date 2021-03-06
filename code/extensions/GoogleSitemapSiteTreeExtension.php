<?php

/**
 * @package googlesitemaps
 */
class GoogleSitemapSiteTreeExtension extends GoogleSitemapExtension {

	/**
	 * @var array
	 */
	private static $db = array(
		"Priority" => "Varchar(5)"
	);

	/**
	 * @param FieldList
	 */
	public function updateSettingsFields(&$fields) {
		$prorities = array(
			'-1' => _t('GoogleSitemaps.PRIORITYNOTINDEXED', "Not indexed"),
			'1.0' => '1 - ' . _t('GoogleSitemaps.PRIORITYMOSTIMPORTANT', "Most important"),
			'0.9' => '2',
			'0.8' => '3',
			'0.7' => '4',
			'0.6' => '5',
			'0.5' => '6',
			'0.4' => '7',
			'0.3' => '8',
			'0.2' => '9',
			'0.1' => '10 - ' . _t('GoogleSitemaps.PRIORITYLEASTIMPORTANT', "Least important")
		);

		$tabset = $fields->findOrMakeTab('Root.Settings');
		
		$message = "<p>";
		$message .= sprintf(_t('GoogleSitemaps.METANOTEPRIORITY', "Manually specify a Google Sitemaps priority for this page (%s)"), 
			'<a href="http://www.google.com/support/webmasters/bin/answer.py?hl=en&answer=71936#prioritize" target="_blank">?</a>'
		);
		$message .=  "</p>";
		
		$tabset->push(new Tab('GoogleSitemap', _t('GoogleSitemaps.TABGOOGLESITEMAP', 'Google Sitemap'),
			new LiteralField("GoogleSitemapIntro", $message),
			$priority = new DropdownField("Priority", $this->owner->fieldLabel('Priority'), $prorities, $this->owner->Priority)
		));

		$priority->setEmptyString(_t('GoogleSitemaps.PRIORITYAUTOSET', 'Auto-set based on page depth'));
	}

	/**
	 * @param FieldList
	 *
	 * @return void
	 */
	public function updateFieldLabels(&$labels) {
		parent::updateFieldLabels($labels);

		$labels['Priority'] = _t('GoogleSitemaps.METAPAGEPRIO', "Page Priority");
	}

	/**
	 * @return boolean
	 */
	public function canIncludeInGoogleSitemap() {
		$result = parent::canIncludeInGoogleSitemap();
		$result = ($this instanceof ErrorPage) ? false : $result;

		return $result;
	}

	/**
	 * @return mixed
	 */
	public function getGooglePriority() {
		$priority = $this->owner->getField('Priority');

		if(!$priority) {
			$parentStack = $this->owner->parentStack();
			$numParents = is_array($parentStack) ? count($parentStack) - 1 : 0;
			
			$num = max(0.1, 1.0 - ($numParents / 10));
			$result = str_replace(",", ".", $num);

			return $result;
		} else if ($priority == -1) {
			return false;
		} else {
			return (is_float($priority) && $priority <= 1.0) ? $priority : 0.5;
		}
	}
}

<?php


namespace PGMB\Google;

use LengthException;
use OutOfBoundsException;
use OverflowException;
use PGMB\MbString;
use PGMB\Util\UTF16CodeUnitsUtil;

/**
 * Class LocalPost
 * @package PGMB\Google
 *
 * @property string $summary
 * @property string $languageCode
 * @property string $topicType
 * @property array $callToAction
 * @property array $offer
 * @property array $media
 * @property array $event
 */
class LocalPost extends AbstractGoogleJsonObject {
	/**
	 * Array containing valid Google post types.
	 *
	 * @var array
	 */
	private $localPostTopicTypes = [
		'LOCAL_POST_TOPIC_TYPE_UNSPECIFIED',
		'STANDARD',
		'EVENT',
		'OFFER',
		'PRODUCT',
		'ALERT'
	];

	/**
	 * Array containing valid alert types for the ALERT topic type
	 *
	 * @var array
	 * @since 2.2.11
	 */
	private $alertTypes = [
		'COVID_19'
	];

	/**
	 * LocalPost constructor.
	 *
	 * @param string $languageCode
	 * @param string $summary
	 * @param string $topicType
	 *
	 * @return void
	 */
	public function __construct($languageCode, $summary, $topicType) {
		$this->setLanguageCode($languageCode);
		$this->setSummary($summary);
		$this->setTopicType($topicType);
	}


	/**
	 * Set post topic type, check against valid post types
	 *
	 * @param $topicType
	 *
	 * @return void
	 */
	public function setTopicType($topicType){
		if(!in_array($topicType, $this->localPostTopicTypes)){
			throw new OutOfBoundsException(__('Invalid Topic type', 'post-to-google-my-business'));
		}
		$this->jsonOutput['topicType'] = $topicType;
	}

	/**
	 * Returns the GMB post type, "topic type"
	 *
	 * @return mixed
	 */
	public function getTopicType(){
		return $this->jsonOutput['topicType'];
	}

	/**
	 * Sets the alert type
	 *
	 * @param $alertType
	 *
	 * @return void
	 *
	 * @since 2.2.11
	 */
	public function setAlertType($alertType){
		if(!in_array($alertType, $this->alertTypes)){
			throw new OutOfBoundsException(__('Invalid Alert type', 'post-to-google-my-business'));
		}
		$this->jsonOutput['alertType'] = $alertType;
	}

	/**
	 * Get the alert type, currently only applicable to COVID-19 posts
	 *
	 * @return string
	 */
	public function getAlertType(): string {
		return $this->jsonOutput['alertType'];
	}

	/**
	 * Set post language code
	 *
	 * @param $languageCode
	 *
	 * @return void
	 */
	public function setLanguageCode($languageCode){
		$this->jsonOutput['languageCode'] = $languageCode;
	}

	/**
	 * Gets the language code
	 *
	 * @return string
	 */
	public function getLanguageCode(): string {
		return $this->jsonOutput['languageCode'];
	}

	/**
	 * Set the post summary and check if it is no longer than 1500 characters
	 *
	 * @param $summary
	 *
	 * @return void
	 */
	public function setSummary($summary){
		$length = UTF16CodeUnitsUtil::strwidth($summary);
		if($length < 1 || $length > 1500){
			throw new LengthException(__('Post text should be between 1 and 1500 characters', 'post-to-google-my-business'));
		}
		$this->jsonOutput['summary'] = $summary;
	}

	/**
	 * Gets the post text, "summary"
	 *
	 * @return string
	 */
	public function getSummary(): string {
		return $this->jsonOutput['summary'];
	}

	/**
	 * Add CallToAction object to the post
	 *
	 * @param CallToAction $callToAction
	 *
	 * @return void
	 */
	public function addCallToAction(CallToAction $callToAction){
		$this->jsonOutput['callToAction'] = $callToAction->getArray();
	}

	/**
	 * Gets CallToAction object if one is set, or false if not.
	 *
	 * @return false|CallToAction
	 */
	public function getCallToAction(){
		return !empty($this->jsonOutput['callToAction']) ? $this->jsonOutput['callToAction'] : false;
	}

	/**
	 * Add Offer object to the post
	 *
	 * @param LocalPostOffer $localPostOffer
	 *
	 * @return void
	 */
	public function addLocalPostOffer(LocalPostOffer $localPostOffer){
		$this->jsonOutput['offer'] = $localPostOffer->getArray();
	}

	/**
	 * Returns LocalPostOffer object or false if none is set
	 *
	 * @return LocalPostOffer|false
	 */
	public function getLocalPostOffer(){
		return !empty($this->jsonOutput['offer']) ? $this->jsonOutput['offer'] : false;
	}

	/**
	 * Attach photo or video to the post
	 *
	 * @param MediaItem $mediaItem
	 *
	 * @return void
	 */
	public function addMediaItem(MediaItem $mediaItem){
		$this->jsonOutput['media'][] = $mediaItem->getArray();
		if(count($this->jsonOutput['media']) > 1){
			throw new OverflowException(__('Posts can only have 1 image', 'post-to-google-my-business'));
		}
	}

	/**
	 * Add event to the post
	 *
	 * @param LocalPostEvent $localPostEvent
	 *
	 * @return void
	 */
	public function addLocalPostEvent(LocalPostEvent $localPostEvent){
		$this->jsonOutput['event'] = $localPostEvent->getArray();
	}


	/**
	 * Create a LocalPost from an array (e.g. Array stored in custom fields, Post data returned by Google)
	 *
	 * @param $localPostData
	 *
	 * @return LocalPost
	 */
	public static function fromArray($localPostData){
		$localPostData = (array)$localPostData;

		$localPost = new static($localPostData['languageCode'], $localPostData['summary'], $localPostData['topicType']);

		if(isset($localPostData->callToAction)){
			$localPost->addCallToAction(CallToAction::fromArray($localPostData['callToAction']));
		}

		if(isset($localPostData->offer)){
			$localPost->addLocalPostOffer(LocalPostOffer::fromArray($localPostData['offer']));
		}

		if(isset($localPostData->media) && is_array($localPostData->media)){
			foreach($localPostData->media as $media){
				$localPost->addMediaItem(MediaItem::fromArray($media));
			}
		}

		if(isset($localPostData->event)){
			$localPost->addLocalPostEvent(LocalPostEvent::fromArray($localPostData['event']));
		}
		return $localPost;
	}
}

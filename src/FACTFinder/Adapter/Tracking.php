<?php
namespace FACTFinder\Adapter;

use FACTFinder\Loader as FF;

class Tracking extends AbstractAdapter
{
    /**
     * @var FACTFinder\Util\LoggerInterface
     */
    private $log;

    public function __construct(
        $loggerClass,
        \FACTFinder\Core\ConfigurationInterface $configuration,
        \FACTFinder\Core\Server\Request $request,
        \FACTFinder\Core\Client\UrlBuilder $urlBuilder
    ) {
        parent::__construct($loggerClass, $configuration, $request,
                            $urlBuilder);

        $this->log = $loggerClass::getLogger(__CLASS__);

        $this->request->setAction('Tracking.ff');

        $this->request->setConnectTimeout($configuration->getTrackingConnectTimeout());
        $this->request->setTimeout($configuration->getTrackingTimeout());

        // $this->usePassthroughResponseContentProcessor(); (default)
    }

    /**
     * Track an event manually. This is only necessary if there are no tracking
     * parameters in the client request or if some of these should be
     * overwritten. Otherwise, you can simply call applyTracking() directly.
     * @param \FACTFinder\Data\TrackingEventType $eventType
     * @param string[] $trackingParameters
     * @return bool Success?
     */
    public function trackEvent(
        \FACTFinder\Data\TrackingEventType $eventType,
        array $trackingParameters
    ) {
        $this->setEventType($eventType);
        $this->setTrackingParameters($trackingParameters);
        return $this->applyTracking();
    }

    /**
     * @param \FACTFinder\Data\TrackingEventType $eventType
     */
    public function setEventType(
        \FACTFinder\Data\TrackingEventType $eventType
    ) {
        $trackingEventTypeEnum = FF::getClassName('Data\TrackingEventType');
        switch ($eventType)
        {
        case $trackingEventTypeEnum::Display():
            $this->parameters['event'] = 'display'; break;
        case $trackingEventTypeEnum::Feedback():
            $this->parameters['event'] = 'feedback'; break;
        case $trackingEventTypeEnum::Inspect():
            $this->parameters['event'] = 'inspect'; break;
        case $trackingEventTypeEnum::AvailabilityCheck():
            $this->parameters['event'] = 'availabilityCheck'; break;
        case $trackingEventTypeEnum::Cart():
            $this->parameters['event'] = 'cart'; break;
        case $trackingEventTypeEnum::Buy():
            $this->parameters['event'] = 'buy'; break;
        case $trackingEventTypeEnum::CacheHit():
            $this->parameters['event'] = 'cacheHit'; break;
        case $trackingEventTypeEnum::SessionStart():
            $this->parameters['event'] = 'sessionStart'; break;
        }
    }

    /**
     * Configure the adapter for a tracking request.
     * @param string[] $trackingParameters
     */
    public function setTrackingParameters(
        array $trackingParameters
    ) {
        $parameterKeys = array(
            'sid',          // Session ID
            'sourceRefKey', // Reference key of the previous FF response
            'uid',          // User ID
            'cookieId',     // Cookie ID for sessionStart events of returning users
            'price',        // Price of item for all kinds of interest events
            'amount',       // Amount of products for cart and buy events
            'positive',     // Boolean to indicate type of feedback event
            'message',      // Message of feedback event
            'site',         // For shops with multiple store IDs
            'id',           // Product ID
            'mid',          // Master ID for shops using product variants
        );

        foreach ($parameterKeys as $key)
        {
            if (isset($trackingParameters[$key])
                && !empty($trackingParameters[$key])
            ) {
                $this->parameters[$key] = $trackingParameters[$key];
            }
        }
    }

    /**
     * Trigger the actual tracking request.
     *
     * @return bool Success?
     */
    public function applyTracking()
    {
        if (!isset($this->parameters['event']))
            throw new \InvalidArgumentException('No event type set!');

        if (!isset($this->parameters['sourceRefKey'])
            && $this->parameters['event'] != 'sessionStart'
        ) {
            throw new \InvalidArgumentException('The given event type requires a "sourceRefKey" parameter.');
        }

        if (!isset($this->parameters['sid']))
            $this->parameters['sid'] = session_id();

        $response = trim($this->getResponseContent());
        return $response == 'The event was successfully tracked';
    }

}

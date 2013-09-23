<?php
namespace FACTFinder\Core\Server;

use FACTFinder\Loader as FF;

/**
 * This implementation retrieves the FACT-Finder data from the file system. File
 * names are generated from request parameters. For the naming convention see
 * the getFileName() method.
 * Responses are queried sequentially and lazily and are cached as long as
 * parameters don't change.
 */
class FileSystemDataProvider extends AbstractDataProvider
{
    /**
     * @var \FACTFinder\Util\LoggerInterface
     */
    private $log;

    /**
     * @var string
     */
    protected $fileLocation;
    protected $fileExtension = ".json";

    public function __construct(
        $loggerClass,
        \FACTFinder\Core\ConfigurationInterface $configuration
    ) {
        parent::__construct($loggerClass, $configuration);

        $this->log = $loggerClass::getLogger(__CLASS__);
    }

    public function setConnectTimeout($id, $timeout)
    { }

    public function setTimeout($id, $timeout)
    { }

    public function setFileLocation($loc)
    {
        $this->fileLocation = ($loc[strlen($loc) -1] == DS) ? $loc : $loc . DS;
    }

    public function setFileExtension($ext)
    {
        $this->fileExtension = ($ext[0] == '.') ? $ext : ".$ext";
    }

    public function loadResponse($id)
    {
        if (!isset($this->connectionData[$id]))
            throw new \InvalidArgumentException('Tried to get response for invalid ID $id.');


        $connectionData = $this->connectionData[$id];

        $action = $connectionData->getAction();
        if (empty($action))
        {
            $this->log->error('Request type missing.');
            $connectionData->setNullResponse();
            return;
        }

        $fileName = $this->getFileName($connectionData);

        if (!$this->hasFileNameChanged($id, $fileName))
            return;

        $this->log->info("Trying to load file: $fileName");

        $response = FF::getInstance(
            'Core\Server\Response',
            file_get_contents($fileName),
            200,
            0,
            ''
        );

        $connectionData->setResponse($response, $fileName);
    }

    private function getFileName($connectionData)
    {
        $action = $connectionData->getAction();

        // Repalce the .ff file extension with an underscore.
        $fileName = preg_replace('/[.]ff$/i', '_', $action);

        $parameters = clone $connectionData->getParameters();

        unset($parameters['format']);
        unset($parameters['user']);
        unset($parameters['pw']);
        unset($parameters['timestamp']);
        unset($parameters['channel']);

        $rawParameters = $parameters->getArray();

        ksort($rawParameters, SORT_STRING);

        $fileName .= http_build_query($rawParameters, '', '_');
        $fileName .= $this->fileExtension;

        // TODO: Get rid of duplicate code (see Parameters::toJavaQueryString()).
        // The following preg_replace removes all []-indices from array
        // parameter names.
        $fileName = preg_replace(
            '/
            %5B       # URL encoded "["
            (?:       # start non-capturing group
              (?!%5D) # make sure the next character does not start "%5D"
              [^=_]   # consume the character if it is no "=" or "_"
            )*        # end of group; repeat
            %5D       # URL encoded "]"
            (?=       # lookahead to ensure the match is inside a parameter name
                      # and not a value
              [^=_]*= # make sure there is a "=" before the next "_"
            )         # end of lookahead
            /xi',
            '',
            $fileName
        );

        return $this->fileLocation . $fileName;
    }

    private function hasFileNameChanged($id, $newFileName)
    {
        $connectionData = $this->connectionData[$id];

        if (is_a($connectionData->getResponse(), FF::getClassName('Core\Server\NullResponse')))
            return true;

        return $newFileName != $connectionData->getPreviousUrl();
    }
}
